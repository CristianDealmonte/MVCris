<?php
namespace Infrastructure\Exceptions;

use Infrastructure\Exceptions\HttpException;

class UnauthorizedException extends HttpException {
    public function __construct(string $message = "No autorizado.") {
        parent::__construct($message, 401);
    }
};