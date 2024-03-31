<?php

namespace App\Trellotrolle\Lib;

use Exception;

/**
 * Classe MotDePasse
 *
 * Cette classe implémente l'interface MotDePasseInterface et fournit des méthodes pour hacher,
 * vérifier et générer une chaîne aléatoire pour les mots de passe.
 *
 * @package App\Trellotrolle\Lib
 */
class MotDePasse implements MotDePasseInterface
{
    /**
     * Cette fonction prend en paramètre une chaîne de caractères $mdpClair et retourne une chaîne de caractères
     * hachée à l'aide de l'algorithme PASSWORD_BCRYPT.
     *
     * @param string $mdpClair La chaîne de caractères à hacher.
     * @return string La chaîne de caractères hachée.
     */
    public function hacher(string $mdpClair): string
    {
        return password_hash($mdpClair, PASSWORD_BCRYPT);
    }

    /**
     * Cette fonction prend en paramètre une chaîne de caractères $mdpClair et une chaîne de caractères $mdpHache,
     * et retourne un booléen indiquant si $mdpClair correspond à $mdpHache.
     *
     * @param string $mdpClair La chaîne de caractères à vérifier.
     * @param string $mdpHache La chaîne de caractères hachée à vérifier.
     * @return bool True si $mdpClair correspond à $mdpHache, False sinon.
     */
    public function verifier(string $mdpClair, string $mdpHache): bool
    {
        return password_verify($mdpClair, $mdpHache);
    }

    /**
     * Cette fonction génère une chaîne de caractères aléatoire de longueur $nbCaracteres en utilisant la fonction random_bytes
     * et la fonction bin2hex pour convertir les octets en une chaîne hexadécimale.
     *
     * @param int $nbCaracteres Le nombre de caractères de la chaîne aléatoire à générer (par défaut 22).
     * @throws Exception Si une erreur se produit lors de la génération des octets aléatoires.
     * @return string La chaîne de caractères aléatoire générée.
     */
    public function genererChaineAleatoire(int $nbCaracteres = 22): string
    {
        $octetsAleatoires = random_bytes($nbCaracteres/2);
        return bin2hex($octetsAleatoires);
    }
}