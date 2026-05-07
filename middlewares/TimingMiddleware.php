<?php
namespace Middleware;

class TimingMiddleware {
    public function handle($req, $res, $next) : void {
        // Inicio del timer (alta precisión)
        $startTime = microtime(true); 

        echo 'antes de tomar el tiempo';

        // Continuar con la siguiente capa / controlador
        $next();

        // Fin del timer
        $endTime = microtime(true);

        // Tiempo total en milisegundos
        $executionTimeMs = ($endTime - $startTime) * 1000;

        // Opciones de salida:
        
        // 1️⃣ Agregar header HTTP
        // $res->setHeader(
        //     'X-Execution-Time',
        //     round($executionTimeMs, 2) . ' ms'
        // );

        // 2️⃣ (Opcional) Guardarlo en el request para uso posterior
        $req->execution_time = $executionTimeMs;
        echo 'despues de tomar el tiempo';
    }
}
