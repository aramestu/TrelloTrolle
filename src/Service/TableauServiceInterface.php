<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Service\Exception\ServiceException;
use Exception;

interface TableauServiceInterface
{
    /**
     * @throws ServiceException
     */
    public function getByCodeTableau(?string $codeTableau): Tableau;

    /**
     * @throws ServiceException
     */
    public function getByIdTableau(?int $idTableau): Tableau;

    /**
     * @throws ServiceException
     * @throws Exception
     */
    public function creerTableau(?string $loginUtilisateurConnecte, ?string $nomTableau): Tableau;

    /**
     * @throws ServiceException
     */
    public function mettreAJourTableau(?int $idTableau, ?string $loginUtilisateurConnecte, ?string $nomtableau): Tableau;

    /**
     * @throws ServiceException
     */
    public function ajouterMembre(?int $idTableau, ?string $loginUtilisateurConnecte, ?string $loginUtilisateurNouveau): Tableau;

    /**
     * @throws ServiceException
     */
    public function supprimerMembre(?int $idTableau, ?string $loginUtilisateurConnecte, ?string $loginUtilisateurDelete): Tableau;

    /**
     * @throws ServiceException
     */
    public function supprimer(?string $loginUtilisateurConnecte, ?int $idTableau): void;

    public function verifierParticipant(?string $loginUtilisateurConnecte, ?int $idTableau): void;

    /**
     * @throws ServiceException
     */
    public function verifierProprietaire(?string $loginUtilisateurConnecte, ?int $idTableau): Tableau;

    public function recupererColonnesEtCartesDuTableau(string $idTableau): array;

    public function informationsAffectationsCartes(string $idTableau): array;

    /**
     * @throws ServiceException
     */
    public function quitterTableau(?string $loginUtilisateurConnecte, ?int $idTableau);
}