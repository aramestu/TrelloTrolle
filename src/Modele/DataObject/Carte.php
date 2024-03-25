<?php

namespace App\Trellotrolle\Modele\DataObject;

use App\Trellotrolle\Modele\Repository\CarteRepository;

class Carte extends AbstractDataObject
{
    public function __construct(
        private int $idCarte,
        private string $titreCarte,
        private string $descriptifCarte,
        private string $couleurCarte,
        private int $idColonne,
        private array $affectationsCarte,
    )
    {}

    public static function construireDepuisTableau(array $objetFormatTableau) : Carte {
        return new Carte(
            $objetFormatTableau["idcarte"],
            $objetFormatTableau["titrecarte"],
            $objetFormatTableau["descriptifcarte"],
            $objetFormatTableau["couleurcarte"],
            $objetFormatTableau["idcolonne"],
            CarteRepository::recupererAffectationsCartes($objetFormatTableau["idcarte"])
        );
    }

    public function getColonne(): int
    {
        return $this->colonne;
    }

    public function setColonne(int $colonne): void
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
        return $this->affectationsCarte;
    }

    public function setAffectationsCarte(?array $affectationsCarte): void
    {
        $this->affectationsCarte = $affectationsCarte;
    }

    public function formatTableau(): array
    {
            return array(
                "idcarteTag" => $this->idCarte,
                "titrecarteTag" => $this->titreCarte,
                "descriptifcarteTag" => $this->descriptifCarte,
                "couleurcarteTag" => $this->couleurCarte,
                "idColonneTag" => $this->idColonne
            );
    }

}