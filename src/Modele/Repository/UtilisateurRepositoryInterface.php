<?php

namespace App\Trellotrolle\Modele\Repository;

interface UtilisateurRepositoryInterface
{
    public function recupererUtilisateursParEmail(string $email): array;

    public function recupererUtilisateursOrderedPrenomNom(): array;

    public function recupererTableauxOuUtilisateurEstMembre(string $login): array;

    public function supprimer(string $valeurClePrimaire): bool;
}