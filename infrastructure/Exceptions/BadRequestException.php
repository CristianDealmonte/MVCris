<?php
namespace Infrastructure\Exceptions;

use Infrastructure\Exceptions\HttpException;


/**
 * Exception representing an HTTP 400 Bad Request error.
 * 
 * This exception should be thrown when the error is caused
 * by the client and can be fixed by modifying the request.
 */
class BadRequestException extends HttpException {
    /**
     * Creates a new Bad Request exception.
     * 
     * @param string $message Human-readable error message describing
     * why the request is invalid.
     */
    public function __construct(string $message = "Peticion invalida.") {
        parent::__construct($message, 400);
    }
};