<?php
namespace Infrastructure\Exceptions;

use Infrastructure\Http\Request;
use Infrastructure\Http\Response;
use Throwable;

class ErrorHandler {
    /**
     * Captura cualquier excepcion no controlada en la aplicacion.
     */
    public static function handleException(Request $req, Response $res, Throwable $exception) : void {
        // $res = new Response();

        // Determinar el codigo de estado HTTP
        $statusCode = $exception instanceof HttpException 
            ? $statusCode = $exception->getStatusCode()
            : 500;

        // Determinara que formato de respuesta espera el cliente
        $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
        $wantsJson = str_contains($acceptHeader, 'application/json');

        // Responder en JSON (Para APIs)
        if($wantsJson) {
            $res->json([
                'success' => false,
                'message' => $exception->getMessage(),
                'code' => $statusCode,
            ], $statusCode);
            return;
        }

        // Responder con vista HTML (Para Navegadores)
        http_response_code($statusCode);
        echo "<div>";
        echo "<h1>Error {$statusCode}</h1>";
        echo "<p>{$exception->getMessage()}</p>";
        echo "</div>";
    }
}