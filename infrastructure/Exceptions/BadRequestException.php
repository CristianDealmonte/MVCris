<?php
namespace Infrastructure\Exceptions;

use Infrastructure\Exceptions\HttpException;

class BadRequestException extends HttpException {
    public function __construct(string $message = "Peticion invalida.") {
        parent::__construct($message, 400);
    }
};