<?php

namespace App\Trellotrolle\Lib;

interface ConnexionUtilisateurInterface
{
    public function connecter(string $idUtilisateur): void;

    public function estConnecte(): bool;

    public function deconnecter();

    public function getIdUtilisateurConnecte(): ?string;
}