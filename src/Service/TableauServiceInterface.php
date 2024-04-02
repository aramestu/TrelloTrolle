<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Service\Exception\ServiceException;
use Exception;

/**
 * Interface TableauServiceInterface
 *
 * Cette interface définit les méthodes pour gérer les tableaux.
 */
interface TableauServiceInterface
{

    /**
     * Récupère un objet Tableau en fonction du code du tableau.
     *
     * @param string|null $codeTableau Le code du tableau.
     * @return Tableau L'objet Tableau correspondant.
     * @throws ServiceException Si une erreur se produit lors de la récupération du tableau.
     */
    public function getByCodeTableau(?string $codeTableau): Tableau;

    /**
     * Récupère un objet Tableau en fonction de son identifiant.
     *
     * @param int|null $idTableau L'identifiant du tableau.
     * @return Tableau L'objet Tableau correspondant à l'identifiant spécifié.
     * @throws ServiceException Si une erreur se produit lors de la récupération du tableau.
     */
    public function getByIdTableau(?int $idTableau): Tableau;

    /**
     * Crée un nouveau tableau avec le nom spécifié.
     *
     * @param string|null $loginUtilisateurConnecte Le login de l'utilisateur connecté.
     * @param string|null $nomTableau Le nom du tableau à créer.
     * @return Tableau Le nouveau tableau créé.
     * @throws ServiceException Si une erreur liée au service se produit.
     * @throws Exception Si une erreur générale se produit.
     */
    public function creerTableau(?string $loginUtilisateurConnecte, ?string $nomTableau): Tableau;

    /**
     * Met à jour un tableau.
     *
     * @param int|null $idTableau L'identifiant du tableau à mettre à jour.
     * @param string|null $loginUtilisateurConnecte Le login de l'utilisateur connecté.
     * @param string|null $nomtableau Le nouveau nom du tableau.
     * @return Tableau L'objet Tableau mis à jour.
     * @throws ServiceException Si une erreur se produit lors de la mise à jour du tableau.
     */
    public function mettreAJourTableau(?int $idTableau, ?string $loginUtilisateurConnecte, ?string $nomtableau): Tableau;

    /**
     * Ajoute un membre à un tableau.
     *
     * @param int|null $idTableau L'identifiant du tableau.
     * @param string|null $loginUtilisateurConnecte Le login de l'utilisateur connecté.
     * @param string|null $loginUtilisateurNouveau Le login du nouvel utilisateur à ajouter.
     * @return Tableau Le tableau mis à jour avec le nouveau membre ajouté.
     * @throws ServiceException Si une erreur se produit lors de l'ajout du membre.
     */
    public function ajouterMembre(?int $idTableau, ?string $loginUtilisateurConnecte, ?string $loginUtilisateurNouveau): Tableau;

    /**
     * Supprime un membre d'un tableau.
     *
     * @param int|null $idTableau L'identifiant du tableau.
     * @param string|null $loginUtilisateurConnecte Le login de l'utilisateur connecté.
     * @param string|null $loginUtilisateurDelete Le login de l'utilisateur à supprimer.
     * @return Tableau L'objet Tableau modifié.
     * @throws ServiceException Si une erreur se produit lors de la suppression du membre.
     */
    public function supprimerMembre(?int $idTableau, ?string $loginUtilisateurConnecte, ?string $loginUtilisateurDelete): Tableau;

    /**
     * Supprime un tableau pour un utilisateur connecté.
     *
     * @param string|null $loginUtilisateurConnecte Le login de l'utilisateur connecté.
     * @param int|null $idTableau L'identifiant du tableau à supprimer.
     * @throws ServiceException En cas d'erreur lors de la suppression du tableau.
     * @return void
     */
    public function supprimer(?string $loginUtilisateurConnecte, ?int $idTableau): void;

    /**
     * Vérifie si l'utilisateur connecté est un participant du tableau spécifié.
     *
     * @param string|null $loginUtilisateurConnecte Le login de l'utilisateur connecté.
     * @param int|null $idTableau L'identifiant du tableau.
     * @return void
     */
    public function verifierParticipant(?string $loginUtilisateurConnecte, ?int $idTableau): void;

    /**
     * Vérifie si l'utilisateur connecté est le propriétaire du tableau.
     *
     * @param string|null $loginUtilisateurConnecte Le login de l'utilisateur connecté.
     * @param int|null $idTableau L'identifiant du tableau.
     * @return Tableau L'objet Tableau correspondant.
     * @throws ServiceException Si une erreur liée au service se produit.
     */
    public function verifierProprietaire(?string $loginUtilisateurConnecte, ?int $idTableau): Tableau;

    /**
     * Récupère les colonnes et les cartes d'un tableau spécifié.
     *
     * @param string $idTableau L'identifiant du tableau.
     * @return array Les colonnes et les cartes du tableau.
     */
    public function recupererColonnesEtCartesDuTableau(string $idTableau): array;

    /**
     * Récupère les informations sur les affectations des cartes pour un tableau spécifié.
     *
     * @param string $idTableau L'identifiant du tableau.
     * @return array Les informations sur les affectations des cartes.
     */
    public function informationsAffectationsCartes(string $idTableau): array;

    /**
     * Méthode pour quitter un tableau.
     *
     * @param string|null $loginUtilisateurConnecte Le login de l'utilisateur connecté.
     * @param int|null $idTableau L'identifiant du tableau.
     * @throws ServiceException En cas d'erreur lors de la sortie du tableau.
     */
    public function quitterTableau(?string $loginUtilisateurConnecte, ?int $idTableau);
}