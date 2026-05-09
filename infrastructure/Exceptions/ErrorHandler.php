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
        // Determinar el codigo de estado HTTP
        $statusCode = $exception instanceof HttpException 
            ? $exception->getStatusCode()
            : 500;

        // 2. Protección de mensajes sensibles en errores 500
        $isClientError = $statusCode >= 400 && $statusCode < 500;
        $message = $isClientError 
            ? $exception->getMessage() 
            : 'Error interno del servidor. Intente más tarde.';
        
        // Si no es un error de cliente, guardamos el mensaje real para depurar
        if (!$isClientError) {
            error_log($exception->getMessage() . " en " . $exception->getFile() . ":" . $exception->getLine());
        }

        // Determinara que formato de respuesta espera el cliente
        $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
        $wantsJson = str_contains($acceptHeader, 'application/json');

        // Responder en JSON (Para APIs)
        if($wantsJson) {
            $res->json([
                'success' => false,
                'message' => $message,
                'code' => $statusCode,
            ], $statusCode);
            return;
        }

        // Responder con vista HTML (Para Navegadores)
        $res->render('errors/error', [
            'statusCode' => $statusCode,
            'message' => $message
        ]);
    }
}