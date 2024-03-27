<?php

namespace App\Trellotrolle\Modele\DataObject;

class Tableau extends AbstractDataObject implements \JsonSerializable
{

    private string $idTableau;
    private string $codeTableau;
    private string $titreTableau;
    private Utilisateur $proprietaireTableau;
    private array $participants;

    public function __construct()
    {}

    public static function create(string $idTableau, string $codeTableau, string $titreTableau, Utilisateur $proprietaireTableau, array $membres)
    {
        $tableau = new Tableau();
        $tableau->idTableau = $idTableau;
        $tableau->codeTableau = $codeTableau;
        $tableau->titreTableau = $titreTableau;
        $tableau->proprietaireTableau = $proprietaireTableau;
        $tableau->participants = $membres;
        return $tableau;
    }

    public static function construireDepuisTableau(array $objetFormatTableau) : Tableau {
        return self::create(
            $objetFormatTableau["idtableau"],
            $objetFormatTableau["codetableau"],
            $objetFormatTableau["titretableau"],
            $objetFormatTableau["proprietairetableau"],
            $objetFormatTableau['participants']
        );
    }

    public function getProprietaireTableau(): Utilisateur
    {
        return $this->proprietaireTableau;
    }

    public function setProprietaireTableau(Utilisateur $proprietaire): void
    {
        $this->proprietaireTableau = $proprietaire;
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

    public function getParticipants(): array
    {
        return array_slice($this->participants, 0, sizeof($this->participants));
    }

    public function formatTableau(): array
    {
           return array(
                "idtableauTag" => $this->idTableau,
                "codetableauTag" => $this->codeTableau,
                "titretableauTag" => $this->titreTableau,
                "proprietaireTableauTag" => $this->proprietaireTableau->getLogin()
            );
    }

    public function jsonSerialize(): mixed
    {
        return array(
            "idTableau" => $this->idTableau,
            "codeTableau" => $this->codeTableau,
            "titreTableau" => $this->titreTableau,
            "proprietaireTableau" => $this->proprietaireTableau,
            "participants" => $this->participants
        );
    }
}