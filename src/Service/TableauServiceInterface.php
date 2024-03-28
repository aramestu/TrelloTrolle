<?php
namespace App\Trellotrolle\Service;
use App\Trellotrolle\Service\Exception\ServiceException;

interface TableauServiceInterface
{
    /**
     * @throws ServiceException
     */
    public function getByCodeTableau(?string $codeTableau): \App\Trellotrolle\Modele\DataObject\Tableau;

    /**
     * @throws ServiceException
     */
    public function getByIdTableau(?int $idTableau): \App\Trellotrolle\Modele\DataObject\Tableau;

    /**
     * @throws ServiceException
     * @throws Exception
     */
    public function creerTableau(?string $loginUtilisateurConnecte, ?string $nomTableau): void;

    /**
     * @throws ServiceException
     */
    public function metterAJourTableau(?int $idTableau, ?string $loginUtilisateurConnecte, ?string $nomtableau): void;

    /**
     * @throws ServiceException
     */
    public function ajouterMembre(?int $idTableau, ?string $loginUtilisateurConnecte, ?string $loginUtilisateurNouveau);

    /**
     * @throws ServiceException
     */
    public function supprimerMembre(?int $idTableau, ?string $loginUtilisateurConnecte, ?string $loginUtilisateurDelete);

    /**
     * @throws ServiceException
     */
    public function supprimer(?string $loginUtilisateurConnecte, ?int $idTableau): void;
}