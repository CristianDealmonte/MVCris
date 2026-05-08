<?php
namespace Infrastructure\Exceptions;

use Infrastructure\Exceptions\HttpException;

class ValidationException extends HttpException {
    public function __construct(string $message = "Datos invalidos.") {
        parent::__construct($message, 422);
    }
};