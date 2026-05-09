<?php
namespace Infrastructure\Http\Middleware;

use Infrastructure\Http\Request;
use Infrastructure\Http\Response;

/**
 * Contract for all HTTP middlewares in the application.
 * 
 * A middleware is a reusable component that participates in the
 * HTTP request lifecycle before and/or after the controller
 * execution.
 * 
 * Middlewares are executed following an "onion architecture"
 * (also known as a middleware pipeline), where each middleware:
 * 
 *  - Recives the current Request and Response objects.
 *  - Decides whether continue the execution.
 *  - Can execute logic before and after calling the next layer.
 * 
 * Examples of middleware responsabilities include:
 *  - Authentication / Authorization.
 *  - Logging and metrics.
 *  - Request validation.
 *  - Performance measurement.
 *  - Error Handling. 
 * 
 * All middlewares MUST implement this interface to be compatible
 * with the routher's middleware pipeline.
 */
interface MiddlewareInterface {
    /**
     * Handles the incoming request before it reaches the controller.
     * 
     * This method is invoked by the router during request dispatch.
     * The middleware may: 
     *  - Execute logic before the next middleware/controller.
     *  - Call the $next callable to continue the pipeline.
     *  - Execute logic after the next layer returns.
     *  - Interrupt the pipeline by not calling $next
     * 
     * IMPORTANT:
     *  - Calling next() allows the request to proceed.
     *  - Not calling $next() stops futher execution.
     *  - Any exception thrown here will be handled by the global error handler.
     * 
     * @param Request $req The incoming HTTP request.
     * @param Response $res The outgoing HTTP response.
     * @param callable $next The next middleware or the core controller.
     * @return void
     */
    public function handle(Request $req, Response $res, callable $next): void;
}