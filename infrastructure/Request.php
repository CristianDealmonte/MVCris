<?php
namespace Infrastructure;

class Request {
    public array $params = []; // Para los parámetros de la URL: /products/{id}
    public array $query = [];  // Para las variables de la URL: ?sort=asc
    public array $body = [];   // Para los datos del formulario (POST)
    public string $method;
    public string $url;

    public function __construct() {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->query = $_GET;
        $this->body = $_POST;
        
        $currentUrl = $_SERVER['REQUEST_URI'] ?? '/';
        $this->url = parse_url($currentUrl, PHP_URL_PATH);
    }
}