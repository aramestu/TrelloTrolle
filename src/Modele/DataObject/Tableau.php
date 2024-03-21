<?php

namespace App\Trellotrolle\Modele\DataObject;

class Tableau extends AbstractDataObject
{
    public function __construct(
        private int $idTableau,
        private string $codeTableau,
        private string $titreTableau,
        private string $proprietaireTableau,
    )
    {}

    public static function construireDepuisTableau(array $objetFormatTableau) : Tableau {
        return new Tableau(
            $objetFormatTableau["idtableau"],
            $objetFormatTableau["codetableau"],
            $objetFormatTableau["titretableau"],
            $objetFormatTableau["proprietaireTableau"]
        );
    }

    public function getProprietaire(): string
    {
        return $this->proprietaireTableau;
    }

    public function setProprietaire(string $proprietaire): void
    {
        $this->utilisateur = $proprietaire;
    }

    public function getIdTableau(): ?int
    {
        return $this->idTableau;
    }

    public function setIdTableau(?int $idTableau): void
    {
        $this->idTableau = $idTableau;
    }

    public function getTitreTableau(): ?string
    {
        return $this->titreTableau;
    }

    public function setTitreTableau(?string $titreTableau): void
    {
        $this->titreTableau = $titreTableau;
    }

    public function getCodeTableau(): ?string
    {
        return $this->codeTableau;
    }

    public function setCodeTableau(?string $codeTableau): void
    {
        $this->codeTableau = $codeTableau;
    }

    public function formatTableau(): array
    {
           return array(
                "idtableauTag" => $this->idTableau,
                "codetableauTag" => $this->codeTableau,
                "titretableauTag" => $this->titreTableau,
                "proprietaireTableauTag" => $this->proprietaireTableau
            );
    }
}