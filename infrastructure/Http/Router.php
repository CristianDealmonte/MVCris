<?php
namespace Infrastructure\Http;
use Utils\Dev;

// use Infraestructure\Http\Request;
// use Infraestructure\Http\Response;
/**
 * Core Router for the MVC Framework
 * Handles route registration, dynamic parameter extraction
 * and request dispatching through an onion-arquitecture middleware stack.
 */
class Router {
    /**
     * Stores registered routes grouped by HTTP method.
     * Contains the action (controller) and its associated middlewares.
     * @var array<string, array<string, array{action: callable|array, middlewares: array<string>}>>
     */
    private array $routes = [];


    /**
     * Temporaly stores the last registered route to allow method chaining.
     * @var array{method: string, url: string}
     */
    private array $lastRoute = [];


    /**
     * Internal method to register a route into the router.
     * 
     * @param string $method the HTTP method (GET, POST, PUT, PATCH, DELETE).
     * @param string $url The URI pattern for the route.
     * @callable|array $fn The controller action or closure to execute.
     * @return self Returns the Router instance to enable method chaining.
     */
    protected function addRoute(string $method, string $url, callable|array $fn) : self {
        $this->routes[$method][$url] = [
            'action' => $fn,
            'middlewares' => []
        ];

        $this->lastRoute = ['method' => $method, 'url' => $url];

        return $this;
    }


    /**
     * Registers a GET route.
     *
     * @param string $url The URI pattern.
     * @param callable|array $fn The callback or controller array [ControllerClass, 'method'].
     * @return self
     */
    public function get(string $url, callable|array $fn) : self {
        return $this->addRoute('GET', $url, $fn);
    }


    /**
     * Registers a POST route.
     *
     * @param string $url The URI pattern.
     * @param callable|array $fn The callback or controller array.
     * @return self
     */
    public function post(string $url, callable|array $fn) : self {
        return $this->addRoute('POST', $url, $fn);
    }


    /**
     * Registers a PUT route.
     *
     * @param string $url The URI pattern.
     * @param callable|array $fn The callback or controller array.
     * @return self
     */
    public function put(string $url, callable|array $fn) : self {
        return $this->addRoute('PUT', $url, $fn);
    }


    /**
     * Registers a PATCH route.
     *
     * @param string $url The URI pattern.
     * @param callable|array $fn The callback or controller array.
     * @return self
     */
    public function patch(string $url, callable|array $fn) : self {
        return $this->addRoute('PATCH', $url, $fn);
    }


    /**
     * Registers a DELETE route.
     *
     * @param string $url The URI pattern.
     * @param callable|array $fn The callback or controller array.
     * @return self
     */
    public function delete(string $url, callable|array $fn) : self {
        return $this->addRoute('DELETE', $url, $fn);
    }


    /**
     * Assigns one or multiple middlewares to the last registered route.
     * 
     * @param string ...$middlewares The fully qualifierd class names of the middlewares.
     * @return self Returns the router instance to enable further chaining.
     */
    public function middleware(string ...$middlewares) : self {
        $method = $this->lastRoute['method'];
        $url = $this->lastRoute['url'];

        foreach($middlewares as $middleware) {
            $this->routes[$method][$url]['middlewares'][] = $middleware;
        }
        return $this;
    }


    /**
     * Resolves the current HTTP request and dispatches it.
     * Validate URLs, extract dynamic parameters, builds the Request,
     * Response objects and executes the middleware stack (Onion Arquitecture)
     * before reaching the core controller.
     * 
     * @return void
     */
    public function verifyRoutes() : void{
        $req = new Request();
        $res = new Response();

        $routes = $this->routes[$req->method] ?? [];
        $routeData = null;

        // Static routes verification
        if (isset($routes[$req->url])) {
            $routeData = $routes[$req->url];
        } else {
            // Dynamic route verification using Regex
            foreach ($routes as $route => $data) {
                $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<\1>[a-zA-Z0-9_-]+)', $route);
                $pattern = '#^' . $pattern . '$#';
       
                if (preg_match($pattern, $req->url, $matches)) {
                    $routeData = $data;

                    // Extract only the named parameters
                    foreach ($matches as $key => $value) {
                        if (is_string($key)) {
                            $req->params[$key] = $value;
                        }
                    }
                    break;
                }
            }
        }

        // Execution flow
        if($routeData) {
            $action = $routeData['action'];
            $middlewares = $routeData['middlewares'];

            // Define the core of the onion (controller)
            $core = function($req, $res) use ($action) {
                call_user_func($action, $req, $res);
            };

            // build the onion arquetecture from the inside out
            $onion = array_reduce(
                array_reverse($middlewares),
                function($nextLayer, $middlewareClass) {
                    return function($req, $res) use ($nextLayer, $middlewareClass) {
                        //
                        $next = function() use ($nextLayer, $req, $res) {
                            return $nextLayer($req, $res);
                        };
                        // Instantiate the middleware
                        $middleware = new $middlewareClass();
                        // Execute it, passing the next layer as the callable
                        return $middleware->handle($req, $res, $next);
                    };
                },
                $core // Initial value for the reduction
            );

            // Dev::debug($onion);

            $onion($req, $res); // Esecute the outermost layer
        } else {
            // Fallback for unmached routes
            http_response_code(404);
            echo "Página no encontrada";
        }
    }
}