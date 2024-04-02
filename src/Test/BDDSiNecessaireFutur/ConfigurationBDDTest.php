<?php

namespace App\Trellotrolle\Test\BDDSiNecessaireFutur;

use App\Trellotrolle\Configuration\ConfigurationBaseDeDonneesInterface;

class ConfigurationBDDTest implements ConfigurationBaseDeDonneesInterface
{
    public function getLogin(): string
    {
        return "mon_utilisateur";
    }

    public function getMotDePasse(): string
    {
        return "mon_mot_de_passe";
    }

    public function getDSN(): string
    {
        //changer les informations
        return "pgsql:host=ip;port=5236;dbname=postgres;user=mon_utilisateur;password=mon_mot_de_passe";
    }

    public function getOptions(): array
    {
        return array(
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
        );
    }
}