<?php

namespace App\Trellotrolle\Modele\DataObject;

use App\Trellotrolle\Modele\Repository\CarteRepository;

class Carte extends AbstractDataObject implements \JsonSerializable
{
    private $idCarte;
    private string $titreCarte;
    private string $descriptifCarte;
    private string $couleurCarte;
    private Colonne $colonne;
    private array $affectationsCarte;

    public function __construct(){}

    public static function create(string $idCarte,string $titreCarte, string $descriptifCarte, string $couleurCarte, Colonne $colonne, array $affectationsCarte): Carte{
        $carte = new Carte();
        $carte->idCarte = $idCarte;
        $carte->titreCarte = $titreCarte;
        $carte->descriptifCarte = $descriptifCarte;
        $carte->couleurCarte = $couleurCarte;
        $carte->colonne = $colonne;
        $carte->affectationsCarte = $affectationsCarte;
        return $carte;
    }
    public static function construireDepuisTableau(array $objetFormatTableau) : Carte {
          $carte = Carte::create(
            $objetFormatTableau["idcarte"],
            $objetFormatTableau["titrecarte"],
            $objetFormatTableau["descriptifcarte"],
            $objetFormatTableau["couleurcarte"],
            $objetFormatTableau["colonne"],
            $objetFormatTableau["affectationscarte"]
        );

        return $carte;
    }

    public function getColonne(): Colonne
    {
        return $this->colonne;
    }

    public function setColonne(Colonne $colonne): void
    {
        $this->colonne = $colonne;
    }

    public function getIdCarte(): ?int
    {
        return $this->idCarte;
    }

    public function setIdCarte(?int $idCarte): void
    {
        $this->idCarte = $idCarte;
    }

    public function getTitreCarte(): ?string
    {
        return $this->titreCarte;
    }

    public function setTitreCarte(?string $titreCarte): void
    {
        $this->titreCarte = $titreCarte;
    }

    public function getDescriptifCarte(): ?string
    {
        return $this->descriptifCarte;
    }

    public function setDescriptifCarte(?string $descriptifCarte): void
    {
        $this->descriptifCarte = $descriptifCarte;
    }

    public function getCouleurCarte(): ?string
    {
        return $this->couleurCarte;
    }

    public function setCouleurCarte(?string $couleurCarte): void
    {
        $this->couleurCarte = $couleurCarte;
    }

    public function getAffectationsCarte(): ?array
    {
        return array_slice($this->affectationsCarte, 0);
    }

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