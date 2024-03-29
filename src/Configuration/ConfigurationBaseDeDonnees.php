<?php

namespace App\Trellotrolle\Configuration;

use App\Trellotrolle\Modele\Repository\ConnexionBaseDeDonnees;
use PDO;

class ConfigurationBaseDeDonnees {

	//Informations de connexion pour le serveur PostgreSQL SAE de l'IUT
    static private array $configurationBaseDeDonnees = array(
        'nomHote' => '162.38.222.142',
        'nomBaseDeDonnees' => 'iut',
        'port' => '5673',
        'login' => 'dumeniaudk',
        'motDePasse' => 'K+0aDZfVK#Z8t_O*6_1NIgUaf+xu*$ea'
    );

    static public function getLogin() : string {
        return ConfigurationBaseDeDonnees::$configurationBaseDeDonnees['login'];
    }

    static public function getNomBaseDeDonnees() : string {
        return ConfigurationBaseDeDonnees::$configurationBaseDeDonnees['nomBaseDeDonnees'];
    }

    static public function getPort() : string {
        return ConfigurationBaseDeDonnees::$configurationBaseDeDonnees['port'];
    }

    static public function getNomHote() : string {
        return ConfigurationBaseDeDonnees::$configurationBaseDeDonnees['nomHote'];
    }

    static public function getMotDePasse() : string {
        return ConfigurationBaseDeDonnees::$configurationBaseDeDonnees['motDePasse'];
    }

    public function getDSN() : string{
        return "pgsql:host=".ConfigurationBaseDeDonnees::getNomHote().";port=".ConfigurationBaseDeDonnees::getPort().";dbname=".ConfigurationBaseDeDonnees::getNomBaseDeDonnees();
    }
    public function getOptions() : array {
        // Option pour que toutes les chaines de caractères
        return array(
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
        );
    }

}