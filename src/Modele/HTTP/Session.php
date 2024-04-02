<?php

namespace App\Trellotrolle\Modele\HTTP;

use App\Trellotrolle\Configuration\ConfigurationSite;
use Exception;

/**
 * Classe Session
 *
 * Cette classe gère la session en cours.
 * Elle permet de démarrer la session, vérifier la dernière activité, accéder à l'instance de la session, vérifier si un nom existe dans la session, enregistrer une valeur dans la session, lire une valeur de la session, supprimer une valeur de la session et détruire la session.
 * La classe utilise la classe ConfigurationSite pour obtenir la durée d'expiration de la session.
 */
class Session
{
    private static ?Session $instance = null;

    /**
     * Constructeur de la classe Session.
     * Démarre la session en cours.
     * Lance une exception si la session ne peut pas démarrer.
     * @throws Exception
     */
    private function __construct()
    {
        if (session_start() === false) {
            throw new Exception("La session n'a pas réussi à démarrer.");
        }
    }

    /**
     * Méthode verifierDerniereActivite
     *
     * Cette méthode vérifie la dernière activité de la session en cours.
     * Si la durée d'expiration de la session est différente de zéro et la dernière activité dépasse cette durée,
     * la session est réinitialisée.
     * La méthode met également à jour la valeur de la dernière activité de la session.
     *
     * @param int $dureeExpiration La durée d'expiration de la session en secondes
     * @return void
     */
    public function verifierDerniereActivite(int $dureeExpiration): void
    {
        if ($dureeExpiration == 0)
            return;

        if (isset($_SESSION['derniereActivite']) && (time() - $_SESSION['derniereActivite'] > ($dureeExpiration)))
            session_unset();

        $_SESSION['derniereActivite'] = time();
    }

    /**
     * Méthode getInstance
     *
     * Cette méthode retourne une instance de la classe Session.
     * Si l'instance n'existe pas, elle est créée en utilisant le constructeur de la classe Session.
     * La méthode vérifie également la durée d'expiration de la session en utilisant la méthode getDureeExpirationSession de la classe ConfigurationSite.
     *
     * @return Session L'instance de la classe Session
     */
    public static function getInstance(): Session
    {
        if (is_null(static::$instance)) {
            static::$instance = new Session();

            // Durée d'expiration des sessions en secondes
            $dureeExpiration = ConfigurationSite::getDureeExpirationSession();
            static::$instance->verifierDerniereActivite($dureeExpiration);
        }
        return static::$instance;
    }

    /**
     * Méthode contient
     *
     * Cette méthode vérifie si une clé existe dans la session en cours.
     *
     * @param string $nom Le nom de la clé à vérifier
     * @return bool Retourne true si la clé existe dans la session, sinon false
     */
    public function contient(string $nom): bool
    {
        return isset($_SESSION[$nom]);
    }

    /**
     * Méthode enregistrer
     *
     * Cette méthode enregistre une valeur dans la session en cours.
     *
     * @param string $nom Le nom de la clé dans la session
     * @param mixed $valeur La valeur à enregistrer dans la session
     * @return void
     */
    public function enregistrer(string $nom, mixed $valeur): void
    {
        $_SESSION[$nom] = $valeur;
    }

    /**
     * Méthode lire
     *
     * Cette méthode permet de lire une valeur dans la session en cours.
     *
     * @param string $nom Le nom de la clé dans la session
     * @return mixed La valeur correspondante dans la session
     */
    public function lire(string $nom): mixed
    {
        if ($this->contient($nom)) return $_SESSION[$nom];
        return null;
    }

    /**
     * Méthode supprimer
     *
     * Cette méthode supprime une clé de la session en cours.
     *
     * @param string $nom Le nom de la clé à supprimer
     * @return void
     */
    public function supprimer(string $nom): void
    {
        unset($_SESSION[$nom]);
    }

    /**
     * Méthode detruire
     *
     * Cette méthode détruit la session en cours.
     * Elle supprime toutes les variables de session, détruit la session elle-même,
     * supprime le cookie de session et réinitialise l'instance de la classe Session à null.
     *
     * @return void
     */
    public function detruire(): void
    {
        session_unset();
        session_destroy();
        Cookie::supprimer(session_name());
        Session::$instance = null;
    }
}