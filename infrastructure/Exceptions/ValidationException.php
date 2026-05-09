<?php
namespace Infrastructure\Exceptions;

use Infrastructure\Exceptions\HttpException;


/**
 * Exception representing an HTTP 422 Unprocessable Entity error.
 *
 * This exception is used when the server successfully understands
 * the request and its structure is syntactically correct, but
 * one or more fields fail domain or validation rules.
*/
class ValidationException extends HttpException {
    /**
     * Creates a new Validation exception.
     *
     * @param string $message Human-readable message describing
     * the validation failure.
    */
    public function __construct(string $message = "Datos invalidos.") {
        parent::__construct($message, 422);
    }
};