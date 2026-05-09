<?php
namespace Infrastructure\Exceptions;

use Infrastructure\Exceptions\HttpException;


/**
 * Exception representing an HTTP 503 Service Unavailable error.
 *
 * This exception is used when the server is currently unable
 * to handle the request due to a temporary condition.
*/
class ServiceUnavailableException extends HttpException {
    /**
     * Creates a new Service Unavailable exception.
     *
     * @param string $message Human-readable message describing
     * the temporary service issue.
    */
    public function __construct(string $message = "Servicio no disponible.") {
        parent::__construct($message, 503);
    }
};