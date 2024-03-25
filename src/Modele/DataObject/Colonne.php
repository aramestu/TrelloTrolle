<?php

namespace App\Trellotrolle\Modele\DataObject;

class Colonne extends AbstractDataObject
{
    public function __construct(
        private int $idTableau,
        private int $idColonne,
        private string $titreColonne
    )
    {}

    public static function construireDepuisTableau(array $objetFormatTableau) : Colonne {
        return new Colonne(
            $objetFormatTableau["idTableau"],
            $objetFormatTableau["idcolonne"],
            $objetFormatTableau["titrecolonne"],
        );
    }

    public function getTableau(): int
    {
        return $this->idTableau;
    }

    public function setTableau(int $tableau): void
    {
        $this->idTableau = $tableau;
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
                "idColonneTag" => $this->idColonne,
                "titreColonneTag" => $this->titreColonne,
                "idTableauTag" => $this->idTableau
            );
    }
}