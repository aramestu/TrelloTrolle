<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\Carte;
use PDOException;

interface CarteRepositoryInterface
{
    public function recupererCartesColonne(int $idcolonne): array;

    public function recupererCartesTableau(int $idTableau): array;

    /**
     * @return Carte[]
     */
    public function recupererCartesUtilisateur(string $login): array;

    public function getNombreCartesTotalUtilisateur(string $login): int;

    public function recupererAffectationsCartes(string $idCarte): array;

    /**
     * @throws PDOException
     */
    public function ajouterAffectation($login, $idCarte): bool;

    public function supprimerAffectation($login, $idCarte): bool;

    public function supprimer(string $valeurClePrimaire): bool;
}