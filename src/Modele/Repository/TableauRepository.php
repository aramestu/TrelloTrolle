<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use Exception;
use PDOException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TableauRepository extends AbstractRepository implements TableauRepositoryInterface
{

    public function __construct(private ContainerInterface $container){

    }

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
        $utilisateurRepository = $this->container->get("utilisateur_repository");
        $objetFormatTableau["proprietairetableau"] = $utilisateurRepository->recupererParClePrimaire($objetFormatTableau["proprietairetableau"]);
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
        $nomColonnes = $this->formatNomsColonnes();
        $sql = "SELECT DISTINCT t.$nomColonnes
                FROM Participer p
                RIGHT JOIN Tableaux t ON t.idtableau = p.idtableau
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
        $utilisateurRepository = $this->container->get("utilisateur_repository");
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

    /**
     * @throws PDOException
     */
    public function ajouterParticipant($login, $idTableau):bool
    {
        $sql = "INSERT INTO Participer(login, idtableau) VALUES (:loginTag, :idTableauTag)";
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($sql);
        $values = [
            "loginTag" => $login,
            "idTableauTag" => $idTableau
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

    public function supprimerParticipant($login, $idTableau):bool
    {
        $sql = "DELETE FROM Participer 
                WHERE login = :loginTag
                AND idtableau = :idTableauTag";
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($sql);
        $values = [
            "loginTag" => $login,
            "idTableauTag" => $idTableau
        ];
        $pdoStatement->execute($values);
        $deleteCount = $pdoStatement->rowCount();

        return ($deleteCount > 0);
    }

    public function supprimer(string $valeurClePrimaire): bool
    {
        $this->supprimerToutesParticipation("idTableau", $valeurClePrimaire);
        $colonnes = $this->recupererPlusieursParOrdonne("idTableau", $valeurClePrimaire, ["idTableau"]);
        $colonneRepository = $this->container->get("colonne_repository");
        foreach ($colonnes as $colonne){
            $colonneRepository->supprimer($colonne->getIdColonne());
        }
        return parent::supprimer($valeurClePrimaire);
    }

}