<?php

namespace App\Trellotrolle\Lib;

/**
 * Interface ConnexionUtilisateurInterface
 *
 * Cette interface définit les méthodes nécessaires pour gérer la connexion des utilisateurs.
 */
interface ConnexionUtilisateurInterface
{
    /**
     * Méthode connecter
     *
     * Cette méthode permet de connecter un utilisateur en utilisant son identifiant.
     *
     * @param string $idUtilisateur L'identifiant de l'utilisateur à connecter.
     * @return void
     */
    public function connecter(string $idUtilisateur): void;

    /**
     * Méthode estConnecte()
     *
     * Cette méthode retourne un booléen indiquant si l'utilisateur est connecté ou non.
     *
     * @return bool
     */
    public function estConnecte(): bool;

    /**
     * Méthode deconnecter
     *
     * Cette méthode permet de déconnecter l'utilisateur.
     *
     * @return void
     */
    public function deconnecter(): void;

    /**
     * Méthode getIdUtilisateurConnecte
     *
     * Cette méthode retourne l'identifiant de l'utilisateur connecté.
     *
     * @return string|null L'identifiant de l'utilisateur connecté, ou null s'il n'y a pas d'utilisateur connecté.
     */
    public function getIdUtilisateurConnecte(): ?string;
}