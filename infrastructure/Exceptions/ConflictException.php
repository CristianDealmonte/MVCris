<?php
namespace Infrastructure\Exceptions;

use Infrastructure\Exceptions\HttpException;


/**
 * Exception representing an HTTP 409 Conflict error.
 * 
 * This exception is used when a request cannot be completed
 * due to a conflict with the current state of the resource.
 */
class ConflictException extends HttpException {
    /**
     * Creates a new Confict exception.
     * 
     * @param string $message Human-readable error message describing
     * the nature of the conflict.
     */
    public function __construct(string $message = "Conflicto.") {
        parent::__construct($message, 409);
    }
};