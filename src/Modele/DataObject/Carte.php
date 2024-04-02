<?php

namespace App\Trellotrolle\Modele\DataObject;

use JsonSerializable;

/**
 * Classe Carte
 *
 * Cette classe représente une carte dans l'application Trellotrolle.
 * Elle hérite de la classe AbstractDataObject et implémente l'interface JsonSerializable.
 *
 * @package App\Trellotrolle\Modele\DataObject
 */
class Carte extends AbstractDataObject implements JsonSerializable
{
    /**
     * Cette partie du code représente les propriétés privées d'une classe.
     * Les propriétés sont les suivantes :
     * - idCarte : un entier représentant l'identifiant de la carte
     * - titreCarte : une chaîne de caractères représentant le titre de la carte
     * - descriptifCarte : une chaîne de caractères représentant le descriptif de la carte
     * - couleurCarte : une chaîne de caractères représentant la couleur de la carte
     * - colonne : un objet de la classe Colonne représentant la colonne à laquelle la carte est associée
     * - affectationsCarte : un tableau représentant les affectations de la carte
     */
    private int $idCarte;
    private string $titreCarte;
    private string $descriptifCarte;
    private string $couleurCarte;
    private Colonne $colonne;
    private array $affectationsCarte;

    public function __construct()
    {
    }

    /**
     * Crée une instance de la classe Carte avec les paramètres spécifiés.
     *
     * @param int $idCarte L'identifiant de la carte
     * @param string $titreCarte Le titre de la carte
     * @param string $descriptifCarte Le descriptif de la carte
     * @param string $couleurCarte La couleur de la carte
     * @param Colonne $colonne La colonne à laquelle la carte est associée
     * @param array $affectationsCarte Les affectations de la carte
     * @return Carte Une instance de la classe Carte
     */
    public static function create(int $idCarte, string $titreCarte, string $descriptifCarte, string $couleurCarte, Colonne $colonne, array $affectationsCarte): Carte
    {
        $carte = new Carte();
        $carte->idCarte = $idCarte;
        $carte->titreCarte = $titreCarte;
        $carte->descriptifCarte = $descriptifCarte;
        $carte->couleurCarte = $couleurCarte;
        $carte->colonne = $colonne;
        $carte->affectationsCarte = $affectationsCarte;
        return $carte;
    }

    /**
     * Méthode statique qui construit une instance de la classe Carte à partir d'un tableau de données.
     *
     * @param array $objetFormatTableau Le tableau de données contenant les informations de la carte
     * @return Carte Une instance de la classe Carte
     */
    public static function construireDepuisTableau(array $objetFormatTableau): Carte
    {
        return Carte::create(
            $objetFormatTableau["idcarte"],
            $objetFormatTableau["titrecarte"],
            $objetFormatTableau["descriptifcarte"],
            $objetFormatTableau["couleurcarte"],
            $objetFormatTableau["colonne"],
            $objetFormatTableau["affectationscarte"]
        );
    }

    /**
     * Renvoie la colonne associée à cet objet.
     *
     * @return Colonne La colonne associée à cet objet.
     */
    public function getColonne(): Colonne
    {
        return $this->colonne;
    }

    /**
     * Définit la colonne associée à cet objet.
     *
     * @param Colonne $colonne La colonne à associer
     * @return void
     */
    public function setColonne(Colonne $colonne): void
    {
        $this->colonne = $colonne;
    }

    /**
     * Renvoie l'identifiant de la carte.
     *
     * @return int|null L'identifiant de la carte.
     */
    public function getIdCarte(): ?int
    {
        return $this->idCarte;
    }

    /**
     * Définit l'identifiant de la carte.
     *
     * @param int|null $idCarte L'identifiant de la carte
     * @return void
     */
    public function setIdCarte(?int $idCarte): void
    {
        $this->idCarte = $idCarte;
    }

    /**
     * Renvoie le titre de la carte.
     *
     * @return string|null Le titre de la carte.
     */
    public function getTitreCarte(): ?string
    {
        return $this->titreCarte;
    }

    /**
     * Définit le titre de la carte.
     *
     * @param string|null $titreCarte Le titre de la carte
     * @return void
     */
    public function setTitreCarte(?string $titreCarte): void
    {
        $this->titreCarte = $titreCarte;
    }

    /**
     * Renvoie le descriptif de la carte.
     *
     * @return string|null Le descriptif de la carte.
     */
    public function getDescriptifCarte(): ?string
    {
        return $this->descriptifCarte;
    }

    /**
     * Définit le descriptif de la carte.
     *
     * @param string|null $descriptifCarte Le descriptif de la carte
     * @return void
     */
    public function setDescriptifCarte(?string $descriptifCarte): void
    {
        $this->descriptifCarte = $descriptifCarte;
    }

    /**
     * Renvoie la couleur de la carte.
     *
     * @return string|null La couleur de la carte.
     */
    public function getCouleurCarte(): ?string
    {
        return $this->couleurCarte;
    }

    /**
     * Définit la couleur de la carte.
     *
     * @param string|null $couleurCarte La couleur de la carte
     * @return void
     */
    public function setCouleurCarte(?string $couleurCarte): void
    {
        $this->couleurCarte = $couleurCarte;
    }

    /**
     * Renvoie les affectations de la carte.
     *
     * @return array|null Les affectations de la carte.
     */
    public function getAffectationsCarte(): ?array
    {
        return array_slice($this->affectationsCarte, 0);
    }

    /**
     * Définit les affectations de la carte.
     *
     * @param array $affectationsCarte Les affectations de la carte
     * @return void
     */
    public function setAffectationsCarte(array $affectationsCarte): void
    {
        $this->affectationsCarte = $affectationsCarte;
    }

    /**
     * Méthode formatTableau
     *
     * Cette méthode retourne un tableau formaté contenant les informations de la carte.
     * Les clés du tableau sont les suivantes :
     * - idCarteTag : l'identifiant de la carte
     * - titreCarteTag : le titre de la carte
     * - descriptifCarteTag : le descriptif de la carte
     * - couleurCarteTag : la couleur de la carte
     * - idColonneTag : l'identifiant de la colonne associée à la carte
     *
     * @return array Le tableau formaté contenant les informations de la carte
     */
    public function formatTableau(): array
    {
        return array(
            "idCarteTag" => $this->idCarte ?? null,
            "titreCarteTag" => $this->titreCarte ?? null,
            "descriptifCarteTag" => $this->descriptifCarte ?? null,
            "couleurCarteTag" => $this->couleurCarte ?? null,
            "idColonneTag" => (isset($this->colonne)) ? $this->colonne->getIdColonne() : null
        );
    }

    /**
     * Méthode jsonSerialize
     *
     * Cette méthode retourne un tableau contenant les informations de la carte au format JSON.
     * Les clés du tableau sont les suivantes :
     * - idCarte : l'identifiant de la carte
     * - titreCarte : le titre de la carte
     * - descriptifCarte : le descriptif de la carte
     * - couleurCarte : la couleur de la carte
     * - idColonne : l'identifiant de la colonne associée à la carte
     * - affectationsCarte : les affectations de la carte
     *
     * @return array Le tableau contenant les informations de la carte au format JSON
     */
    public function jsonSerialize(): array
    {
        return [
            "idCarte" => $this->idCarte ?? null,
            "titreCarte" => $this->titreCarte ?? null,
            "descriptifCarte" => $this->descriptifCarte ?? null,
            "couleurCarte" => $this->couleurCarte ?? null,
            "idColonne" => (isset($this->colonne)) ? $this->colonne->getIdColonne() : null,
            "affectationsCarte" => $this->affectationsCarte ?? null,
        ];
    }
}