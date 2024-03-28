<?php

namespace App\Trellotrolle\Lib;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JsonWebToken
{
    private static string $jsonSecret = "cQTcHTyBBwfGaFkCez0EJ6";

    public static function encoder(array $contenu) : string {
        return JWT::encode($contenu, self::$jsonSecret, 'HS256');
    }

    public static function decoder(string $jwt) : array {
        try {
            $decoded = JWT::decode($jwt, new Key(self::$jsonSecret, 'HS256'));
            return (array) $decoded;
        } catch (\Exception $exception) {
            return [];
        }
    }

}