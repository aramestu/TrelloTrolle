<?php

namespace App\Trellotrolle\Configuration;

/**
 * Interface ConfigurationBaseDeDonneesInterface
 *
 * Cette interface définit les méthodes nécessaires pour récupérer les informations de configuration de la base de données.
 *
 * @package App\Trellotrolle\Configuration
 */
interface ConfigurationBaseDeDonneesInterface
{
    /**
     * Méthode getLogin()
     *
     * Cette méthode retourne une chaîne de caractères représentant le login utilisé pour se connecter à la base de données.
     *
     * @return string
     */
    public function getLogin(): string;

    /**
     * Méthode getMotDePasse()
     *
     * Cette méthode retourne une chaîne de caractères représentant le mot de passe utilisé pour se connecter à la base de données.
     *
     * @return string
     */
    public function getMotDePasse(): string;

    /**
     * Méthode getDSN()
     *
     * Cette méthode retourne une chaîne de caractères représentant le DSN (Data Source Name) utilisé pour se connecter à la base de données.
     *
     * @return string
     */
    public function getDSN(): string;

    /**
     * Méthode getOptions()
     *
     * Cette méthode retourne un tableau contenant les options utilisées pour la configuration de la base de données.
     *
     * @return array
     */
    public function getOptions(): array;
}