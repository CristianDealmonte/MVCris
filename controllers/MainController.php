<?php
namespace Controller;

class MainController {
    public static function index($req, $res) {
        $id = $req->params['id'];

        $res->json(["mensaje" => "Buscando el producto con id: $id"]);
    }
}
