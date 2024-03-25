<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Carte;
use Exception;

class CarteRepository extends AbstractRepository
{

    protected function getNomTable(): string
    {
        return "Cartes";
    }

    protected function getNomCle(): string
    {
        return "idCarte";
    }

    protected function getNomsColonnes(): array
    {
        return [
            "idCarte", "titreCarte", "descriptifCarte", "couleurCarte", "idColonne"
        ];
    }

    protected function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject
    {
        return Carte::construireDepuisTableau($objetFormatTableau);
    }

    public function recupererCartesColonne(int $idcolonne): array {
        return $this->recupererPlusieursPar("idColonne", $idcolonne);
    }


    //Cette fonction ne va surement pas fonctionner avec la nouvelle BD
    public function recupererCartesTableau(int $idTableau): array {
        return $this->recupererPlusieursPar("idtableau", $idTableau);
    }


    //Cette fonction ne va surement pas fonctionner avec la nouvelle BD
    /**
     * @return Carte[]
     */
    public function recupererCartesUtilisateur(string $login): array
    {
        $sql = "SELECT {$this->formatNomsColonnes()} from app_db WHERE affectationscarte @> :json";
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($sql);
        $values = array(
            "json" => json_encode(["utilisateurs" => [["login" => $login]]])
        );
        $pdoStatement->execute($values);
        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->construireDepuisTableau($objetFormatTableau);
        }
        return $objets;
    }

    public function getNombreCartesTotalUtilisateur(string $login) : int {
        $query = "SELECT COUNT(*) FROM Affecter WHERE login=:login";
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($query);
        $pdoStatement->execute(["login" => $login]);
        $obj = $pdoStatement->fetch();
        return $obj[0];
    }

    public static function recupererAffectationsCartes(string $idCarte): array {
        $sql = "SELECT login FROM Affecter WHERE idCarte=:idCarte";
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($sql);
        $pdoStatement->execute(["idCarte" => $idCarte]);
        return $pdoStatement->fetch();
    }

    public function getNextIdCarte() : int {
        return $this->getNextId("idCarte");
    }
}