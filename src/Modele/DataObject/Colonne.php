<?php

namespace App\Trellotrolle\Modele\DataObject;

use JsonSerializable;

/**
 * Classe Colonne
 *
 * Cette classe représente une colonne dans un tableau.
 * Elle hérite de la classe AbstractDataObject et implémente l'interface JsonSerializable.
 * Elle contient les propriétés idColonne, titreColonne et tableau.
 * Les méthodes create() et construireDepuisTableau() permettent de créer une instance de Colonne à partir d'un tableau.
 * Les méthodes getTableau(), setTableau(), getIdColonne(), setIdColonne(), getTitreColonne() et setTitreColonne() permettent de manipuler les propriétés de la colonne.
 * La méthode __hashCode() retourne le hash de l'objet.
 * Les méthodes formatTableau() et jsonSerialize() retournent un tableau représentant la colonne au format souhaité.
 *
 * @package App\Trellotrolle\Modele\DataObject
 */
class Colonne extends AbstractDataObject implements JsonSerializable
{

    /**
     * Classe représentant une colonne avec un identifiant et un titre, associée à un tableau.
     *
     * @property int $idColonne L'identifiant de la colonne.
     * @property string $titreColonne Le titre de la colonne.
     * @property Tableau $tableau Le tableau auquel la colonne est associée.
     */
    private int $idColonne;
    private string $titreColonne;
    private Tableau $tableau;

    public function __construct()
    {
    }

    /**
     * Méthode create
     *
     * Cette méthode statique crée une instance de la classe Colonne.
     * Elle prend en paramètres un entier $idColonne, une chaîne de caractères $titreColonne et un objet de la classe Tableau $tableau.
     * Elle crée une nouvelle instance de la classe Colonne, lui assigne les valeurs des paramètres et retourne l'instance créée.
     *
     * @param int $idColonne L'identifiant de la colonne.
     * @param string $titreColonne Le titre de la colonne.
     * @param Tableau $tableau Le tableau auquel la colonne est associée.
     * @return Colonne L'instance de la classe Colonne créée.
     */
    public static function create(int $idColonne, string $titreColonne, Tableau $tableau): Colonne
    {
        $colonne = new Colonne();
        $colonne->idColonne = $idColonne;
        $colonne->titreColonne = $titreColonne;
        $colonne->tableau = $tableau;
        return $colonne;
    }

    /**
     * Méthode construireDepuisTableau
     *
     * Cette méthode statique construit une instance de la classe Colonne à partir d'un tableau.
     * Elle prend en paramètre un tableau $objetFormatTableau contenant les informations nécessaires pour créer la colonne.
     * Elle utilise la méthode statique create() pour créer une nouvelle instance de la classe Colonne avec les valeurs du tableau.
     * Elle retourne l'instance créée.
     *
     * @param array $objetFormatTableau Le tableau contenant les informations pour créer la colonne.
     * @return Colonne L'instance de la classe Colonne créée.
     */
    public static function construireDepuisTableau(array $objetFormatTableau): Colonne
    {

        return self::create(
            $objetFormatTableau["idcolonne"],
            $objetFormatTableau["titrecolonne"],
            $objetFormatTableau['tableau']
        );
    }

    /**
     * Méthode getTableau
     *
     * Cette méthode retourne l'objet de la classe Tableau associé à l'instance courante.
     *
     * @return Tableau L'objet de la classe Tableau associé à l'instance courante.
     */
    public function getTableau(): Tableau
    {
        return $this->tableau;
    }

    /**
     * Méthode setTableau
     *
     * Cette méthode permet de définir l'objet de la classe Tableau associé à l'instance courante.
     *
     * @param Tableau $tableau L'objet de la classe Tableau à associer à l'instance courante.
     * @return void
     */
    public function setTableau(Tableau $tableau): void
    {
        $this->tableau = $tableau;
    }

    /**
     * Méthode getIdColonne
     *
     * Cette méthode retourne l'identifiant de la colonne.
     *
     * @return int|null L'identifiant de la colonne.
     */
    public function getIdColonne(): ?int
    {
        return $this->idColonne;
    }

    /**
     * Méthode setIdColonne
     *
     * Cette méthode permet de définir l'identifiant de la colonne.
     *
     * @param int|null $idColonne L'identifiant de la colonne.
     * @return void
     */
    public function setIdColonne(?int $idColonne): void
    {
        $this->idColonne = $idColonne;
    }

    /**
     * Méthode getTitreColonne
     *
     * Cette méthode retourne le titre de la colonne.
     *
     * @return string|null Le titre de la colonne.
     */
    public function getTitreColonne(): ?string
    {
        return $this->titreColonne;
    }

    /**
     * Méthode setTitreColonne
     *
     * Cette méthode permet de définir le titre de la colonne.
     *
     * @param string|null $titreColonne Le titre de la colonne.
     * @return void
     */
    public function setTitreColonne(?string $titreColonne): void
    {
        $this->titreColonne = $titreColonne;
    }

    /**
     * Méthode __hashCode
     *
     * Cette méthode retourne le hash de l'objet courant.
     *
     * @return string Le hash de l'objet courant.
     */
    public function __hashCode(): string
    {
        return spl_object_hash($this);
    }


    /**
     * Méthode formatTableau
     *
     * Cette méthode retourne un tableau formaté contenant les informations de la colonne.
     * Le tableau retourné contient les clés "idColonneTag", "titreColonneTag" et "idTableauTag".
     * La clé "idColonneTag" contient l'identifiant de la colonne, ou null si l'identifiant n'est pas défini.
     * La clé "titreColonneTag" contient le titre de la colonne, ou null si le titre n'est pas défini.
     * La clé "idTableauTag" contient l'identifiant du tableau associé à la colonne, ou null si le tableau n'est pas défini.
     *
     * @return array Le tableau formaté contenant les informations de la colonne.
     */
    public function formatTableau(): array
    {
        return array(
            "idColonneTag" => $this->idColonne ?? null,
            "titreColonneTag" => $this->titreColonne ?? null,
            "idTableauTag" => $this->tableau->getIdTableau() ?? null
        );
    }

    /**
     * Méthode jsonSerialize
     *
     * Cette méthode retourne un tableau associatif contenant les propriétés de l'objet courant au format JSON.
     * Le tableau retourné contient les clés "idColonne", "titreColonne" et "idTableau".
     * La clé "idColonne" contient l'identifiant de la colonne, ou null si l'identifiant n'est pas défini.
     * La clé "titreColonne" contient le titre de la colonne, ou null si le titre n'est pas défini.
     * La clé "idTableau" contient l'identifiant du tableau associé à la colonne, ou null si le tableau n'est pas défini.
     *
     * @return array Le tableau associatif contenant les propriétés de l'objet courant au format JSON.
     */
    public function jsonSerialize(): array
    {
        return [
            "idColonne" => $this->idColonne ?? null,
            "titreColonne" => $this->titreColonne ?? null,
            "idTableau" => (isset($this->tableau)) ? $this->tableau->getIdTableau() : null
        ];
    }
}