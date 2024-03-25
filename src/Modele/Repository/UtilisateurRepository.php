<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use Exception;

class UtilisateurRepository extends AbstractRepository implements UtilisateurRepositoryInterface
{

    protected function getNomTable(): string
    {
        return "app_db";
    }

    protected function getNomCle(): string
    {
        return "login";
    }

    protected function getNomsColonnes(): array
    {
        return ["login", "nom", "prenom", "email", "mdphache", "mdp"];
    }

    protected function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject
    {
        return Utilisateur::construireDepuisTableau($objetFormatTableau);
    }

    /**
     * @throws Exception
     */
    public function ajouter(AbstractDataObject $object): bool
    {
        throw new Exception("Impossible d'ajouter seulement un utilisateur...");
    }

    public function recupererUtilisateursParEmail(string $email): array {
        return $this->recupererPlusieursPar("email", $email);
    }

    public function recupererUtilisateursOrderedPrenomNom() : array {
        return $this->recupererOrdonne(["prenom", "nom"]);
    }
}