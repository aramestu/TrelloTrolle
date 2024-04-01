<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use PDOException;

interface TableauRepositoryInterface
{
    public function recupererTableauxUtilisateur(string $login): array;

    public function recupererParCodeTableau(string $codeTableau): ?AbstractDataObject;

    /**
     * @return Tableau[]
     */
    public function recupererTableauxParticipeUtilisateur(string $login): array;

    /**
     * @return Utilisateur[]
     */
    public function recupererParticipantsTableau(string $idTableau): array;

    public function getNombreTableauxTotalUtilisateur(string $login): int;

    /**
     * @throws PDOException
     */
    public function ajouterParticipant($login, $idTableau): bool;

    public function supprimerParticipant($login, $idTableau): bool;

    public function supprimerAffectation($login, $idTableau): bool;

    public function supprimer(string $valeurClePrimaire): bool;
}