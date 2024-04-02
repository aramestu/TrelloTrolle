<?php

namespace App\Trellotrolle\Modele\DataObject;

use JsonSerializable;

/**
 * Classe Tableau
 *
 * Cette classe représente un tableau dans l'application Trellotrolle.
 * Elle hérite de la classe AbstractDataObject et implémente l'interface JsonSerializable.
 *
 * @package App\Trellotrolle\Modele\DataObject
 */
class Tableau extends AbstractDataObject implements JsonSerializable
{

    /**
     * Cette partie du code représente les propriétés d'une classe.
     * Les propriétés sont déclarées avec des types spécifiques et sont privées.
     * Les propriétés incluent:
     * - idTableau: un entier représentant l'identifiant du tableau
     * - codeTableau: une chaîne de caractères représentant le code du tableau
     * - titreTableau: une chaîne de caractères représentant le titre du tableau
     * - proprietaireTableau: un objet de la classe Utilisateur représentant le propriétaire du tableau
     * - participants: un tableau contenant les participants du tableau
     */
    private int $idTableau;
    private string $codeTableau;
    private string $titreTableau;
    private Utilisateur $proprietaireTableau;
    private array $participants;

    public function __construct()
    {
    }

    /**
     * Crée un nouvel objet Tableau avec les paramètres spécifiés.
     *
     * @param int $idTableau L'identifiant du tableau.
     * @param string $codeTableau Le code du tableau.
     * @param string $titreTableau Le titre du tableau.
     * @param Utilisateur $proprietaireTableau Le propriétaire du tableau.
     * @param array $membres Les participants du tableau.
     * @return Tableau Le nouvel objet Tableau créé.
     */
    public static function create(int $idTableau, string $codeTableau, string $titreTableau, Utilisateur $proprietaireTableau, array $membres): Tableau
    {
        $tableau = new Tableau();
        $tableau->idTableau = $idTableau;
        $tableau->codeTableau = $codeTableau;
        $tableau->titreTableau = $titreTableau;
        $tableau->proprietaireTableau = $proprietaireTableau;
        $tableau->participants = $membres;
        return $tableau;
    }

    /**
     * Méthode statique pour construire un objet Tableau à partir d'un tableau de données.
     *
     * @param array $objetFormatTableau Le tableau de données contenant les informations du tableau.
     * @return Tableau L'objet Tableau construit à partir des données fournies.
     */
    public static function construireDepuisTableau(array $objetFormatTableau): Tableau
    {
        return self::create(
            $objetFormatTableau["idtableau"],
            $objetFormatTableau["codetableau"],
            $objetFormatTableau["titretableau"],
            $objetFormatTableau["proprietairetableau"],
            $objetFormatTableau['participants']
        );
    }

    /**
     * Récupère le propriétaire du tableau.
     *
     * @return Utilisateur Le propriétaire du tableau.
     */
    public function getProprietaireTableau(): Utilisateur
    {
        return $this->proprietaireTableau;
    }

    /**
     * Définit le propriétaire du tableau.
     *
     * @param Utilisateur $proprietaire Le propriétaire du tableau.
     * @return void
     */
    public function setProprietaireTableau(Utilisateur $proprietaire): void
    {
        $this->proprietaireTableau = $proprietaire;
    }

    /**
     * Retourne l'identifiant du tableau.
     *
     * @return int|null L'identifiant du tableau.
     */
    public function getIdTableau(): ?int
    {
        return $this->idTableau;
    }

    /**
     * Définit l'identifiant du tableau.
     *
     * @param int|null $idTableau L'identifiant du tableau.
     * @return void
     */
    public function setIdTableau(?int $idTableau): void
    {
        $this->idTableau = $idTableau;
    }

    /**
     * Retourne le titre du tableau.
     *
     * @return string|null Le titre du tableau.
     */
    public function getTitreTableau(): ?string
    {
        return $this->titreTableau;
    }

    /**
     * Définit le titre du tableau.
     *
     * @param string|null $titreTableau Le titre du tableau.
     * @return void
     */
    public function setTitreTableau(?string $titreTableau): void
    {
        $this->titreTableau = $titreTableau;
    }

    /**
     * Retourne le code du tableau.
     *
     * @return string|null Le code du tableau.
     */
    public function getCodeTableau(): ?string
    {
        return $this->codeTableau;
    }

    /**
     * Méthode pour définir le code du tableau.
     *
     * @param string|null $codeTableau Le code du tableau.
     * @return void
     */
    public function setCodeTableau(?string $codeTableau): void
    {
        $this->codeTableau = $codeTableau;
    }

    /**
     * Méthode getParticipants
     *
     * Cette méthode retourne un tableau contenant les participants du tableau.
     *
     * @return array Le tableau des participants.
     */
    public function getParticipants(): array
    {
        return array_slice($this->participants, 0);
    }

    /**
     * Vérifie si l'utilisateur spécifié est le propriétaire du tableau.
     *
     * @param string $login Le login de l'utilisateur à vérifier.
     * @return bool Retourne true si l'utilisateur est le propriétaire du tableau, sinon false.
     */
    public function estProprietaire(string $login): bool
    {
        return $this->proprietaireTableau->getLogin() == $login;
    }

    /**
     * Méthode estParticipant
     *
     * Cette méthode vérifie si l'utilisateur spécifié est un participant du tableau.
     *
     * @param string $login Le login de l'utilisateur à vérifier.
     * @return bool Retourne true si l'utilisateur est un participant du tableau, sinon false.
     */
    public function estParticipant(string $login): bool
    {
        foreach ($this->participants as $participant) {
            if ($participant->getLogin() == $login) return true;
        }
        return false;
    }

    /**
     * Méthode estParticipantOuProprietaire
     *
     * Cette méthode vérifie si l'utilisateur spécifié est un participant ou le propriétaire du tableau.
     *
     * @param string $login Le login de l'utilisateur à vérifier.
     * @return bool Retourne true si l'utilisateur est un participant ou le propriétaire du tableau, sinon false.
     */
    public function estParticipantOuProprietaire(string $login): bool
    {
        return $this->estParticipant($login) || $this->estProprietaire($login);
    }

    /**
     * Méthode formatTableau
     *
     * Cette méthode retourne un tableau associatif contenant les informations du tableau formatées.
     *
     * @return array Le tableau formaté avec les clés suivantes:
     *   - "idTableauTag": l'identifiant du tableau ou null si non défini
     *   - "codeTableauTag": le code du tableau ou null si non défini
     *   - "titreTableauTag": le titre du tableau ou null si non défini
     *   - "proprietaireTableauTag": le login du propriétaire du tableau ou null si non défini
     */
    public function formatTableau(): array
    {
        return array(
            "idTableauTag" => $this->idTableau ?? null,
            "codeTableauTag" => $this->codeTableau ?? null,
            "titreTableauTag" => $this->titreTableau ?? null,
            "proprietaireTableauTag" => $this->proprietaireTableau->getLogin() ?? null
        );
    }

    /**
     * Méthode jsonSerialize
     *
     * Cette méthode est utilisée pour sérialiser l'objet Tableau en un tableau associatif.
     * Les clés du tableau sont les propriétés de l'objet Tableau, et les valeurs sont les valeurs actuelles de ces propriétés.
     * Si une propriété n'est pas définie, sa valeur sera null dans le tableau sérialisé.
     *
     * @return array Le tableau associatif sérialisé représentant l'objet Tableau.
     */
    public function jsonSerialize(): array
    {
        return array(
            "idTableau" => $this->idTableau ?? null,
            "codeTableau" => $this->codeTableau ?? null,
            "titreTableau" => $this->titreTableau ?? null,
            "proprietaireTableau" => $this->proprietaireTableau ?? null,
            "participants" => $this->participants ?? null
        );
    }
}