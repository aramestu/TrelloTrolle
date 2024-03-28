<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use PDOException;

abstract class AbstractRepository
{
    protected abstract function getNomTable(): string;
    protected abstract function getNomCle(): string;
    protected abstract function getNomsColonnes(): array;
    protected abstract function construireDepuisTableau(array $objetFormatTableau) : AbstractDataObject;
    protected abstract function estAutoIncremente(): bool;



    protected function formatNomsColonnes() : string {
        return join(",",$this->getNomsColonnes());
    }

    /**
     * @return AbstractDataObject[]
     */
    public function recuperer(): array
    {
        $nomTable = $this->getNomTable();

        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->query("SELECT * FROM $nomTable");

        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->construireDepuisTableau($objetFormatTableau);
        }

        return $objets;
    }

    /**
     * @return AbstractDataObject[]
     */
    protected function recupererOrdonne($attributs, $sens = "ASC"): array
    {
        $nomTable = $this->getNomTable();
        $attributsTexte = join(",", $attributs);

        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare("SELECT {$this->formatNomsColonnes()} FROM $nomTable ORDER BY :attributsTexteTag :sensTag");
        $values = [
            "attributsTexteTag" => $attributsTexte,
            "sensTag" => $sens
        ];

        $pdoStatement->execute($values);

        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->construireDepuisTableau($objetFormatTableau);
        }

        return $objets;
    }

    /**
     * @return AbstractDataObject[]
     */
    protected function recupererPlusieursPar(string $nomAttribut, $valeur): array
    {
        $nomTable = $this->getNomTable();
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare("SELECT {$this->formatNomsColonnes()} FROM :nomTableTag WHERE :nomAttributTag = :valeurTag");
        $values = [
            "nomTableTag" => $nomTable,
            "nomAttributTag" => $nomAttribut,
            "valeurTag" => $valeur
        ];
        $pdoStatement->execute($values);
        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->construireDepuisTableau($objetFormatTableau);
        }

        return $objets;
    }

    /**
     * @return AbstractDataObject[]
     */
    protected function recupererPlusieursParOrdonne(string $nomAttribut, $valeur, $attributs, $sens = "ASC"): array
    {
        $nomTable = $this->getNomTable();
        $attributsTexte = join(",", $attributs);
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare("SELECT {$this->formatNomsColonnes()} FROM :nomTableTag WHERE :nomAttributTag = :valeurTag ORDER BY :attributsTexteTag :sensTag ");
        $values = array(
            "nomTableTag" => $nomTable,
            "nomAttributTag" => $nomAttribut,
            "valeurTag" => $valeur,
            "attributsTexteTag" => $attributsTexte,
            "sensTag" => $sens
        );
        $pdoStatement->execute($values);
        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->construireDepuisTableau($objetFormatTableau);
        }

        return $objets;
    }

    protected function recupererPar(string $nomAttribut, $valeur): ?AbstractDataObject
    {
        $nomTable = $this->getNomTable();
        $sql = "SELECT DISTINCT {$this->formatNomsColonnes()} from $nomTable WHERE $nomAttribut='$valeur'";
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($sql);
        $pdoStatement->execute();
        $objetFormatTableau = $pdoStatement->fetch();

        if ($objetFormatTableau !== false) {
            return $this->construireDepuisTableau($objetFormatTableau);
        }
        return null;
    }

    public function recupererParClePrimaire(string $valeurClePrimaire): ?AbstractDataObject
    {
        return $this->recupererPar($this->getNomCle(), $valeurClePrimaire);
    }

    public function supprimer(string $valeurClePrimaire): bool
    {
        $nomTable = $this->getNomTable();
        $nomClePrimaire = $this->getNomCle();
        $sql = "DELETE FROM $nomTable WHERE $nomClePrimaire= :valeurClePrimaireTag";
        $values = [
            "valeurClePrimaireTag" => $valeurClePrimaire
        ];
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->query($sql);
        $deleteCount = $pdoStatement->rowCount();

        return ($deleteCount > 0);
    }

    public function mettreAJour(AbstractDataObject $object): void
    {
        $nomTable = $this->getNomTable();
        $nomClePrimaire = $this->getNomCle();
        $nomsColonnes = $this->getNomsColonnes();

        $partiesSet = array_map(function ($nomcolonne) {
            return "$nomcolonne = :{$nomcolonne}Tag";
        }, $nomsColonnes);
        $setString = join(',', $partiesSet);
        $whereString = "$nomClePrimaire = :{$nomClePrimaire}Tag";

        $sql = "UPDATE $nomTable SET $setString WHERE $whereString";
        $req_prep = ConnexionBaseDeDonnees::getPdo()->prepare($sql);

        $objetFormatTableau = $object->formatTableau();
        $req_prep->execute($objetFormatTableau);

    }

    public function ajouter(AbstractDataObject $object): bool
    {
        $nomTable = $this->getNomTable();
        $nomsColonnes = $this->getNomsColonnes();

        if ($this->estAutoIncremente()) unset($nomsColonnes[array_search($this->getNomCle(), $nomsColonnes)]);

        $insertString = '(' . join(', ', $nomsColonnes) . ')';

        $partiesValues = array_map(function ($nomcolonne) {
            return ":{$nomcolonne}Tag";
        }, $nomsColonnes);
        $valueString = '(' . join(', ', $partiesValues) . ')';

        $sql = "INSERT INTO $nomTable $insertString VALUES $valueString";
        echo $sql;
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($sql);

        $objetFormatTableau = $object->formatTableau();
        if ($this->estAutoIncremente()) unset($objetFormatTableau[$this->getNomCle() . "Tag"]);
        var_dump($objetFormatTableau);
        try {
            $pdoStatement->execute($objetFormatTableau);
            return true;
        } catch (PDOException $exception) {
            if ($pdoStatement->errorCode() === "23000") {
                return false;
            } else {
                throw $exception;
            }
        }
    }

    protected function getNextId(string $type) : int {
        $query = ConnexionBaseDeDonnees::getPdo()->query("SELECT MAX($type) FROM app_db");
        $query->execute();
        $obj = $query->fetch();
        return $obj[0] === null ? 0 : $obj[0] + 1;
    }

}
