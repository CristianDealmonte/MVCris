<?php
namespace Infrastructure\Http;

/**
 * Represents the incoming HTTP request
 * This class is responsable for extracting and normalizing
 * requests data from PHP superglobals, providing a unified 
 * object-oriented interface to access:
 */
class Request {
    /**
     * Route parameters extracted from dynamic routes.
     * 
     * Example: 
     *  Route: /users/{id}
     *  URL: /users/15
     *  Result: ['id' => '15']
     * 
     * @var array<string, string>
     */
    public array $params = [];


    /**
     * Query string parameters.
     * 
     * Example: 
     *  URL: /products?sort=asc&page=2
     *  Result: ['sort' => 'asc', 'page' => '2']
     * 
     * @var array<string, string>
     */
    public array $query = [];


    /**
     * Parsed request body data.
     * 
     * For JSON requests, this contains the decoded JSON payload.
     * For form submissions, this contains the $_POST data.
     * 
     * @var array<string, mixed>
     */    
    public array $body = [];


    /**
     * HTTP request method.
     * 
     * Common values: 
     *  - GET
     *  - POST
     *  - PUT
     *  - PATCH
     *  - DELETE
     * 
     * @var string
     */
    public string $method;


    /**
     * Request URI path without query string.
     * 
     * Example: 
     *  Full URI: /products/15?sort=asc
     *  Stored as : /products/15
     * 
     * @var string
     */
    public string $url;


    /**
     * Creates a new Request instance.
     * Reads and normalizes data from PHP superglobals:
     *  - $_SERVER
     *  - $_GET
     *  - $_POST
     *  - php://input
     * 
     * The constructor automarically detects the request
     * content type and parses the body accordingly.
     */
    public function __construct() {
        // ===== HTTP Method and URL =====
        $currentUrl = $_SERVER['REQUEST_URI'] ?? '/';
        $this->url = parse_url($currentUrl, PHP_URL_PATH);
        $this->method = $_SERVER['REQUEST_METHOD'];

        // ===== Query Paramethers =====
        $this->query = $_GET;

        // ===== Parse Body Data =====
        $contentType = $_SERVER["CONTENT_TYPE"] ?? '';
        if(str_contains($contentType, 'application/json')) {
            // Read raw input and decode JSON payload.
            $rawBody = file_get_contents('php://input');
            $this->body = json_decode($rawBody, true) ?? [];
        } else {
            // Assume standard form-data submission.
            $this->body = $_POST;
        }
    }
}