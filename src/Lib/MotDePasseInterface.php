<?php

namespace App\Trellotrolle\Lib;

use Exception;

interface MotDePasseInterface
{
    public function hacher(string $mdpClair): string;

    public function verifier(string $mdpClair, string $mdpHache): bool;

    /**
     * @throws Exception
     */
    public function genererChaineAleatoire(int $nbCaracteres): string;
}