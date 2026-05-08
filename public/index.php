<?php
require_once __DIR__ . '/../server.php';

use Infrastructure\Http\Router;
use Infrastructure\Http\Middleware\AuthMiddleware;

use Controller\MainController;
use Middleware\TimingMiddleware;


$router = new Router();

$router->get('/', [MainController::class, 'index']);
$router->get('/products/{id}/coments/{id_comment}', [MainController::class, 'index']);
$router->post('/1', [MainController::class, 'index']);
$router->post('/2', [MainController::class, 'index']);


$router->get('/perfil', [MainController::class, 'perfil'])
    ->middleware(TimingMiddleware::class)
    ->middleware(AuthMiddleware::class);


$router->verifyRoutes();