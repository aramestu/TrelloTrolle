<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\Carte;

interface CarteRepositoryInterface extends AbstractRepositoryInterface
{
    public function recupererCartesColonne(int $idcolonne): array;

    public function recupererCartesTableau(int $idTableau): array;

    /**
     * @return Carte[]
     */
    public function recupererCartesUtilisateur(string $login): array;

    public function getNombreCartesTotalUtilisateur(string $login): int;

}