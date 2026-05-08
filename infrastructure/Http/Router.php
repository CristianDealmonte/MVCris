<?php
namespace Infrastructure\Http;

use Infrastructure\Exceptions\ErrorHandler;


/**
 * Core HTTP Router of the framework.
 *
 * The Router is responsible for:
 * - Registering HTTP routes and their handlers
 * - Associating middleware pipelines to routes
 * - Matching incoming requests (static and dynamic routes)
 * - Building and executing the middleware onion architecture
 * - Delegating error handling to the global ErrorHandler
 *
 * The Router acts as the main request coordinator (HTTP kernel)
 * and guarantees that each request results in exactly ONE response.
*/

class Router {
    /**
     * Registered routes grouped by HTTP method.
     *
     * Structure:
     * [
     *   'GET' => [
     *     '/users/{id}' => [
     *       'action' => callable|array,
     *       'middlewares' => [MiddlewareClass::class, ...]
     *     ]
     *   ]
     * ]
     *
     * @var array<string, array<string, array{
     *     action: callable|array,
     *     middlewares: array<string>
     * }>>
    */
    private array $routes = [];



    /**
     * Stores the last registered route.
     *
     * Used to allow fluent method chaining when assigning middleware
     * after defining a route.
     *
     * Example:
     *   $router->get('/profile', ...)->middleware(AuthMiddleware::class);
     *
     * @var array{method: string, url: string}
    */
    private array $lastRoute = [];



    /**
     * Registers a route internally.
     *
     * This method stores the route definition along with its handler
     * and initializes an empty middleware stack.
     *
     * @param string $method HTTP method (GET, POST, PUT, PATCH, DELETE)
     * @param string $url Route URI pattern (can contain dynamic params)
     * @param callable|array $fn Route handler. (closure or [Controller, method])
     *
     * @return self
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
     * @param callable|array $fn Route handler.
     * @return self
     */
    public function get(string $url, callable|array $fn) : self {
        return $this->addRoute('GET', $url, $fn);
    }


    /**
     * Registers a POST route.
     *
     * @param string $url The URI pattern.
     * @param callable|array $fn Route Handler.
     * @return self
     */
    public function post(string $url, callable|array $fn) : self {
        return $this->addRoute('POST', $url, $fn);
    }


    /**
     * Registers a PUT route.
     *
     * @param string $url The URI pattern.
     * @param callable|array $fn Route handler.
     * @return self
     */
    public function put(string $url, callable|array $fn) : self {
        return $this->addRoute('PUT', $url, $fn);
    }


    /**
     * Registers a PATCH route.
     *
     * @param string $url The URI pattern.
     * @param callable|array $fn Route handler.
     * @return self
     */
    public function patch(string $url, callable|array $fn) : self {
        return $this->addRoute('PATCH', $url, $fn);
    }


    /**
     * Registers a DELETE route.
     *
     * @param string $url The URI pattern.
     * @param callable|array $fn Route handler.
     * @return self
     */
    public function delete(string $url, callable|array $fn) : self {
        return $this->addRoute('DELETE', $url, $fn);
    }



    /**
     * Assigns one or more middlewares to the last registered route.
     *
     * This method must be called after a route definition.
     *
     * @param string ...$middlewares Fully-qualified middleware class names
     * @return self
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
     * Resolves and dispatches the current HTTP request.
     *
     * This method:
     * - Creates Request and Response instances
     * - Matches the current request against registered routes
     * - Extracts dynamic route parameters
     * - Builds the middleware onion architecture
     * - Executes the request pipeline
     * - Ensures exactly one response is returned
     * - Delegates exception handling to the ErrorHandler
     *
     * @throws \Infrastructure\Exceptions\NotFoundException
    */
    public function verifyRoutes() : void{
        $req = new Request();
        $res = new Response();

        $routes = $this->routes[$req->method] ?? [];
        $routeData = null;

        // ===== Static route matching =====
        if (isset($routes[$req->url])) {
            $routeData = $routes[$req->url];
        } else {
            // ===== Dynamic route matching =====
            foreach ($routes as $route => $data) {
                $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<\1>[a-zA-Z0-9_-]+)', $route);
                $pattern = '#^' . $pattern . '$#';
       
                if (preg_match($pattern, $req->url, $matches)) {
                    $routeData = $data;

                    // Extract named parameters
                    foreach ($matches as $key => $value) {
                        if (is_string($key)) {
                            $req->params[$key] = $value;
                        }
                    }

                    break;
                }
            }
        }

        // ===== Request execution =====
        if($routeData) {
            $action = $routeData['action'];
            $middlewares = $routeData['middlewares'];

            // Core controller execution (center of the onion)
            $core = function($req, $res) use ($action) {
                call_user_func($action, $req, $res);
            };

            // Build the onion architecture (inside-out)
            $onion = array_reduce(
                array_reverse($middlewares),
                function($nextLayer, $middlewareClass) {
                    return function($req, $res) use ($nextLayer, $middlewareClass) {
                        
                        $next = function() use ($nextLayer, $req, $res) {
                            return $nextLayer($req, $res);
                        };
                        
                        $middleware = new $middlewareClass();
                        return $middleware->handle($req, $res, $next);
                    };
                },
                $core
            );

            try {
                $onion($req, $res);

                if (!$res->hasBeenSent()) {
                    throw new \LogicException('No response returned');
                }
            } catch (\Throwable $e) {
                if (!$res->hasBeenSent()) {
                    ErrorHandler::handleException($req, $res, $e);
                }

                error_log($e);
            }
        } else {
            // Route not found
            throw new \Infrastructure\Exceptions\NotFoundException(
                "La ruta {$req->url} no existe."
            );
        }
    }
}