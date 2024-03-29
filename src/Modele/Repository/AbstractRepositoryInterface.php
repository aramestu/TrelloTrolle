<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;

interface AbstractRepositoryInterface
{
    /**
     * @return AbstractDataObject[]
     */
    public function recuperer(): array;

    public function recupererParClePrimaire(string $valeurClePrimaire): ?AbstractDataObject;

    public function supprimer(string $valeurClePrimaire): bool;

    public function mettreAJour(AbstractDataObject $object): void;

    public function ajouter(AbstractDataObject $object): bool;
}