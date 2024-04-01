<?php

namespace App\Trellotrolle\Lib;

use App\Trellotrolle\Modele\HTTP\Cookie;

/**
 * Classe ConnexionUtilisateurJWT
 *
 * Cette classe implémente l'interface ConnexionUtilisateurInterface et gère la connexion et la déconnexion des utilisateurs en utilisant JSON Web Tokens (JWT).
 * Les utilisateurs sont connectés en enregistrant un JWT contenant l'ID de l'utilisateur dans un cookie.
 * La méthode estConnecte() vérifie si un utilisateur est connecté en vérifiant si le cookie contenant le JWT existe.
 * La méthode deconnecter() supprime le cookie contenant le JWT pour déconnecter l'utilisateur.
 * La méthode getIdUtilisateurConnecte() récupère l'ID de l'utilisateur connecté en décodant le JWT contenu dans le cookie.
 */
class ConnexionUtilisateurJWT implements ConnexionUtilisateurInterface
{

    /**
     * Méthode connecter
     *
     * Cette méthode est responsable de la connexion d'un utilisateur en enregistrant un token d'authentification dans un cookie.
     *
     * @param string $idUtilisateur L'identifiant de l'utilisateur
     * @return void
     */
    public function connecter(string $idUtilisateur): void
    {
        Cookie::enregistrer("auth_token", JsonWebToken::encoder(["idUtilisateur" => $idUtilisateur]));
    }

    /**
     * Méthode estConnecte
     *
     * Cette méthode vérifie si un utilisateur est connecté en appelant la méthode getIdUtilisateurConnecte() et en vérifiant si le résultat est différent de null.
     *
     * @return bool Retourne true si un utilisateur est connecté, sinon false.
     */
    public function estConnecte(): bool
    {
        return !is_null($this->getIdUtilisateurConnecte());
    }

    /**
     * Méthode deconnecter
     *
     * Supprime le cookie "auth_token" s'il existe.
     */
    public function deconnecter(): void
    {
        if (Cookie::contient("auth_token"))
            Cookie::supprimer("auth_token");

    }

    /**
     * Méthode getIdUtilisateurConnecte
     *
     * Cette méthode retourne l'identifiant de l'utilisateur connecté en utilisant le JSON Web Token (JWT) stocké dans le cookie "auth_token".
     *
     * @return string|null L'identifiant de l'utilisateur connecté ou null s'il n'y a pas de JWT valide dans le cookie "auth_token"
     */
    public function getIdUtilisateurConnecte(): ?string
    {
        if (Cookie::contient("auth_token")) {
            $jwt = Cookie::lire("auth_token");
            $donnees = JsonWebToken::decoder($jwt);
            return $donnees["idUtilisateur"] ?? null;
        } else
            return null;
    }
}