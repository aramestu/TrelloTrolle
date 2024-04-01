<?php

namespace App\Trellotrolle\Lib;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Classe JsonWebToken
 *
 * Cette classe est responsable de l'encodage et du décodage des JSON Web Tokens (JWT).
 */
class JsonWebToken
{
    /**
     * Variable $jsonSecret
     *
     * Cette variable est une chaîne de caractères qui représente la clé secrète utilisée pour l'encodage et le décodage des JSON Web Tokens (JWT).
     */
    private static string $jsonSecret = "cQTcHTyBBwfGaFkCez0EJ6";

    /**
     * Méthode encoder
     *
     * Cette méthode est responsable de l'encodage d'un tableau en JSON Web Token (JWT).
     *
     * @param array $contenu Le tableau à encoder
     * @return string Le JWT encodé
     */
    public static function encoder(array $contenu): string
    {
        return JWT::encode($contenu, self::$jsonSecret, 'HS256');
    }

    /**
     * Méthode decoder
     *
     * Cette méthode est responsable du décodage d'un JSON Web Token (JWT) en un tableau.
     *
     * @param string $jwt Le JWT à décoder
     * @return array Le tableau décodé
     */
    public static function decoder(string $jwt): array
    {
        try {
            $decoded = JWT::decode($jwt, new Key(self::$jsonSecret, 'HS256'));
            return (array)$decoded;
        } catch (Exception) {
            return [];
        }
    }

}