<?php

namespace App\Trellotrolle\Modele\DataObject;

class Tableau extends AbstractDataObject implements \JsonSerializable
{

    private int $idTableau;
    private string $codeTableau;
    private string $titreTableau;
    private Utilisateur $proprietaireTableau;
    private array $participants;

    public function __construct()
    {}

    public static function create(int $idTableau, string $codeTableau, string $titreTableau, Utilisateur $proprietaireTableau, array $membres)
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
        return array_slice($this->participants, 0);
    }

    public function estProprietaire(string $login):bool
    {
        return $this->proprietaireTableau->getLogin() == $login;
    }

    public function estParticipant(string $login): bool
    {
        foreach ($this->participants as $participant){
            if($participant->getLogin() == $login) return true;
        }
        return false;
    }

    public function estParticipantOuProprietaire(string $login): bool
    {
        return $this->estParticipant($login) || $this->estParticipant($login);
    }

    public function formatTableau(): array
    {
           return array(
                "idTableauTag" => $this->idTableau ?? null,
                "codeTableauTag" => $this->codeTableau ?? null,
                "titreTableauTag" => $this->titreTableau ?? null,
                "proprietaireTableauTag" => $this->proprietaireTableau->getLogin() ?? null
            );
    }

    public function jsonSerialize(): mixed
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