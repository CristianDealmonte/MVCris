<?php
namespace Infrastructure\Exceptions;

use Infrastructure\Http\Request;
use Infrastructure\Http\Response;
use Throwable;

/**
 * Global application error handler.
 * 
 * This class is responsable for converting any unhandled exception
 * thrown during the HTTP lifecycle into a valid HTTP response.
 * 
 * The ErrorHandler:
 *  - Determinates the apropiate HTTP status code.
 *  - Protects sensitive error details for server errors (5xx).
 *  - Chooses the correct response format (JSON or HTML).
 *  - Delegates output rendering to the Response object.
 * 
 * It represents the final boundary between application errors
 * and the HTTP layer.
 */

class ErrorHandler {
    /**
     * Handles an unhandled exception and sends the appropiate HTTP response.
     * 
     * This method is invoked by the Router when an exception escapes
     * the middleware/controller pipeline.
     * 
     * Responsabilities:
     *  - Map exceptions to HTTP status code.
     *  - Prevent sensitive server error details from reaching the client.
     *  - Log internal errors for debugging.
     *  - Decide whether to respond with JSON or HTML
     * 
     * @param Request $req The incoming HTTP request.
     * @param Response $res The outgoing HTTP response.
     * @param Throwable $exception The thrown exception.
     * 
     * @return void.
    */
    public static function handleException(Request $req, Response $res, Throwable $exception) : void {
        /**
         * Determinate HTTP status code.
         * 
         * If the exception represents an HTTP-aware error,
         * extract its status code. Otherwise, default to 500
         * (Internal Server Error).
        */
        $statusCode = $exception instanceof HttpException 
            ? $exception->getStatusCode()
            : 500;

        /**
         * Protect sesitive error details.
         * 
         * Client errors (4xx) are safe to expose since they usually
         * represents validation or authorization issues.
         * 
         * Server errors (5xx) must not expose internal server details.
        */
        $isClientError = $statusCode >= 400 && $statusCode < 500;
        $message = $isClientError 
            ? $exception->getMessage() 
            : 'Error interno del servidor. Intente más tarde.';
        
        
        /**
         * Log internal error for debugging purposes.
         * 
         * Only logs 5xx errors to avoid polluting logs with
         * expected client-side failures.
         */
        if (!$isClientError) {
            error_log($exception->getMessage() . " en " . $exception->getFile() . ":" . $exception->getLine());
        }

        
        /**
         * Determinate the expected response format.
         * 
         * API client tipically request JSON responses via
         * the Acept header. Browsers expect HTML.
         */
        $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
        $wantsJson = str_contains($acceptHeader, 'application/json');

        
        /**
         * Return JSON response for API clients.
         */
        if($wantsJson) {
            $res->json([
                'success' => false,
                'message' => $message,
                'code' => $statusCode,
            ], $statusCode);
            return;
        }

        /**
         * Render an HTML error page for browser clients.
         */
        $res->render('errors/error', [
            'statusCode' => $statusCode,
            'message' => $message
        ]);
    }
}