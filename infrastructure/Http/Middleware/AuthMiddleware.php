<?php
namespace Infrastructure\Http\Middleware;

use Infrastructure\Http\Middleware\MiddlewareInterface;
use Infrastructure\Http\Request;
use Infrastructure\Http\Response;

class AuthMiddleware implements MiddlewareInterface{
    public function handle(Request $req, Response $res, callable $next) : void {
        // simula verificacion de auth
        session_start();

        // $_SESSION['user_id'] = 15;
        $isUserLoggedIn = isset($_SESSION['user_id']);

        if(!$isUserLoggedIn) {
            // /detener
            $res->json(['error' => 'no autorizado'], 401);
        }

        $req->user_id = $_SESSION['user_id'];

        // todo bien pasamos a la sig capa
        $next();
    }
}
