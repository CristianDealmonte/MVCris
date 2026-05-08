<?php
namespace Infrastructure\Exceptions;

use Infrastructure\Exceptions\HttpException;

class ServiceUnavailableException extends HttpException {
    public function __construct(string $message = "Servicio no disponible.") {
        parent::__construct($message, 503);
    }
};