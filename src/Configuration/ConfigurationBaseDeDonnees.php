<?php

namespace App\Trellotrolle\Configuration;

use PDO;

/**
 * Classe ConfigurationBaseDeDonnees
 *
 * Cette classe implémente l'interface ConfigurationBaseDeDonneesInterface et fournit les informations de configuration
 * nécessaires pour établir une connexion à la base de données.
 *
 * @package App\Trellotrolle\Configuration
 */
class ConfigurationBaseDeDonnees implements ConfigurationBaseDeDonneesInterface
{

    /**
     * Code Snippet
     *
     * Ce code représente une section de configuration contenant les informations de connexion à une base de données.
     * Il contient les propriétés suivantes :
     * - login : le nom d'utilisateur pour la connexion à la base de données
     * - motDePasse : le mot de passe pour la connexion à la base de données
     * - nomBDD : le nom de la base de données
     * - hostname : l'adresse IP ou le nom d'hôte du serveur de la base de données
     * - port : le numéro de port pour la connexion à la base de données
     *
     */
    private string $login = "dumeniaudk";
    private string $motDePasse = 'K+0aDZfVK#Z8t_O*6_1NIgUaf+xu*$ea';
    private string $nomBDD = "iut";
    private string $hostname = "162.38.222.142";
    private string $port = '5673';

    /**
     * Méthode getLogin
     *
     * Cette méthode retourne la valeur de la propriété "login" de l'objet courant.
     *
     * @return string Le nom d'utilisateur pour la connexion à la base de données.
     */
    public function getLogin(): string
    {
        return $this->login;
    }

    /**
     * Méthode getMotDePasse
     *
     * Cette méthode retourne la valeur de la propriété "motDePasse" de l'objet courant.
     *
     * @return string Le mot de passe pour la connexion à la base de données.
     */
    public function getMotDePasse(): string
    {
        return $this->motDePasse;
    }

    /**
     * Méthode getDSN
     *
     * Cette méthode retourne une chaîne de caractères représentant le DSN (Data Source Name) pour la connexion à une base de données PostgreSQL.
     * Le DSN est construit en utilisant les informations de configuration suivantes :
     * - hostname : l'adresse IP ou le nom d'hôte du serveur de la base de données
     * - port : le numéro de port pour la connexion à la base de données
     * - nomBDD : le nom de la base de données
     *
     * @return string Le DSN pour la connexion à la base de données PostgreSQL.
     */
    public function getDSN(): string
    {
        return "pgsql:host=" . $this->hostname . ";port=" . $this->port . ";dbname=" . $this->nomBDD;
    }

    /**
     * Méthode getOptions
     *
     * Cette méthode retourne un tableau contenant les options de configuration pour une connexion à une base de données MySQL.
     * L'option spécifiée dans ce cas est "PDO::MYSQL_ATTR_INIT_COMMAND" avec pour valeur "SET NAMES utf8".
     *
     * @return array Les options de configuration pour la connexion à une base de données MySQL.
     */
    public function getOptions(): array
    {
        // Option pour que toutes les chaines de caractères
        // en entrée et sortie de MySql soit dans le codage UTF-8
        return [];
    }
}