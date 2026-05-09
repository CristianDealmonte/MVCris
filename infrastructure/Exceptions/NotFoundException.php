<?php
namespace Infrastructure\Exceptions;

use Infrastructure\Exceptions\HttpException;


/**
 * Exception representing an HTTP 404 Not Found error.
 *
 * This exception is used when the requested resource
 * or route does not exist.
*/
class NotFoundException extends HttpException {
    /**
     * Creates a new Not Found exception.
     *
     * @param string $message Human-readable message describing
     * the missing resource.
    */
    public function __construct(string $message = 'Pagina no encontrada') {
        parent::__construct($message, 404);
    }
}