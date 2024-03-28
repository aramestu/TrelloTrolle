<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use Exception;

interface ColonneRepositoryInterface extends AbstractRepositoryInterface
{
    public function recupererColonnesTableau(int $idTableau): array;

    public function getNextIdColonne(): int;

    public function getNombreColonnesTotalTableau(int $idTableau): int;

    /**
     * @throws Exception
     */
    public function ajouter(AbstractDataObject $object): bool;
}