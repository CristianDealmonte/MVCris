<?php
namespace Infrastructure\Http\Middleware;

use Infrastructure\Http\Middleware\MiddlewareInterface;
use Infrastructure\Http\Request;
use Infrastructure\Http\Response;
use Infrastructure\Exceptions\UnauthorizedException;

class AuthMiddleware implements MiddlewareInterface{
    public function handle(Request $req, Response $res, callable $next) : void {
        // echo "\niniciando autenticacion\n";

        // simula verificacion de auth
        session_start();
        $_SESSION['user_id'] = 15;

        
        $isUserLoggedIn = isset($_SESSION['user_id']);
        if(!$isUserLoggedIn) {
            throw new UnauthorizedException("Tu sesion ha expirado o no has iniciado sesion");
            // echo "\nretorno de respuesta\n";
        }

        $req->user_id = $_SESSION['user_id'];

        // todo bien pasamos a la sig capa
        $next();

        // echo "\ndespues de autenticar\n";
    }
}
