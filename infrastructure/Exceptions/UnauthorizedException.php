<?php
namespace Infrastructure\Exceptions;

use Infrastructure\Exceptions\HttpException;



/**
 * Exception representing an HTTP 401 Unauthorized error.
 *
 * This exception is used when a client attempts to access
 * a protected resource without being properly authenticated.
*/
class UnauthorizedException extends HttpException {
    /**
     * Creates a new Unauthorized exception.
     *
     * @param string $message Human-readable message describing
     * the authentication failure.
    */
    public function __construct(string $message = "No autorizado.") {
        parent::__construct($message, 401);
    }
};