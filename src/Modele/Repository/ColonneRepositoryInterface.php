<?php

namespace App\Trellotrolle\Modele\Repository;

/**
 * Interface ColonneRepositoryInterface
 *
 * Cette interface définit les méthodes nécessaires pour manipuler les colonnes d'un tableau.
 */
interface ColonneRepositoryInterface
{

    /**
     * Méthode pour récupérer les colonnes d'un tableau.
     *
     * @param int $idTableau L'identifiant du tableau.
     * @return array Les colonnes du tableau.
     */
    public function recupererColonnesTableau(int $idTableau): array;

    /**
     * Méthode pour obtenir le nombre total de colonnes dans un tableau.
     *
     * @param int $idTableau L'identifiant du tableau.
     * @return int Le nombre total de colonnes dans le tableau.
     */
    public function getNombreColonnesTotalTableau(int $idTableau): int;

    /**
     * Méthode pour supprimer une entrée en utilisant une valeur de clé primaire.
     *
     * @param string $valeurClePrimaire La valeur de la clé primaire de l'entrée à supprimer.
     * @return bool Retourne true si l'entrée a été supprimée avec succès, sinon false.
     */
    public function supprimer(string $valeurClePrimaire): bool;
}