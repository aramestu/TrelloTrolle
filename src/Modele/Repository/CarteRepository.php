<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use Exception;
use PDOException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CarteRepository extends AbstractRepository implements CarteRepositoryInterface
{

    public function __construct(private ContainerInterface $container, private ConnexionBaseDeDonneesInterface $connexionBaseDeDonnees){
        parent::__construct($connexionBaseDeDonnees);
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

    public function lastInsertId(): false|string
    {
        return $this->connexionBaseDeDonnees->getPdo()->lastInsertId();
    }

    public function recupererCartesColonne(int $idcolonne): array {
        return $this->recupererPlusieursPar("idColonne", $idcolonne);
    }


    public function recupererCartesTableau(int $idTableau): array {
        $nomColonnes = $this->formatNomsColonnes();
        $sql = "SELECT $nomColonnes FROM Cartes c1
                WHERE EXISTS (SELECT * FROM Colonnes c2
                              WHERE idtableau = :idTableauTag
                              AND c1.idcolonne = c2.idcolonne)";
        $values = [
            "idTableauTag" => $idTableau
        ];
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute($values);
        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->construireDepuisTableau($objetFormatTableau);
        }
        return $objets;
    }


    //Cette fonction ne va surement pas fonctionner avec la nouvelle BD
    /**
     * @return Carte[]
     */
    public function recupererCartesUtilisateur(string $login): array
    {
        $sql = "SELECT {$this->formatNomsColonnes()} from Cartes c 
                WHERE EXISTS(SELECT * FROM Affecter a
                             WHERE a.idcarte = c.idcarte
                             AND a.login = :loginTag)";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $values = array(
            "loginTag" => $login
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
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
        $pdoStatement->execute(["login" => $login]);
        $obj = $pdoStatement->fetch();
        return $obj[0];
    }

    public function recupererAffectationsCartes(string $idCarte): array {
        $utilisateurRepository = $this->container->get("utilisateur_repository");
        $nomColonnnes = $utilisateurRepository->formatNomsColonnes();

        $sql = "SELECT a.$nomColonnnes FROM Affecter a
             JOIN Utilisateurs u ON u.login = a.login
             WHERE idCarte=:idCarteTag";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
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
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
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
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $values = [
            "loginTag" => $login,
            "idCarteTag" => $idCarte
        ];
        $pdoStatement->execute($values);
        $deleteCount = $pdoStatement->rowCount();

        return ($deleteCount > 0);
    }

    public function supprimerToutesAffectationsCarte($idCarte): bool
    {
        return $this->supprimerToutesAffectations("idCarte", $idCarte);
    }

    public function supprimer(string $valeurClePrimaire): bool
    {
        $this->supprimerToutesAffectations("idCarte", $valeurClePrimaire);
        return parent::supprimer($valeurClePrimaire);
    }

}