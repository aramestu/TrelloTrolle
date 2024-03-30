<?php

namespace App\Trellotrolle\Test;

use App\Trellotrolle\Configuration\ConfigurationBaseDeDonneesInterface;

class ConfigurationBDDTest implements ConfigurationBaseDeDonneesInterface
{
    public function getLogin(): string
    {
        return "lemoinem";
    }

    public function getMotDePasse(): string
    {
        return "19102004";
    }

    public function getDSN(): string
    {
        return "pgsql:host=162.38.222.142;port=5673;dbname=iut;user=mon_utilisateur;password=mon_mot_de_passe";
    }

    public function getOptions(): array
    {
        return array(
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
        );
    }
}