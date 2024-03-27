<?php

namespace App\Trellotrolle\Modele\DataObject;

use JsonSerializable;

class Tableau extends AbstractDataObject implements JsonSerializable
{
    private int $idTableau;
    private string $codeTableau;
    private string $titreTableau;
    private Utilisateur $proprietaireTableau;
    private array $participants;

    public function __construct(){}

    public static function create(string $codeTableau, string $titreTableau, Utilisateur $proprietaireTableau, array $participants): Tableau{
        $t = new Tableau();
        $t->codeTableau = $codeTableau;
        $t->titreTableau = $titreTableau;
        $t->proprietaireTableau = $proprietaireTableau;
        $t->participants = $participants;
        return $t;
    }

    public static function construireDepuisTableau(array $objetFormatTableau) : Tableau {
        $proprio = new Utilisateur();
        $proprio->setLogin($objetFormatTableau["proprietaireTableau"]);

        $t = Tableau::create(
            $objetFormatTableau["codetableau"],
            $objetFormatTableau["titretableau"],
            $proprio,
            $objetFormatTableau["participants"] ??= []
        );
        $t->idTableau = $objetFormatTableau["idtableau"];
        return $t;
    }

    public function getParticipants(): array
    {
        return array_slice($this->participants, 0);
    }

    public function getProprietaire(): Utilisateur
    {
        return $this->proprietaireTableau;
    }

    public function estProprietaire(string $login): bool{
        return $this->proprietaireTableau == $login;
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
                "proprietaireTableauTag" => $this->proprietaireTableau,
            );
    }

    public function jsonSerialize(): array
    {
        $tabParticipants = [];
        foreach ($this->participants as $user) {
            $tabParticipants[] = $user->jsonSerialize();
        }
        return [
            "idTableau" => $this->idTableau,
            "codeTableau" => $this->codeTableau,
            "titreTableau" => $this->titreTableau,
            "proprietaireTableau" => $this->proprietaireTableau,
            "participants" => $tabParticipants
        ];
    }
}