<?php

namespace App\Trellotrolle\Modele\DataObject;

class Colonne extends AbstractDataObject implements \JsonSerializable
{
    private string $idColonne;
    private string $titreColonne;
    private Tableau $tableau;

    public function __construct(){}

    public static function create(string $idColonne, string $titreColonne, Tableau $tableau): Colonne{
        $colonne = new Colonne();
        $colonne->idColonne = $idColonne;
        $colonne->titreColonne = $titreColonne;
        $colonne->tableau = $tableau;
        return $colonne;
    }

    public static function construireDepuisTableau(array $objetFormatTableau) : Colonne {

        return self::create(
            $objetFormatTableau["idcolonne"],
            $objetFormatTableau["titrecolonne"],
            $objetFormatTableau['tableau']
        );
    }

    public function getTableau(): Tableau
    {
        return $this->tableau;
    }

    public function getIdColonne(): ?int
    {
        return $this->idColonne;
    }

    public function setIdColonne(?int $idColonne): void
    {
        $this->idColonne = $idColonne;
    }

    public function getTitreColonne(): ?string
    {
        return $this->titreColonne;
    }

    public function setTitreColonne(?string $titreColonne): void
    {
        $this->titreColonne = $titreColonne;
    }

    public function formatTableau(): array
    {
        return array(
                "idColonneTag" => $this->idColonne ??null,
                "titreColonneTag" => $this->titreColonne ?? null,
                "idTableauTag" => $this->tableau->getIdTableau() ?? null
            );
    }

    public function jsonSerialize(): array
    {
        return [
            "idColonne" => $this->idColonne ?? null,
            "titreColonne" => $this->titreColonne ?? null,
            "idTableau" => (isset($this->tableau)) ? $this->tableau->getIdTableau() : null
        ];
    }
}