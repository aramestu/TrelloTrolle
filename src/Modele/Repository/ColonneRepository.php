<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Colonne;
use Exception;

class ColonneRepository extends AbstractRepository implements ColonneRepositoryInterface
{

    protected function getNomTable(): string
    {
        return "app_db";
    }

    protected function getNomCle(): string
    {
        return "idcolonne";
    }

    protected function getNomsColonnes(): array
    {
        return [
            "login", "nom", "prenom", "email", "mdphache",
            "mdp", "idtableau", "codetableau", "titretableau",
            "participants", "idcolonne", "titrecolonne"
        ];
    }

    protected function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject
    {
        return Colonne::construireDepuisTableau($objetFormatTableau);
    }

    public function recupererColonnesTableau(int $idTableau): array {
        return $this->recupererPlusieursParOrdonne("idtableau", $idTableau, ["idcolonne"]);
    }

    public function getNextIdColonne() : int {
        return $this->getNextId("idcolonne");
    }

    public function getNombreColonnesTotalTableau(int $idTableau) : int {
        $query = "SELECT COUNT(DISTINCT idcolonne) FROM app_db WHERE idtableau=:idTableau";
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($query);
        $pdoStatement->execute(["idTableau" => $idTableau]);
        $obj = $pdoStatement->fetch();
        return $obj[0];
    }

    /**
     * @throws Exception
     */
    public function ajouter(AbstractDataObject $object): bool
    {
        throw new Exception("Impossible d'ajouter seulement une colonne...");
    }


}