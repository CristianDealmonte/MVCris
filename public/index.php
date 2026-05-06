<?php
require_once __DIR__ . '/../server.php';

use Infrastructure\Router;
use Controller\MainController;

$router = new Router();

$router->get('/', [MainController::class, 'index']);
$router->get('/products/{id}/coments/{id_coment}', [MainController::class, 'index']);



$router->verifyRoutes();