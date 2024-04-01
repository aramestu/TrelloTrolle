<?php

namespace App\Trellotrolle\Modele\Repository;

use PDO;

/**
 * Interface ConnexionBaseDeDonneesInterface
 *
 * Cette interface définit la méthode getPdo() qui doit être implémentée par les classes qui veulent se connecter à une base de données.
 */
interface ConnexionBaseDeDonneesInterface
{

    /**
     * Cette méthode retourne une instance de la classe PDO.
     *
     * @return PDO L'instance de la classe PDO.
     */
    public function getPdo(): PDO;
}