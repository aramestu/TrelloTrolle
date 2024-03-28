<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use Exception;

class TableauRepository extends AbstractRepository implements TableauRepositoryInterface
{
    protected function getNomTable(): string
    {
        return "Tableaux";
    }

    protected function getNomCle(): string
    {
        return "idTableau";
    }

    protected function getNomsColonnes(): array
    {
        return ["idTableau", "codeTableau", "titreTableau", "proprietaireTableau"];
    }

    protected function estAutoIncremente():bool
    {
        return true;
    }

    protected function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject
    {
        $objetFormatTableau["participants"] = $this->recupererParticipantsTableau($objetFormatTableau["idtableau"]);
        $objetFormatTableau["proprietairetableau"] = (new UtilisateurRepository())->recupererParClePrimaire($objetFormatTableau["proprietairetableau"]);
        return Tableau::construireDepuisTableau($objetFormatTableau);
    }

    public function recupererTableauxUtilisateur(string $login): array {
        return $this->recupererPlusieursPar("login", $login);
    }

    public function recupererParCodeTableau(string $codeTableau): ?AbstractDataObject {
        return $this->recupererPar("codetableau", $codeTableau);
    }

    /**
     * @return Tableau[]
     */
    public function recupererTableauxParticipeUtilisateur(string $login): array
    {
        $sql = "SELECT DISTINCT (idTableau)
                from Participer
                WHERE login= :login";
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($sql);
        $values = array(
            "login" => $login
        );
        $pdoStatement->execute($values);
        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->construireDepuisTableau($objetFormatTableau);
        }
        return $objets;
    }

    /**
     * @return Utilisateur[]
     */
    public function recupererParticipantsTableau(string $idTableau): array
    {
        $utilisateurRepository = new UtilisateurRepository();
        $nomColonne = $utilisateurRepository->formatNomsColonnes();
        $sql = "SELECT p.$nomColonne
                FROM Participer p
                JOIN Utilisateurs u ON p.login = u.login
                WHERE idTableau= :idTableauTag";
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($sql);
        $values = array(
            "idTableauTag" => $idTableau
        );
        $pdoStatement->execute($values);
        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $utilisateurRepository->construireDepuisTableau($objetFormatTableau);
        }
        $pdoStatement->execute($values);

        return $objets;
    }

    public function getNombreTableauxTotalUtilisateur(string $login) : int {
        $query = "SELECT COUNT(DISTINCT idTableau) FROM Participer WHERE login=:login";
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($query);
        $pdoStatement->execute(["login" => $login]);
        $obj = $pdoStatement->fetch();
        return $obj[0];
    }
}