<?php

namespace App\Trellotrolle\Lib;

use App\Trellotrolle\Modele\HTTP\Session;

/**
 * Classe ConnexionUtilisateur
 *
 * Cette classe gère la connexion et la déconnexion des utilisateurs, ainsi que la vérification de leur statut de connexion.
 * Elle utilise la classe Session pour enregistrer et supprimer les informations de connexion dans la session.
 * La classe utilise également la classe UtilisateurRepository pour vérifier si un utilisateur est connecté.
 *
 */
class ConnexionUtilisateur
{

    /**
 * Variable privée statique pour stocker la clé de connexion de l'utilisateur.
 *
 * @var string
 */
    private static string $cleConnexion = "_utilisateurConnecte";

    /**
 * Méthode connecter
 *
 * Cette méthode permet de connecter un utilisateur en enregistrant son login dans la session en cours.
 *
 * @param string $loginUtilisateur Le login de l'utilisateur à connecter
 * @return void
 */
    public static function connecter(string $loginUtilisateur): void
    {
        $session = Session::getInstance();
        $session->enregistrer(ConnexionUtilisateur::$cleConnexion, $loginUtilisateur);
    }

    /**
 * Méthode estConnecte
 *
 * Cette méthode vérifie si un utilisateur est connecté en vérifiant si la clé de connexion de l'utilisateur existe dans la session en cours.
 *
 * @return bool Retourne true si l'utilisateur est connecté, sinon false
 */
    public static function estConnecte(): bool
    {
        $session = Session::getInstance();
        return $session->contient(ConnexionUtilisateur::$cleConnexion);
    }

    /**
 * Méthode deconnecter
 *
 * Cette méthode permet de déconnecter l'utilisateur en supprimant la clé de connexion de l'utilisateur de la session en cours.
 *
 * @return void
 */
    public static function deconnecter() : void
    {
        $session = Session::getInstance();
        $session->supprimer(ConnexionUtilisateur::$cleConnexion);
    }

    /**
 * Méthode getLoginUtilisateurConnecte
 *
 * Cette méthode retourne le login de l'utilisateur connecté en utilisant la classe Session.
 * Si la clé de connexion de l'utilisateur existe dans la session en cours, la méthode retourne la valeur correspondante.
 * Sinon, la méthode retourne null.
 *
 * @return string|null Le login de l'utilisateur connecté, ou null s'il n'y a pas d'utilisateur connecté
 */
    public static function getLoginUtilisateurConnecte(): ?string
    {
        $session = Session::getInstance();
        if ($session->contient(ConnexionUtilisateur::$cleConnexion)) {
            return $session->lire(ConnexionUtilisateur::$cleConnexion);
        } else
            return null;
    }

    /**
 * Méthode estUtilisateur
 *
 * Cette méthode vérifie si un utilisateur est connecté et correspond au login donné.
 *
 * @param string $login Le login de l'utilisateur à vérifier
 * @return bool Retourne true si l'utilisateur est connecté et correspond au login donné, sinon false
 */
    public static function estUtilisateur(string $login): bool
    {
        return (ConnexionUtilisateur::estConnecte() &&
            ConnexionUtilisateur::getLoginUtilisateurConnecte() == $login
        );
    }
}
