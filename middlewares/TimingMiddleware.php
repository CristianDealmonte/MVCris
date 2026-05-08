<?php
namespace Middleware;

class TimingMiddleware {
    public function handle($req, $res, $next) : void {
        // Inicio del timer (alta precisión)
        $startTime = microtime(true); 

        // echo "\n comienza la toma de tiempo\n";
        
        // $res->json(['error' => 'antes de tomar tiempo'], 401);
        // return;
        // echo "\ndespues de retornar la respuesta\n";

        // Continuar con la siguiente capa / controlador
        $next();

        // Fin del timer
        $endTime = microtime(true);
        $executionTimeMs = ($endTime - $startTime) * 1000;

        // echo "\ termina de tomar el tiempo\n";
        // echo "\ntiempo de ejecucion: " . round($executionTimeMs, 2) . " ms\n";
    }
}
