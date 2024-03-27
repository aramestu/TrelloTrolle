<?php

namespace App\Trellotrolle\Modele\DataObject;

use App\Trellotrolle\Modele\Repository\CarteRepository;

class Carte extends AbstractDataObject implements \JsonSerializable
{
    private int $idCarte;
    private string $titreCarte;
    private string $descriptifCarte;
    private string $couleurCarte;
    private Colonne $colonne;
    private array $affectationsCarte;

    public function __construct(){}

    public static function create(string $titreCarte, string $descriptifCarte, string $couleurCarte, Colonne $colonne, array $affectationsCarte): Carte{
        $c = new Carte();
        $c->titreCarte = $titreCarte;
        $c->descriptifCarte = $descriptifCarte;
        $c->couleurCarte = $couleurCarte;
        $c->colonne = $colonne;
        $c->affectationsCarte = $affectationsCarte;
        return $c;
    }
    public static function construireDepuisTableau(array $objetFormatTableau) : Carte {
        $col = new Colonne();
        $col->setIdColonne($objetFormatTableau["idcolonne"]);

        $c = Carte::create(
            $objetFormatTableau["titrecarte"],
            $objetFormatTableau["descriptifcarte"],
            $objetFormatTableau["couleurcarte"],
            $col,
            $objetFormatTableau["affectationsCarte"]
        );

        $c->idCarte = $objetFormatTableau["idcarte"];
        return $c;
    }

    public function getColonne(): Colonne
    {
        return $this->colonne;
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
                "idColonneTag" => $this->colonne->getIdColonne()
            );
    }

    public function jsonSerialize(): array
    {
        $tabAffectation = [];
        foreach ($this->affectationsCarte as $user) {
            $tabAffectation[] = $user->jsonSerialize();
        }
        return [
            "idCarte" => $this->idCarte,
            "titreCarte" => $this->titreCarte,
            "descriptifCarte" => $this->descriptifCarte,
            "couleurCarte" => $this->couleurCarte,
            "colonne" => $this->colonne->jsonSerialize(),
            "affectationsCarte" => $tabAffectation,
        ];
    }
}