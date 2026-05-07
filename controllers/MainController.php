<?php
namespace Controller;

use Utils\Dev;

class MainController {
    public static function index($req, $res) {

        $idProducto = $req->params['id'];
        $idComment = $req->params['id_comment'];

        $res->json(["mensaje" => "Buscando el producto con id: $idProducto con comentario $idComment"]);
    }

    public static function perfil($req, $res) {

        // $time = $req->execution_time;
        $time = 10;
        $idUser = $req->user_id;

        // Dev::debug($req);

        $res->json(["mensaje" => "Visitando perfil del susuario $idUser. Tardo: $time"]);
    }
}
