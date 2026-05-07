<?php
namespace Infrastructure\Http;

class Response {
    
    // ===== Show Static View =====
    public function render(string $view, array $data = []) : void{
        extract($data, EXTR_SKIP);                  // extract the data values in individual variables
        
        ob_start();                                 // start memory storage
        include __DIR__ . "/../views/$view.php";    // Including the specific view
        $content = ob_get_clean();                  // Clean buffer and set the view as string

        include __DIR__ . '/../views/layout/MainLayout.php';
    }

    public function json(array $data, int $statusCode = 200) : void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}