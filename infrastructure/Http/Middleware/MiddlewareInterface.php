<?php
namespace Infrastructure\Http\Middleware;

use Infrastructure\Http\Request;
use Infrastructure\Http\Response;

/**
 * Standard interface for all application middlewares.
 */
interface MiddlewareInterface {
    /**
     * Handles the incoming request before it reaches the controller.
     * @param Request $req The incoming HTTP request.
     * @param Response $res The outgoing HTTP response.
     * @param callable $next The next middleware or the controller.
     * @return void
     */
    public function handle(Request $req, Response $res, callable $next): void;
}