<?php

namespace App\Trellotrolle\Lib;

use Exception;

class MotDePasse implements MotDePasseInterface
{
    private string $poivre = "5QcWU25xA5XriYkC4HzgVN";

    public function hacher(string $mdpClair): string
    {
        return $this->poivre.hash('sha256', $mdpClair);
    }

    public function verifier(string $mdpClair, string $mdpHache): bool
    {
        return $this->hacher($mdpClair) === $mdpHache;
    }

    /**
     * @throws Exception
     */
    public function genererChaineAleatoire(int $nbCaracteres = 22): string
    {
        // 22 caractères par défaut pour avoir au moins 128 bits aléatoires
        $octetsAleatoires = random_bytes(ceil($nbCaracteres * 6 / 8));
        return substr(base64_encode($octetsAleatoires), 0, $nbCaracteres);
    }
}