<?php

namespace App\Trellotrolle\Configuration;

use App\Trellotrolle\Modele\Repository\ConnexionBaseDeDonnees;
use PDO;

class ConfigurationBaseDeDonnees implements ConfigurationBaseDeDonneesInterface
{

    private string $login = "dumeniaudk";
    private string $motDePasse = 'K+0aDZfVK#Z8t_O*6_1NIgUaf+xu*$ea';
    private string $nomBDD = "iut";
    private string $hostname = "162.38.222.142";
    private string $port = '5673';

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getMotDePasse(): string
    {
        return $this->motDePasse;
    }

    public function getDSN() : string{
        return "pgsql:host=".$this->hostname.";port=".$this->port.";dbname=".$this->nomBDD;
    }
    public function getOptions() : array {
        // Option pour que toutes les chaines de caractères
        // en entrée et sortie de MySql soit dans le codage UTF-8
        return array(
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
        );
    }
}