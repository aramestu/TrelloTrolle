<?php

namespace App\Trellotrolle\Lib;

use Exception;

/**
 * Interface MotDePasseInterface
 *
 * Cette interface définit les méthodes nécessaires pour gérer les mots de passe.
 */
interface MotDePasseInterface
{
    /**
     * Méthode pour hacher un mot de passe en clair.
     *
     * @param string $mdpClair Le mot de passe en clair à hacher.
     * @return string Le mot de passe haché.
     */
    public function hacher(string $mdpClair): string;

    /**
     * Méthode pour vérifier si un mot de passe en clair correspond à un mot de passe haché.
     *
     * @param string $mdpClair Le mot de passe en clair à vérifier.
     * @param string $mdpHache Le mot de passe haché à comparer.
     * @return bool Retourne true si le mot de passe en clair correspond au mot de passe haché, sinon false.
     */
    public function verifier(string $mdpClair, string $mdpHache): bool;

    /**
     * Génère une chaîne aléatoire de caractères.
     *
     * @param int $nbCaracteres Le nombre de caractères de la chaîne générée.
     * @return string La chaîne aléatoire générée.
     * @throws Exception
     */
    public function genererChaineAleatoire(int $nbCaracteres): string;
}