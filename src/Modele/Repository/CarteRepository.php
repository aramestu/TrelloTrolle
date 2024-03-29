<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use Exception;
use PDOException;

class CarteRepository extends AbstractRepository implements CarteRepositoryInterface
{

    public function __construct(private UtilisateurRepositoryInterface $utilisateurRepository){

    }

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

        $affectations = $this->recupererAffectationsCartes($objetFormatTableau["idcarte"]);
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

    public function recupererAffectationsCartes(string $idCarte): array {
        $utilisateurRepository = $this->utilisateurRepository;
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

    /**
     * @throws PDOException
     */
    public function ajouterAffectation($login, $idCarte):bool
    {
        $sql = "INSERT INTO Affecter(login, idcarte) VALUES (:loginTag, :idCarteTag)";
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($sql);
        $values = [
            "loginTag" => $login,
            "idCarteTag" => $idCarte
        ];
        try {
            $pdoStatement->execute($values);
            return true;
        } catch (PDOException $exception) {
            if ($pdoStatement->errorCode() === "23000") {
                return false;
            } else {
                throw $exception;
            }
        }
    }

    public function supprimerAffectation($login, $idCarte):bool
    {
        $sql = "DELETE FROM Affecter 
                WHERE login = :loginTag
                AND idcarte = :idCarteTag";
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($sql);
        $values = [
            "loginTag" => $login,
            "idCarteTag" => $idCarte
        ];
        $pdoStatement->execute($values);
        $deleteCount = $pdoStatement->rowCount();

        return ($deleteCount > 0);
    }

    public function supprimer(string $valeurClePrimaire): bool
    {
        $this->supprimerToutesAffectations("idCarte", $valeurClePrimaire);
        return parent::supprimer($valeurClePrimaire);
    }

}