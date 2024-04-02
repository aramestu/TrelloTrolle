<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Service\Exception\ServiceException;
use Exception;

/**
 * Interface ColonneServiceInterface
 *
 * Cette interface définit les méthodes pour gérer les colonnes dans un tableau.
 */
interface ColonneServiceInterface
{

    /**
     * Récupère une colonne en fonction de son identifiant.
     *
     * @param int|null $idColonne L'identifiant de la colonne.
     * @return Colonne L'instance de la classe Colonne correspondante.
     * @throws ServiceException Si une erreur se produit lors de la récupération de la colonne.
     */
    public function getColonne(?int $idColonne): Colonne;

    /**
     * Supprime une colonne.
     *
     * @param int|null $idColonne L'identifiant de la colonne à supprimer.
     * @param string|null $loginUtilisateurConnecte Le login de l'utilisateur connecté.
     * @throws ServiceException En cas d'erreur lors de la suppression de la colonne.
     * @return void
     */
    public function supprimerColonne(?int $idColonne, ?string $loginUtilisateurConnecte): void;

    /**
     * Crée une colonne dans un tableau.
     *
     * @param int|null $idTableau L'identifiant du tableau.
     * @param string|null $nomColonne Le nom de la colonne.
     * @param string|null $loginUtilisateurConnecte Le login de l'utilisateur connecté.
     * @return int L'identifiant de la colonne créée.
     * @throws ServiceException Si une erreur liée au service se produit.
     * @throws Exception Si une erreur générale se produit.
     */
    public function creerColonne(?int $idTableau, ?string $nomColonne, ?string $loginUtilisateurConnecte): int;

    /**
     * Met à jour une colonne.
     *
     * @param int|null $idColonne L'identifiant de la colonne à mettre à jour.
     * @param string|null $nomColonne Le nouveau nom de la colonne.
     * @param string|null $loginUtilisateurConnecte Le login de l'utilisateur connecté.
     * @return Colonne La colonne mise à jour.
     * @throws ServiceException Si une erreur se produit lors de la mise à jour de la colonne.
     */
    public function mettreAJour(?int $idColonne, ?string $nomColonne, ?string $loginUtilisateurConnecte): Colonne;

    /**
     * Récupère les colonnes d'un tableau.
     *
     * @param int|null $idTableau L'identifiant du tableau (optionnel).
     * @return array Les colonnes du tableau.
     * @throws ServiceException Si une erreur liée au service se produit.
     */
    public function recupererColonnesTableau(int|null $idTableau): array;
}