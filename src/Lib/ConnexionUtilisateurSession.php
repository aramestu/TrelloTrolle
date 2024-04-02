<?php

namespace App\Trellotrolle\Lib;

use App\Trellotrolle\Modele\HTTP\Session;


/**
 * Classe ConnexionUtilisateurSession
 *
 * Cette classe implémente l'interface ConnexionUtilisateurInterface et gère la connexion de l'utilisateur via une session.
 * Elle utilise la classe Session pour gérer la session utilisateur.
 *
 * @package App\Trellotrolle\Lib
 */
class ConnexionUtilisateurSession implements ConnexionUtilisateurInterface
{
    /**
     * Variable privée représentant la clé de connexion de l'utilisateur.
     *
     * @var string $cleConnexion
     */
    private string $cleConnexion = "_utilisateurConnecte";

    /**
     * Méthode connecter
     *
     * Cette méthode permet de connecter un utilisateur en enregistrant son identifiant dans la session.
     *
     * @param string $idUtilisateur L'identifiant de l'utilisateur à connecter
     * @return void
     */
    public function connecter(string $idUtilisateur): void
    {
        $session = Session::getInstance();
        $session->enregistrer($this->cleConnexion, $idUtilisateur);
    }

    /**
     * Méthode estConnecte
     *
     * Cette méthode vérifie si l'utilisateur est connecté en vérifiant si la session contient la clé de connexion.
     *
     * @return bool Retourne true si l'utilisateur est connecté, sinon false.
     */
    public function estConnecte(): bool
    {
        $session = Session::getInstance();
        return $session->contient($this->cleConnexion);
    }

    /**
     * Méthode deconnecter
     *
     * Cette méthode permet de déconnecter l'utilisateur en supprimant la clé de connexion de la session.
     *
     * @return void
     */
    public function deconnecter(): void
    {
        $session = Session::getInstance();
        $session->supprimer($this->cleConnexion);
    }

    /**
     * Méthode getIdUtilisateurConnecte
     *
     * Cette méthode permet d'obtenir l'identifiant de l'utilisateur connecté en vérifiant la clé de connexion dans la session.
     *
     * @return string|null Retourne l'identifiant de l'utilisateur connecté s'il existe, sinon retourne null.
     */
    public function getIdUtilisateurConnecte(): ?string
    {
        $session = Session::getInstance();
        if ($session->contient($this->cleConnexion)) {
            return $session->lire($this->cleConnexion);
        } else
            return null;
    }
}
