<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use Exception;

interface UtilisateurRepositoryInterface extends AbstractRepositoryInterface
{
    /**
     * @throws Exception
     */
    public function ajouter(AbstractDataObject $object): bool;

    public function recupererUtilisateursParEmail(string $email): array;

    public function recupererUtilisateursOrderedPrenomNom(): array;
}