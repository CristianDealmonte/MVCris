<?php
namespace Controller;

use Infrastructure\Exceptions\ForbiddenException;
use Model\User;
use Utils\Dev;

class MainController {
    public static function index($req, $res) {

        $newUser = User::getById(1);

        $newUser->email = 'emailcorreo@correo.com';
        $newUser->save();

        return $res->json(['usuario' => $newUser->email]);
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
