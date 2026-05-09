<?php
namespace Infrastructure\Exceptions;

use Infrastructure\Exceptions\HttpException;


/**
 * Exception representing an HTTP 403 Forbidden error.
 *
 * This exception is used when the client is authenticated
 * but does NOT have permission to perform the requested action
 * or access the requested resource.
*/
class ForbiddenException extends HttpException {
    /**
     * Creates a new Forbidden exception.
     *
     * @param string $message Human-readable message explaining
     * why access is denied.
     */
    public function __construct(string $message = "Acceso denegado.") {
        parent::__construct($message, 403);
    }
};