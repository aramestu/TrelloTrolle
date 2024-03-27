<?php

namespace App\Trellotrolle\Modele\DataObject;

class Colonne extends AbstractDataObject implements \JsonSerializable
{
    private int $idColonne;
    private string $titreColonne;
    private Tableau $tableau;

    public function __construct(){}

    public static function create(string $titreColonne, Tableau $tableau): Colonne{
        $c = new Colonne();
        $c->titreColonne = $titreColonne;
        $c->tableau = $tableau;
        return $c;
    }

    public static function construireDepuisTableau(array $objetFormatTableau) : Colonne {
        $t = new Tableau();
        $t->setIdTableau($objetFormatTableau["idTableau"]);

        $c = Colonne::create(
            $objetFormatTableau["titrecolonne"],
            $t
        );
        $c->idColonne = $objetFormatTableau["idcolonne"];

        return $c;
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
                "idColonneTag" => $this->idColonne,
                "titreColonneTag" => $this->titreColonne,
                "idTableauTag" => $this->tableau->getIdTableau()
            );
    }

    public function jsonSerialize(): array
    {
        return [
            "idColonne" => $this->idColonne,
            "titreColonne" => $this->titreColonne,
            "tableau" => $this->tableau->jsonSerialize()
        ];
    }
}