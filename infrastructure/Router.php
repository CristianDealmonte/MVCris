<?php
namespace Infrastructure;

class Router {
    // Valida las rutas, que existan y que se les asignen un controlador 
    public $routesGET = [];
    public $routesPOST = [];

    public function get($url, $fn) {
        $this->routesGET[$url] = $fn;
    }

    public function post($url, $fn) {
        $this->routesPOST[$url] = $fn;
    }

    public function verifyRoutes() {
        $url = $_GET['url'] ? '/' . $_GET['url'] : '/';  // obtiene la url del navegador
        $method = $_SERVER['REQUEST_METHOD'];   // obtiene el metodo realizado (get/post)

        // realiza busqueda del controlador asociado a la ruta
        $fn = $method === 'GET' ? $this->routesGET[$url] ?? null : $this->routesPOST[$url] ?? null;

        if($fn) {
            // si existe la ruta, se ejecuta el controlador asociado
            call_user_func($fn, $this);
        } else {
            // si no existe la ruta, se muestra un mensaje de error
            http_response_code(404);
            echo "Página no encontrada";
        }
    }

        // ===== Mostrar vistas =====
    public function render($view, $data = []) {
        // convierte el arreglo asociativo en variables, la llave del array se convierte en la variable y
        // el valor de la llave se convierte en el valor de la variable
        extract($data);
        
        ob_start();                                     // Inicializacion de almacenamiento en memoria 
        include __DIR__ . "/../views/$view.php";        // Inclusion de la vista indicada
        $content = ob_get_clean();                    // Limpia el buffer y lo asigna como string 

        // $content ahora es una variable con la vista completa y estara disponible en el layout principal
        include __DIR__ . '/../views/layout/MainLayout.php';       
    }
}