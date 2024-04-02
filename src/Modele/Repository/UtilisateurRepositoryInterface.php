<?php

namespace App\Trellotrolle\Modele\Repository;

/**
 * Interface UtilisateurRepositoryInterface
 *
 * Cette interface définit les méthodes pour récupérer des utilisateurs à partir de leur email,
 * récupérer des utilisateurs triés par prénom et nom, récupérer les tableaux dont un utilisateur est membre,
 * et supprimer un utilisateur à partir de sa clé primaire.
 */
interface UtilisateurRepositoryInterface
{

    /**
 * Méthode pour récupérer les utilisateurs par email.
 *
 * @param string $email L'email des utilisateurs à récupérer.
 * @return array Les utilisateurs correspondants à l'email donné.
 */
    public function recupererUtilisateursParEmail(string $email): array;

    /**
 * Méthode pour récupérer les utilisateurs triés par prénom et nom.
 *
 * @return array Les utilisateurs triés par prénom et nom.
 */
    public function recupererUtilisateursOrderedPrenomNom(): array;

    /**
 * Méthode pour récupérer les tableaux où un utilisateur est membre.
 *
 * @param string $login Le login de l'utilisateur.
 * @return array Les tableaux où l'utilisateur est membre.
 */
    public function recupererTableauxOuUtilisateurEstMembre(string $login): array;

    /**
 * Méthode pour supprimer un élément en utilisant sa clé primaire.
 *
 * @param string $valeurClePrimaire La valeur de la clé primaire de l'élément à supprimer.
 * @return bool Retourne true si l'élément a été supprimé avec succès, sinon false.
 */
    public function supprimer(string $valeurClePrimaire): bool;
}