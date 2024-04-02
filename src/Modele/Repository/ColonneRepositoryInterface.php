<?php

namespace App\Trellotrolle\Modele\Repository;

interface ColonneRepositoryInterface
{
    public function recupererColonnesTableau(int $idTableau): array;

    public function getNombreColonnesTotalTableau(int $idTableau): int;

    public function supprimer(string $valeurClePrimaire): bool;

    public function lastInsertId(): false|string;
}