<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Service\Exception\ServiceException;

/**
 * Interface CarteServiceInterface
 *
 * Cette interface définit les méthodes pour la gestion des cartes dans le service CarteService.
 *
 * @package App\Trellotrolle\Service
 */
interface CarteServiceInterface
{

    /**
     * Récupère une carte en fonction de son identifiant.
     *
     * @param int|null $idCarte L'identifiant de la carte
     * @return Carte|null La carte correspondante, ou null si aucune carte n'est trouvée
     * @throws ServiceException Si une erreur se produit lors de la récupération de la carte
     */
    public function getCarte(?int $idCarte): ?Carte;

    /**
     * Supprime une carte d'un tableau.
     *
     * @param int|null $idCarte L'identifiant de la carte à supprimer.
     * @param string|null $loginUtilisateurConnecte Le login de l'utilisateur connecté.
     * @return Tableau Le tableau mis à jour après la suppression de la carte.
     * @throws ServiceException Si une erreur se produit lors de la suppression de la carte.
     */
    public function supprimerCarte(?int $idCarte, ?string $loginUtilisateurConnecte): Tableau;

    /**
     * Crée une carte avec les paramètres spécifiés.
     *
     * @param int|null $idColonne L'identifiant de la colonne dans laquelle créer la carte.
     * @param string|null $titreCarte Le titre de la carte.
     * @param string|null $descriptifCarte Le descriptif de la carte.
     * @param string|null $couleurCarte La couleur de la carte.
     * @param string|null $loginUtilisateurConnecte Le login de l'utilisateur connecté.
     * @param array|null $affectations Les affectations de la carte.
     * @return int L'identifiant de la carte créée.
     * @throws ServiceException En cas d'erreur lors de la création de la carte.
     */
    public function creerCarte(?int $idColonne, ?string $titreCarte, ?string $descriptifCarte, ?string $couleurCarte, ?string $loginUtilisateurConnecte, ?array $affectations): int;

    /**
     * Met à jour une carte avec les nouvelles informations spécifiées.
     *
     * @param int|null $idCarte L'identifiant de la carte à mettre à jour
     * @param int|null $idColonne L'identifiant de la colonne à laquelle la carte est associée
     * @param string|null $titreCarte Le nouveau titre de la carte
     * @param string|null $descriptifCarte Le nouveau descriptif de la carte
     * @param string|null $couleurCarte La nouvelle couleur de la carte
     * @param string|null $loginUtilisateurConnecte Le login de l'utilisateur connecté
     * @param array|null $affectations Les nouvelles affectations de la carte
     * @return Carte La carte mise à jour
     * @throws ServiceException Si une erreur se produit lors de la mise à jour de la carte
     */
    public function mettreAJourCarte(?int $idCarte, ?int $idColonne, ?string $titreCarte, ?string $descriptifCarte, ?string $couleurCarte, ?string $loginUtilisateurConnecte, ?array $affectations): Carte;
}