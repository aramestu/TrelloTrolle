<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Tableau;
use PDOException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Classe TableauRepository
 *
 * Cette classe est une implémentation de l'interface TableauRepositoryInterface.
 * Elle gère les opérations de base de données liées aux tableaux dans l'application Trellotrolle.
 *
 * @package App\Trellotrolle\Modele\Repository
 */
class TableauRepository extends AbstractRepository implements TableauRepositoryInterface
{

    /**
     * Constructeur de la classe.
     *
     * @param ContainerInterface $container L'instance du conteneur d'injection de dépendances.
     * @param ConnexionBaseDeDonneesInterface $connexionBaseDeDonnees L'instance de la connexion à la base de données.
     */
    public function __construct(private ContainerInterface $container, private ConnexionBaseDeDonneesInterface $connexionBaseDeDonnees)
    {
        parent::__construct($connexionBaseDeDonnees);
    }

    /**
     * Retourne le nom de la table "Tableaux".
     *
     * @return string Le nom de la table.
     */
    protected function getNomTable(): string
    {
        return "Tableaux";
    }

    /**
     * Retourne le nom de la clé primaire de la table "Tableaux".
     *
     * @return string Le nom de la clé primaire.
     */
    protected function getNomCle(): string
    {
        return "idTableau";
    }

    /**
     * Retourne les noms des colonnes de la table.
     *
     * @return array Les noms des colonnes.
     */
    protected function getNomsColonnes(): array
    {
        return ["idTableau", "codeTableau", "titreTableau", "proprietaireTableau"];
    }

    /**
     * Indique si la méthode estAutoIncremente() retourne une valeur booléenne.
     *
     * @return bool La valeur de retour de la méthode estAutoIncremente().
     */
    protected function estAutoIncremente(): bool
    {
        return true;
    }

    /**
     * Méthode protégée pour construire un objet AbstractDataObject à partir d'un tableau de données.
     *
     * @param array $objetFormatTableau Le tableau de données à utiliser pour construire l'objet.
     * @return AbstractDataObject L'objet construit à partir du tableau de données.
     */
    protected function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject
    {
        $objetFormatTableau["participants"] = $this->recupererParticipantsTableau($objetFormatTableau["idtableau"]);
        $utilisateurRepository = $this->container->get("utilisateur_repository");
        $objetFormatTableau["proprietairetableau"] = $utilisateurRepository->recupererParClePrimaire($objetFormatTableau["proprietairetableau"]);
        return Tableau::construireDepuisTableau($objetFormatTableau);
    }

    /**
     * Récupère les tableaux d'un utilisateur donné.
     *
     * @param string $login Le login de l'utilisateur.
     * @return array Les tableaux de l'utilisateur.
     */
    public function recupererTableauxUtilisateur(string $login): array
    {
        return $this->recupererPlusieursPar("login", $login);
    }

    /**
     * Récupère un objet de type AbstractDataObject en fonction du code du tableau.
     *
     * @param string $codeTableau Le code du tableau
     * @return AbstractDataObject|null L'objet récupéré ou null s'il n'existe pas
     */
    public function recupererParCodeTableau(string $codeTableau): ?AbstractDataObject
    {
        return $this->recupererPar("codetableau", $codeTableau);
    }

    /**
     * Récupère les tableaux auxquels un utilisateur participe.
     *
     * @param string $login Le login de l'utilisateur
     * @return array Les tableaux auxquels l'utilisateur participe
     */
    public function recupererTableauxParticipeUtilisateur(string $login): array
    {
        $nomColonnes = $this->formatNomsColonnes();
        $sql = "SELECT DISTINCT t.$nomColonnes
                FROM Tableaux t
                LEFT JOIN Participer p ON t.idtableau = p.idtableau
                WHERE login= :login
                OR proprietairetableau = :login";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
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
     * Récupère les participants d'un tableau.
     *
     * @param string $idTableau L'identifiant du tableau.
     * @return array Les participants du tableau.
     */
    public function recupererParticipantsTableau(string $idTableau): array
    {
        $utilisateurRepository = $this->container->get("utilisateur_repository");
        $nomColonne = $utilisateurRepository->formatNomsColonnes();
        $sql = "SELECT p.$nomColonne
                FROM Participer p
                JOIN Utilisateurs u ON p.login = u.login
                WHERE idTableau= :idTableauTag";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
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

    /**
     * Méthode getNombreTableauxTotalUtilisateur
     *
     * Cette méthode retourne le nombre total de tableaux auxquels un utilisateur participe.
     *
     * @param string $login Le login de l'utilisateur.
     * @return int Le nombre total de tableaux auxquels l'utilisateur participe.
     */
    public function getNombreTableauxTotalUtilisateur(string $login): int
    {
        $query = "SELECT COUNT(DISTINCT idTableau) FROM Participer WHERE login=:login";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
        $pdoStatement->execute(["login" => $login]);
        $obj = $pdoStatement->fetch();
        return $obj[0];
    }

    /**
     * Ajoute un participant à un tableau.
     *
     * @param string $login Le login du participant.
     * @param int $idTableau L'identifiant du tableau.
     * @return bool Retourne true si le participant a été ajouté avec succès, false sinon.
     * @throws PDOException Si une erreur PDO se produit lors de l'exécution de la requête.
     */
    public function ajouterParticipant(string $login, int $idTableau): bool
    {
        $sql = "INSERT INTO Participer(login, idtableau) VALUES (:loginTag, :idTableauTag)";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
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

    /**
     * Supprime un participant d'un tableau.
     *
     * @param string $login Le login du participant.
     * @param int $idTableau L'identifiant du tableau.
     * @return bool Retourne true si le participant a été supprimé avec succès, false sinon.
     */
    public function supprimerParticipant(string $login, int $idTableau): bool
    {
        $sql = "DELETE FROM Participer 
                WHERE login = :loginTag
                AND idtableau = :idTableauTag";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $values = [
            "loginTag" => $login,
            "idTableauTag" => $idTableau
        ];
        $pdoStatement->execute($values);
        $deleteCount = $pdoStatement->rowCount();

        return ($deleteCount > 0);
    }

    /**
     * Supprime une affectation d'un tableau.
     *
     * @param string $login Le login de l'utilisateur.
     * @param int $idTableau L'identifiant du tableau.
     * @return bool Retourne true si l'affectation a été supprimée avec succès, false sinon.
     */
    public function supprimerAffectation(string $login, int $idTableau): bool
    {
        $sql = "DELETE FROM Affecter a 
                WHERE EXISTS (SELECT * FROM Cartes ca
                              JOIN colonnes co WHERE idTableau = :idTableauTag
                              AND a.idcarte = ca.idcarte)
                AND login = :loginTag
                AND idcarte = :idCarteTag";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $values = [
            "loginTag" => $login,
            "idTableauTag" => $idTableau
        ];
        $pdoStatement->execute($values);
        $deleteCount = $pdoStatement->rowCount();

        return ($deleteCount > 0);
    }

    /**
     * Supprime un élément en utilisant la valeur de la clé primaire.
     *
     * @param string $valeurClePrimaire La valeur de la clé primaire.
     * @return bool Retourne true si la suppression a réussi, sinon false.
     */
    public function supprimer(string $valeurClePrimaire): bool
    {
        $this->supprimerToutesParticipation("idTableau", $valeurClePrimaire);
        $colonneRepository = $this->container->get("colonne_repository");
        $colonnes = $colonneRepository->recupererPlusieursParOrdonne("idTableau", $valeurClePrimaire, ["idTableau"]);
        $colonneRepository = $this->container->get("colonne_repository");
        foreach ($colonnes as $colonne) {
            $colonneRepository->supprimer($colonne->getIdColonne());
        }
        return parent::supprimer($valeurClePrimaire);
    }

}