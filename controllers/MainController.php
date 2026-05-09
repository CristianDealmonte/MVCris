<?php
namespace Controller;

use Infrastructure\Exceptions\ForbiddenException;

class MainController {
    public static function index($req, $res) {

        $idProducto = $req->params['id'];
        $idComment = $req->params['id_comment'];

        return $res->json(["mensaje" => "Buscando el producto con id: $idProducto con comentario $idComment"]);
    }

    public static function perfil($req, $res) {

        if(!isset($req->user_id)) {
            throw new ForbiddenException('La peticion no contiene toda la info requerida');
        }

        $user = $req->user_id;
        $time = 10;

        $res->json(["mensaje" => "Visitando perfil del usuario $user. Tardo: $time"]);
    }
}
