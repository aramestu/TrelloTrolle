<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Service\Exception\ServiceException;

interface CarteServiceInterface
{
    /**
     * @throws ServiceException
     */
    public function getCarte(?int $idCarte): ?Carte;

    /**
     * @throws ServiceException
     */
    public function supprimerCarte(?int $idCarte, ?string $loginUtilisateurConnecte);

    /**
     * @throws ServiceException
     */
    public function creerCarte(?int $idColonne, ?string $titreCarte, ?string $descriptifCarte, ?string $couleurCarte, ?string $loginUtilisateurConnecte, ?array $affectations);

    /**
     * @throws ServiceException
     */
    public function mettreAJourCarte(?int $idCarte, ?int $idColonne, ?string $titreCarte, ?string $descriptifCarte, ?string $couleurCarte, ?string $loginUtilisateurConnecte, ?array $affectations);
}