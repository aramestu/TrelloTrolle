<?php

namespace App\Trellotrolle\Configuration;

/**
 *  Classe ConfigurationSite
 *
 *  Cette classe contient une méthode statique getDureeExpirationSession() qui retourne la durée d'expiration de la session en secondes.
 */
class ConfigurationSite {

    /**
     * @return int La durée d'expiration de la session en secondes.
     */
    static public function getDureeExpirationSession() : int {
        return 36000;
    }
}