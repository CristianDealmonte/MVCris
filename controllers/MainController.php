<?php
namespace Controller;

class MainController {
    public static function index($req, $res) {

        $res->json(["mensaje" => "Buscando el producto con id: "]);
    }
}
