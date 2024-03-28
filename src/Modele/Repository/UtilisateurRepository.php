<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use Exception;

class UtilisateurRepository extends AbstractRepository implements UtilisateurRepositoryInterface
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

    public function recupererTableauxOuUtilisateurEstMembre(string $login): array
    {
        $tableauRepository =  new TableauRepository();
        $nomColonnes = $tableauRepository->formatNomsColonnes();
        $sql = "SELECT DISTINCT  t.$nomColonnes FROM Tableaux t
                LEFT JOIN participer p ON t.idTableau = p.idtableau
                WHERE p.login = :loginTag
                OR t.proprietairetableau = :loginTag
                ";
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($sql);
        $values = ["loginTag" => $login];
        $pdoStatement->execute($values);
        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $tableauRepository->construireDepuisTableau($objetFormatTableau);
        }

        return $objets;
    }
}