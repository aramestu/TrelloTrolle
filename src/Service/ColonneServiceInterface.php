<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Service\Exception\ServiceException;
use Exception;

interface ColonneServiceInterface
{
    /**
     * @throws ServiceException
     */
    public function getColonne(?int $idColonne): Colonne;

    /**
     * @throws ServiceException
     */
    public function supprimerColonne(?int $idColonne, ?string $loginUtilisateurConnecte): void;

    /**
     * @throws ServiceException
     * @throws Exception
     */
    public function creerColonne(?int $idTableau, ?string $nomColonne, ?string $loginUtilisateurConnecte): void;

    /**
     * @throws ServiceException
     */
    public function mettreAJour(?int $idColonne, ?string $nomColonne, ?string $loginUtilisateurConnecte): void;

    /**
     * @throws ServiceException
     */
    public function recupererColonnesTableau(int|null $idTableau): array;
}