<?php
namespace Infrastructure\Exceptions;

use Infrastructure\Exceptions\HttpException;

class NotFoundException extends HttpException {
    public function __construct(string $message = 'Pagina no encontrada') {
        parent::__construct($message, 404);
    }
}