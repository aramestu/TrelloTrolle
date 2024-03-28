<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use Exception;

class UtilisateurRepository extends AbstractRepository
{

    protected function getNomTable(): string
    {
        return "Utilisateurs";
    }

    protected function getNomCle(): string
    {
        return "login";
    }

    protected function getNomsColonnes(): array
    {
        return ["login", "nomUtilisateur", "prenomUtilisateur", "emailUtilisateur", "mdpHache"];
    }

    protected function estAutoIncremente():bool
    {
        return false;
    }

    protected function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject
    {
        return Utilisateur::construireDepuisTableau($objetFormatTableau);
    }

    public function recupererUtilisateursParEmail(string $email): array {
        return $this->recupererPlusieursPar("emailUtilisateur", $email);
    }

    public function recupererUtilisateursOrderedPrenomNom() : array {
        return $this->recupererOrdonne(["prenomUtilisateur", "nomUtilisateur"]);
    }
}