<?php
namespace Infrastructure\Http;

class Request {
    public array $params = []; // Para los parámetros de la URL: /products/{id}
    public array $query = [];  // Para las variables de la URL: ?sort=asc
    public array $body = [];   // Para los datos del formulario (POST)
    public string $method;
    public string $url;

    public function __construct() {
        $currentUrl = $_SERVER['REQUEST_URI'] ?? '/';
        $this->url = parse_url($currentUrl, PHP_URL_PATH);
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->query = $_GET;

        // ===== Parse Body Data =====
        $contentType = $_SERVER["CONTENT_TYPE"] ?? '';
        if(str_contains($contentType, 'application/json')) {
            // Leer datos crudos y decodificar JSON
            $rawBody = file_get_contents('php://input');
            $this->body = json_decode($rawBody, true) ?? [];
        } else {
            // Asumimos que son datos de form estandar.
            $this->body = $_POST;
        }
    }
}