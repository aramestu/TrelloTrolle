<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use PDOException;

/**
 * Classe abstraite représentant un Repository générique.
 *
 * Cette classe abstraite implémente l'interface AbstractRepositoryInterface et fournit des méthodes de base pour interagir avec une base de données.
 * Les méthodes abstraites doivent être implémentées par les sous-classes.
 *
 * @package App\Trellotrolle\Modele\Repository
 */
abstract class AbstractRepository implements AbstractRepositoryInterface
{

    public function __construct(private ConnexionBaseDeDonneesInterface $connexionBaseDeDonnees)
    {
    }

    /**
     * Méthode abstraite qui retourne le nom de la table.
     *
     * @return string Le nom de la table.
     */
    protected abstract function getNomTable(): string;

    /**
     * Méthode abstraite qui retourne le nom de la clé primaire.
     *
     * @return string Le nom de la clé primaire.
     */
    protected abstract function getNomCle(): string;

    /**
     * Méthode abstraite qui retourne les noms des colonnes.
     *
     * @return array Les noms des colonnes.
     */
    protected abstract function getNomsColonnes(): array;

    /**
     * Méthode abstraite construireDepuisTableau()
     *
     * Cette méthode construit un objet de données à partir d'un tableau.
     *
     * @param array $objetFormatTableau Le tableau représentant l'objet de données
     * @return AbstractDataObject L'objet de données construit
     */
    protected abstract function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject;

    /**
     * Méthode abstraite pour vérifier si l'attribut est auto-incrémenté.
     *
     * @return bool
     */
    protected abstract function estAutoIncremente(): bool;


    /**
     * Méthode protégée qui formate les noms des colonnes en une chaîne de caractères séparée par des virgules.
     *
     * @return string Les noms des colonnes formatés.
     */
    protected function formatNomsColonnes(): string
    {
        return join(",", $this->getNomsColonnes());
    }

    /**
     * Récupère un tableau d'objets de données.
     *
     * @return AbstractDataObject[] Le tableau d'objets de données récupérés.
     */
    public function recuperer(): array
    {
        $nomTable = $this->getNomTable();

        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->query("SELECT * FROM $nomTable");

        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->construireDepuisTableau($objetFormatTableau);
        }

        return $objets;
    }


    /**
     * Récupère un tableau d'objets de données ordonnés selon les attributs spécifiés.
     *
     * @param array $attributs Les attributs utilisés pour l'ordonnancement
     * @param string $sens Le sens de l'ordonnancement (par défaut: "ASC")
     * @return AbstractDataObject[] Le tableau d'objets de données récupérés et ordonnés
     */
    protected function recupererOrdonne(array $attributs, string $sens = "ASC"): array
    {
        $nomTable = $this->getNomTable();
        $attributsTexte = join(",", $attributs);

        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare("SELECT {$this->formatNomsColonnes()} FROM $nomTable ORDER BY $attributsTexte $sens");
        $pdoStatement->execute();

        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->construireDepuisTableau($objetFormatTableau);
        }

        return $objets;
    }

    /**
     * Récupère plusieurs objets de données en fonction d'un attribut spécifié.
     *
     * @param string $nomAttribut Le nom de l'attribut utilisé pour la récupération
     * @param mixed $valeur La valeur de l'attribut utilisé pour la récupération
     * @return array Le tableau d'objets de données récupérés
     */
    protected function recupererPlusieursPar(string $nomAttribut, mixed $valeur): array
    {
        $nomTable = $this->getNomTable();
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare("SELECT {$this->formatNomsColonnes()} FROM $nomTable WHERE $nomAttribut = :valeurTag");
        $values = [
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
     * Méthode protégée qui récupère plusieurs objets de données ordonnés selon un attribut spécifié.
     *
     * @param string $nomAttribut Le nom de l'attribut utilisé pour la récupération
     * @param mixed $valeur La valeur de l'attribut utilisé pour la récupération
     * @param array $attributs Les attributs utilisés pour l'ordonnancement
     * @param string $sens Le sens de l'ordonnancement (par défaut: "ASC")
     * @return AbstractDataObject[] Le tableau d'objets de données récupérés et ordonnés
     */
    protected function recupererPlusieursParOrdonne(string $nomAttribut, mixed $valeur, array $attributs, string $sens = "ASC"): array
    {
        $nomTable = $this->getNomTable();
        $attributsTexte = join(",", $attributs);
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare("SELECT {$this->formatNomsColonnes()} FROM $nomTable WHERE $nomAttribut = :valeurTag ORDER BY $attributsTexte $sens ");
        $values = array(
            "valeurTag" => $valeur
        );
        $pdoStatement->execute($values);
        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->construireDepuisTableau($objetFormatTableau);
        }

        return $objets;
    }

    /**
     * Méthode protégée pour récupérer un objet par un attribut spécifique.
     *
     * @param string $nomAttribut Le nom de l'attribut utilisé pour la recherche.
     * @param mixed $valeur La valeur de l'attribut utilisée pour la recherche.
     * @return AbstractDataObject|null L'objet récupéré ou null s'il n'existe pas.
     */
    protected function recupererPar(string $nomAttribut, mixed $valeur): ?AbstractDataObject
    {
        $nomTable = $this->getNomTable();
        $sql = "SELECT DISTINCT {$this->formatNomsColonnes()} from $nomTable WHERE $nomAttribut= :valeurTag";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $values = [
            "valeurTag" => $valeur
        ];
        $pdoStatement->execute($values);
        $objetFormatTableau = $pdoStatement->fetch();

        if ($objetFormatTableau !== false) {
            return $this->construireDepuisTableau($objetFormatTableau);
        }
        return null;
    }

    /**
     * Méthode permettant de récupérer un objet par sa clé primaire.
     *
     * @param string $valeurClePrimaire La valeur de la clé primaire.
     * @return AbstractDataObject|null L'objet correspondant à la clé primaire, ou null si aucun objet n'est trouvé.
     */
    public function recupererParClePrimaire(string $valeurClePrimaire): ?AbstractDataObject
    {
        return $this->recupererPar($this->getNomCle(), $valeurClePrimaire);
    }

    /**
     * Supprime une entrée de la table spécifiée en utilisant la valeur de la clé primaire.
     *
     * @param string $valeurClePrimaire La valeur de la clé primaire de l'entrée à supprimer.
     * @return bool Retourne true si l'entrée a été supprimée avec succès, sinon false.
     */
    public function supprimer(string $valeurClePrimaire): bool
    {
        $nomTable = $this->getNomTable();
        $nomClePrimaire = $this->getNomCle();
        $sql = "DELETE FROM $nomTable WHERE $nomClePrimaire= :valeurClePrimaireTag";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $values = [
            "valeurClePrimaireTag" => $valeurClePrimaire
        ];
        $pdoStatement->execute($values);
        $deleteCount = $pdoStatement->rowCount();

        return ($deleteCount > 0);
    }

    /**
     * Met à jour un objet dans la base de données.
     *
     * @param AbstractDataObject $object L'objet à mettre à jour.
     * @return void
     */
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
        $req_prep = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);

        $objetFormatTableau = $object->formatTableau();
        $req_prep->execute($objetFormatTableau);

    }

    /**
     * Méthode pour ajouter un objet dans la base de données.
     *
     * @param AbstractDataObject $object L'objet à ajouter.
     * @return bool Retourne true si l'ajout a réussi, false sinon.
     * @throws PDOException Si une erreur PDO se produit.
     */
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

        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);

        $objetFormatTableau = $object->formatTableau();
        if ($this->estAutoIncremente()) unset($objetFormatTableau[$this->getNomCle() . "Tag"]);
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

    /**
     * Supprime toutes les affectations dans la table "Affecter" où la colonne spécifiée a la valeur spécifiée.
     *
     * @param string $nomColonne Le nom de la colonne à vérifier.
     * @param string $valeurColonne La valeur de la colonne à vérifier.
     * @return bool Retourne true si des affectations ont été supprimées, sinon false.
     */
    public function supprimerToutesAffectations(string $nomColonne, string $valeurColonne): bool
    {
        $sql = "DELETE FROM Affecter 
                WHERE $nomColonne = :valeurTag";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $values = [
            "valeurTag" => $valeurColonne
        ];
        $pdoStatement->execute($values);
        $deleteCount = $pdoStatement->rowCount();

        return ($deleteCount > 0);
    }

    /**
     * Supprime toutes les participations dans la table "Participer" où la colonne spécifiée a la valeur spécifiée.
     *
     * @param string $nomColonne Le nom de la colonne à vérifier.
     * @param string $valeurColonne La valeur de la colonne à vérifier.
     * @return bool Retourne true si des participations ont été supprimées, sinon false.
     */
    public function supprimerToutesParticipation(string $nomColonne, string $valeurColonne): bool
    {
        $sql = "DELETE FROM Participer 
                WHERE $nomColonne = :valeurTag";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $values = [
            "valeurTag" => $valeurColonne
        ];
        $pdoStatement->execute($values);
        $deleteCount = $pdoStatement->rowCount();

        return ($deleteCount > 0);
    }
}