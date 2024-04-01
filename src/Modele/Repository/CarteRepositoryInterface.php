<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\Carte;

/**
 * Interface CarteRepositoryInterface
 *
 * Cette interface définit les méthodes pour interagir avec la base de données concernant les cartes.
 */
interface CarteRepositoryInterface
{

    /**
     * Récupère les cartes d'une colonne spécifiée.
     *
     * @param int $idcolonne L'identifiant de la colonne.
     * @return array Les cartes de la colonne.
     */
    public function recupererCartesColonne(int $idcolonne): array;

    /**
     * Récupère les cartes d'un tableau spécifié.
     *
     * @param int $idTableau L'identifiant du tableau.
     * @return array Les cartes du tableau.
     */
    public function recupererCartesTableau(int $idTableau): array;

    /**
     * Récupère les cartes associées à un utilisateur.
     *
     * @param string $login Le login de l'utilisateur
     * @return Carte[] Un tableau contenant les cartes associées à l'utilisateur
     */
    public function recupererCartesUtilisateur(string $login): array;

    /**
     * Récupère le nombre total de cartes associées à un utilisateur.
     *
     * @param string $login Le login de l'utilisateur.
     * @return int Le nombre total de cartes associées à l'utilisateur.
     */
    public function getNombreCartesTotalUtilisateur(string $login): int;

    /**
     * Récupère les affectations des cartes pour une carte spécifiée.
     *
     * @param int $idCarte L'identifiant de la carte.
     * @return array Les affectations des cartes pour la carte spécifiée.
     */
    public function recupererAffectationsCartes(int $idCarte): array;

    /**
     * Supprime toutes les affectations de cartes pour une carte spécifiée.
     *
     * @param int $idCarte L'identifiant de la carte.
     * @return bool Retourne true si les affectations ont été supprimées avec succès, sinon false.
     */
    public function supprimerToutesAffectationsCarte(int $idCarte): bool;

    /**
     * Ajoute une affectation pour une carte spécifiée.
     *
     * @param string $login Le login de l'utilisateur.
     * @param int $idCarte L'identifiant de la carte.
     * @return bool Retourne true si l'affectation a été ajoutée avec succès, sinon false.
     */
    public function ajouterAffectation(string $login, int $idCarte): bool;

    /**
     * Supprime une affectation pour une carte spécifiée.
     *
     * @param string $login Le login de l'utilisateur.
     * @param int $idCarte L'identifiant de la carte.
     * @return bool Retourne true si l'affectation a été supprimée avec succès, sinon false.
     */
    public function supprimerAffectation(string $login, int $idCarte): bool;

    /**
     * Supprime une entrée de la base de données en utilisant la valeur de la clé primaire spécifiée.
     *
     * @param string $valeurClePrimaire La valeur de la clé primaire de l'entrée à supprimer.
     * @return bool Retourne true si l'entrée a été supprimée avec succès, sinon false.
     */
    public function supprimer(string $valeurClePrimaire): bool;

    /**
     * Méthode lastInsertId()
     *
     * Cette méthode retourne l'identifiant de la dernière ligne insérée dans la base de données.
     *
     * @return false|string L'identifiant de la dernière ligne insérée, ou false si aucune ligne n'a été insérée.
     */
    public function lastInsertId(): false|string;
}