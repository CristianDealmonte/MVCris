<?php
namespace Infrastructure\Exceptions;

use Infrastructure\Exceptions\HttpException;

class ForbiddenException extends HttpException {
    public function __construct(string $message = "Acceso denegado.") {
        parent::__construct($message, 403);
    }
};