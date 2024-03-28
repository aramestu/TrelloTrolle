<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
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

    protected function estAutoIncremente():bool
    {
        return true;
    }

    protected function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject
    {
        $colonne = new Colonne();
        $colonne->setIdColonne($objetFormatTableau["idcolonne"]);
        $objetFormatTableau["colonne"] = $colonne;

        $affectations = self::recupererAffectationsCartes($objetFormatTableau["idcarte"]);
        $objetFormatTableau["affectationscarte"] = $affectations;

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
        $utilisateurRepository = new UtilisateurRepository();
        $nomColonnnes = $utilisateurRepository->formatNomsColonnes();

        $sql = "SELECT a.$nomColonnnes FROM Affecter a
             JOIN Utilisateurs u ON u.login = a.login
             WHERE idCarte=:idCarteTag";
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($sql);
        $pdoStatement->execute(["idCarteTag" => $idCarte]);
        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $utilisateurRepository->construireDepuisTableau($objetFormatTableau);
        }
        return $objets;
    }

    public function getNextIdCarte() : int {
        return $this->getNextId("idCarte");
    }
}