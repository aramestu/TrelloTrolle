<?php

namespace App\Trellotrolle\Modele\HTTP;

use Exception;

/**
 * Classe Cookie
 *
 * Cette classe contient des méthodes statiques pour gérer les cookies.
 */
class Cookie
{

    /**
     * Vérifie si la clé spécifiée existe dans le tableau des cookies.
     *
     * @param string $cle La clé à vérifier.
     * @return bool Retourne true si la clé existe, sinon false.
     */
    public static function contient(string $cle): bool
    {
        return isset($_COOKIE[$cle]);
    }

    /**
     * Enregistre un cookie avec une clé, une valeur et une durée d'expiration optionnelle.
     *
     * @param string $cle La clé du cookie.
     * @param mixed $valeur La valeur du cookie.
     * @param int|null $dureeExpiration La durée d'expiration du cookie en secondes (optionnelle).
     * @return void
     * @throws Exception
     */
    public static function enregistrer(string $cle, mixed $valeur, ?int $dureeExpiration = null): void
    {
        $valeurJSON = json_encode($valeur);
        if ($valeurJSON === false) {
            throw new Exception('Error encoding value to JSON');
        }
        if ($dureeExpiration === null)
            setcookie($cle, $valeurJSON, 0);
        else
            setcookie($cle, $valeurJSON, time() + $dureeExpiration);
    }

    /**
     * Méthode lire
     *
     * Cette méthode permet de désérialiser la valeur d'un cookie spécifié par sa clé.
     *
     * @param string $cle La clé du cookie à lire.
     * @return mixed La valeur désérialisée du cookie.
     */
    public static function lire(string $cle): mixed
    {
        if (isset($_COOKIE[$cle])) {
            return json_decode($_COOKIE[$cle]);
        }
        return null;
    }

    /**
     * Méthode supprimer
     *
     * Cette méthode supprime un cookie en utilisant sa clé.
     *
     * @param string $cle La clé du cookie à supprimer.
     * @return void
     */
    public static function supprimer(string $cle): void
    {
        unset($_COOKIE[$cle]);
        setcookie($cle, "", time() - 3600);
    }
}