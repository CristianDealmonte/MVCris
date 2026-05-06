<?php
namespace Infrastructure\Http;

/**
 * Core Router for the MVC Framework
 * 
 * Handles route registration and matches incomming HTTP requests
 * to their corresponding controllers or closures.
 */
class Router {
    /**
     * Stores registered routes grouped by HTTP method.
     * 
     * @var array<string, array<string, callable|array>>
     */
    private array $routes = [];


    /**
     * Internal method to register a route into the router.
     * 
     * @param string $method the HTTP method (GET, POST, PUT, PATCH, DELETE).
     * @param string $url The URI pattern for the route.
     * @callable|array $fn The controller action or closure to execute.
     * @return void
     */
    protected function addRoute(string $method, string $url, callable|array $fn) : void {
        $this->routes[$method][$url] = $fn;
    }


    /**
     * Registers a GET route.
     *
     * @param string $url The URI pattern.
     * @param callable|array $fn The callback or controller array [ControllerClass, 'method'].
     * @return void
     */
    public function get(string $url, callable|array $fn) : void {
        $this->addRoute('GET', $url, $fn);
    }


    /**
     * Registers a POST route.
     *
     * @param string $url The URI pattern.
     * @param callable|array $fn The callback or controller array.
     * @return void
     */
    public function post(string $url, callable|array $fn) : void {
        $this->addRoute('POST', $url, $fn);
    }


    /**
     * Registers a PUT route.
     *
     * @param string $url The URI pattern.
     * @param callable|array $fn The callback or controller array.
     * @return void
     */
    public function put(string $url, callable|array $fn) : void {
        $this->addRoute('PUT', $url, $fn);
    }


    /**
     * Registers a PATCH route.
     *
     * @param string $url The URI pattern.
     * @param callable|array $fn The callback or controller array.
     * @return void
     */
    public function patch(string $url, callable|array $fn) : void {
        $this->addRoute('PATCH', $url, $fn);
    }


    /**
     * Registers a DELETE route.
     *
     * @param string $url The URI pattern.
     * @param callable|array $fn The callback or controller array.
     * @return void
     */
    public function delete(string $url, callable|array $fn) : void {
        $this->addRoute('DELETE', $url, $fn);
    }


    /**
     * Resolves the current HTTP request and dispatches it to the matching route.
     * 
     * Validate static and dynamic URLs using regular expressions, extracts
     * URL parameters, and injects the Request and Response objects into the controllers.
     * 
     * @return void
     */
    public function verifyRoutes() {
        $req = new Request();
        $res = new Response();

        // Prevent errors if a request is made with an unregistered method
        $routes = $this->routes[$req->method] ?? [];
        $fn = null;

        // Verification for static routes
        if (isset($routes[$req->url])) {
            $fn = $routes[$req->url];
        } else {
            // Regular Expression Search for Dynamic Routes
            foreach ($routes as $route => $controller) {
                // Convert {param} to a Regex capturing group
                $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<\1>[a-zA-Z0-9_-]+)', $route);
                $pattern = '#^' . $pattern . '$#';
       
                // Check if the current URL matches the pattern
                if (preg_match($pattern, $req->url, $matches)) {
                    $fn = $controller;

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

        if($fn) {
            // Run the controller by injecting $req and $res
            call_user_func($fn, $req, $res);
        } else {
            // If the path does not exist, an error message is displayed
            http_response_code(404);
            echo "Página no encontrada";
        }
    }
}