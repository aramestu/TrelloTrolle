<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Tableau;
use Exception;

interface TableauRepositoryInterface extends AbstractRepositoryInterface
{
    public function recupererTableauxUtilisateur(string $login): array;

    public function recupererParCodeTableau(string $codeTableau): ?AbstractDataObject;

    /**
     * @throws Exception
     */
    public function ajouter(AbstractDataObject $object): bool;

    /**
     * @return Tableau[]
     */
    public function recupererTableauxOuUtilisateurEstMembre(string $login): array;

    /**
     * @return Tableau[]
     */
    public function recupererTableauxParticipeUtilisateur(string $login): array;

    public function getNextIdTableau(): int;

    public function getNombreTableauxTotalUtilisateur(string $login): int;
}