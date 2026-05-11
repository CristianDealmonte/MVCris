<?php
namespace Infrastructure\Http;

use Utils\Dev;

/**
 * Represents the HTTP response sent back to the client.
 * 
 * This class is responsable for:
 *  - Enforcing that only ONE response is sent per request.
 *  - Rendering HTML views using a layout.
 *  - Sending HTML responses for API endpoints.
 * 
 * The response object acts as the final output boundary
 * of the HTTP lifecycle and is shared across the entire
 * middleware and controller pipeline.
 */
class Response {
    /**
     * Indicates whether a response has already been sent.
     * 
     * This flag is used to prevent multiple responses
     * (e.g. sending JSON and then rendering a view).
     * 
     * @var bool
     */
    private bool $sent = false;


    /**
     * Cheks if a response has already been sent.
     * 
     * This method allows the router or error handler
     * to determinate whether it is still safe to emit
     * a response.
     * 
     * @return bool True if a response has been sent.
     */
    public function hasBeenSent() : bool {
        return $this->sent;
    }
    
    
    /**
     * Renders an HTML view inside the main application layout.
     * 
     * This method:
     *  - Prevents multiple responses from being sent.
     *  - Extracts view data into scoped variables.
     *  - Buffers the view output into a $content variable.
     *  - Injects the content into the main layout
     * 
     * @param string $view The view name (relative to the views directory).
     * @param array<string, mixed> $data Data to be passed to the view.
     * 
     * @throws \LogicException If a response has already been sent.
     */
    public function render(string $view, array $data = []) : void{
        if($this->sent) {
            throw new \LogicException('Response already sent');
        }
        $this->sent = true;

        // Make view data available as local variables.
        extract($data, EXTR_SKIP);
        
        // Capture the view output.
        ob_start();
        include __DIR__ . "/../../views/$view.php";
        $content = ob_get_clean();

        // Render the main layout (expects $content variable).
        include __DIR__ . '/../../views/layout/MainLayout.php';
    }


    /**
     * Sends a JSON response to the client.
     * 
     * This method:
     *  - Prevents multiple responses from being sent.
     *  - Sets the appropiate HTTP status code.
     *  - Sets the Content-Type header to application/json.
     *  - Outputs a JSON-encoded response body.
     * 
     * Commonly used by API controllers and error handlers.
     * 
     * @param array<string, mixed> $data The data to encode as JSON.
     * @param int $statusCode The HTTP status code (default: 200).
     * 
     * @throws \LogicException If a response has already been sent.
     */
    public function json(array $data, int $statusCode = 200) : void {
        if($this->sent) {
            throw new \LogicException('Response already sent');
        }
        $this->sent = true;

        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}