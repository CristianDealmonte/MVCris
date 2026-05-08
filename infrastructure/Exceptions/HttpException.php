<?php
namespace Infrastructure\Exceptions;

use Exception;

/**
 * Base exception for all HTTP related errors.
 */
class HttpException extends Exception {
    protected int $statusCode;

    public function __construct(string $message, int $statusCode = 500) {
        parent::__construct($message);
        $this->statusCode = $statusCode;
    }

    public function getStatusCode(): int {
        return $this->statusCode;
    }
}