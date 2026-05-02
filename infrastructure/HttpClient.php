<?php
namespace Infrastructure;

class HttpClient {
    private $defaultHeaders = [];
    private $verifySSL = true;

    public function __construct(array $defaultHeaders = [], bool $verifySSL = true) {
        $this->defaultHeaders = $defaultHeaders;
        $this->verifySSL = $verifySSL;
    }

    public function request(
        string $method, 
        string $url, 
        array | string $data = [], 
        array $headers = [],
        array $options = []
    ): array {
        $ch = curl_init();

        // Configuracion basica
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        // Configurar metodo HTTP
        $method = strtoupper($method);
        switch ($method) {
            case 'POST': 
            case 'PUT':
            case 'PATCH':
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
                if (!empty($data)) {
                    // Si es array, asumimos JSON
                    $payload = is_array($data) ? json_encode($data) : $data;
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                    $allHeaders[] = 'Content-Type: application/json';
                }
                break;
            case 'GET':
                if(!empty($data)) {
                    $url .= '?' . http_build_query($data);
                    curl_setopt($ch, CURLOPT_URL, $url);
                }
                break;
        }

        // Configurar headers
        $allHeaders = array_merge($this->defaultHeaders, $headers);
        if(!empty($allHeaders)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $allHeaders);
        }

        // Configurar SSL
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->verifySSL);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->verifySSL ? 2 : 0);

        // Opciones adicionales 
        foreach ($options as $opt => $val) {
            curl_setopt($ch, $opt, $val);
        }

        // Ejecutar la solicitud
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if($error) {
            throw new \Exception("Error en la solicitud HTTP: $error");
        }

        // Intentar decodificar como JSON
        $decoded = json_decode($response, true);
        $isJson = json_last_error() === JSON_ERROR_NONE;

        return [
            'status' => $httpCode,
            'body'   => $isJson ? $decoded : $response,
            'headers'=> $allHeaders
        ];
    }
}