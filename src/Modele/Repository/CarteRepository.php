<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use PDOException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Classe CarteRepository
 *
 * Cette classe est responsable de la gestion des cartes dans la base de données.
 * Elle implémente l'interface CarteRepositoryInterface.
 *
 * @package App\Trellotrolle\Modele\Repository
 */
class CarteRepository extends AbstractRepository implements CarteRepositoryInterface
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
     * Retourne le nom de la table "Cartes".
     *
     * @return string Le nom de la table "Cartes".
     */
    protected function getNomTable(): string
    {
        return "Cartes";
    }

    /**
     * Retourne le nom de la clé primaire de la table "Cartes".
     *
     * @return string Le nom de la clé primaire de la table "Cartes".
     */
    protected function getNomCle(): string
    {
        return "idCarte";
    }

    /**
     * Retourne les noms des colonnes de la table "Cartes".
     *
     * @return array Les noms des colonnes de la table "Cartes".
     */
    protected function getNomsColonnes(): array
    {
        return [
            "idCarte", "titreCarte", "descriptifCarte", "couleurCarte", "idColonne"
        ];
    }

    /**
     * Méthode estAutoIncremente
     *
     * Cette méthode retourne un booléen indiquant si l'attribut "estAutoIncremente" est vrai ou faux.
     *
     * @return bool La valeur de l'attribut "estAutoIncremente".
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
        $colonne = new Colonne();
        $colonne->setIdColonne($objetFormatTableau["idcolonne"]);
        $objetFormatTableau["colonne"] = $colonne;

        $affectations = $this->recupererAffectationsCartes($objetFormatTableau["idcarte"]);
        $objetFormatTableau["affectationscarte"] = $affectations;

        return Carte::construireDepuisTableau($objetFormatTableau);
    }

    /**
     * Méthode lastInsertId
     *
     * Cette méthode retourne l'identifiant de la dernière ligne insérée dans la base de données.
     *
     * @return false|string L'identifiant de la dernière ligne insérée, ou false si aucune ligne n'a été insérée.
     */
    public function lastInsertId(): false|string
    {
        return $this->connexionBaseDeDonnees->getPdo()->lastInsertId();
    }

    /**
     * Récupère les cartes d'une colonne spécifiée.
     *
     * @param int $idcolonne L'identifiant de la colonne.
     * @return array Les cartes de la colonne.
     */
    public function recupererCartesColonne(int $idcolonne): array
    {
        return $this->recupererPlusieursPar("idColonne", $idcolonne);
    }

    /**
     * Récupère les cartes d'un tableau spécifié.
     *
     * @param int $idTableau L'identifiant du tableau.
     * @return array Les cartes du tableau.
     */
    public function recupererCartesTableau(int $idTableau): array
    {
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


    /**
     * Récupère les cartes d'un utilisateur spécifié.
     *
     * @param string $login Le login de l'utilisateur.
     * @return array Les cartes de l'utilisateur.
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

    /**
     * Retourne le nombre total de cartes pour un utilisateur donné.
     *
     * @param string $login Le login de l'utilisateur.
     * @return int Le nombre total de cartes pour l'utilisateur.
     */
    public function getNombreCartesTotalUtilisateur(string $login): int
    {
        $query = "SELECT COUNT(*) FROM Affecter WHERE login=:login";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
        $pdoStatement->execute(["login" => $login]);
        $obj = $pdoStatement->fetch();
        return $obj[0];
    }

    /**
     * Méthode recupererAffectationsCartes
     *
     * Cette méthode récupère les affectations de cartes pour une carte spécifiée.
     *
     * @param int $idCarte L'identifiant de la carte.
     * @return array Les affectations de cartes pour la carte spécifiée.
     */
    public function recupererAffectationsCartes(int $idCarte): array
    {
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
     * Ajoute une affectation d'une carte à un utilisateur.
     *
     * @param string $login Le login de l'utilisateur.
     * @param int $idCarte L'identifiant de la carte.
     * @return bool Retourne true si l'affectation a été ajoutée avec succès, false sinon.
     * @throws PDOException Si une erreur PDO se produit lors de l'exécution de la requête.
     */
    public function ajouterAffectation(string $login, int $idCarte): bool
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

    /**
     * Supprime une affectation d'une carte à un utilisateur.
     *
     * @param string $login Le login de l'utilisateur.
     * @param string $idCarte L'identifiant de la carte.
     * @return bool Retourne true si l'affectation a été supprimée avec succès, false sinon.
     */
    public function supprimerAffectation(string $login, $idCarte): bool
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

    /**
     * Supprime toutes les affectations d'une carte spécifiée.
     *
     * @param mixed $idCarte L'identifiant de la carte.
     * @return bool Retourne true si les affectations ont été supprimées avec succès, sinon false.
     */
    public function supprimerToutesAffectationsCarte(mixed $idCarte): bool
    {
        return $this->supprimerToutesAffectations("idCarte", $idCarte);
    }

    /**
     * Supprime un élément en utilisant la clé primaire spécifiée.
     *
     * @param string $valeurClePrimaire La valeur de la clé primaire de l'élément à supprimer.
     * @return bool Retourne true si l'élément a été supprimé avec succès, sinon false.
     */
    public function supprimer(string $valeurClePrimaire): bool
    {
        $this->supprimerToutesAffectations("idCarte", $valeurClePrimaire);
        return parent::supprimer($valeurClePrimaire);
    }

}