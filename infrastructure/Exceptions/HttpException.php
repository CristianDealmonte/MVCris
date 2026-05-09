<?php
namespace Infrastructure\Exceptions;

use Exception;

/**
 * Base exception for all HTTP related errors.
 * 
 * This exception represents an error that has a direct
 * correspondence with an HTTP status code.
 * 
 * It allows the application to throw meaningful, semantic
 * exceptions (e.g. 404 Not Found, 403 Forbidden) withoth
 * immediately deciding how the response should be rendered.
 * 
 * The conversion of this exception into an actual HTTP response
 * is handled later by the global ErrorHandler.
 */
class HttpException extends Exception {
    /**
     * HTTP status code associated with the exception.
     * 
     * Common examples:
     *  - 400 Bad Request.
     *  - 401 Unauthorized.
     *  - 403 Forbidden.
     * 
     * @var int
     */
    protected int $statusCode;


    /**
     * Creates a new HTTP exception instance.
     * 
     * @param string $message Human-readable error message.
     * @param int $statusCode HTTP status code (defaults to 500).
     */
    public function __construct(string $message, int $statusCode = 500) {
        parent::__construct($message);
        $this->statusCode = $statusCode;
    }


    /**
     * Returns the HTTP status code associated with the exception.
     * 
     * This value is used by the ErrorHandler to determinate
     * which HTTP status code should be sent to the client.
     * 
     * @return int
     */
    public function getStatusCode(): int {
        return $this->statusCode;
    }
}