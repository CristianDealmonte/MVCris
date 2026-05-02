<?php
namespace Utils;

class Dev {
    public static function debug($data) {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
        exit; // Detener la ejecución después de mostrar el debug
    }
}