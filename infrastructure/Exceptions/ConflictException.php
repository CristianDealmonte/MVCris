<?php
namespace Infrastructure\Exceptions;

use Infrastructure\Exceptions\HttpException;

class ConflictException extends HttpException {
    public function __construct(string $message = "Conflicto.") {
        parent::__construct($message, 409);
    }
};