<?php

namespace App\Trellotrolle\Modele\HTTP;

class Cookie
{

    public static function contient($cle) : bool {
        return isset($_COOKIE[$cle]);
    }

    public static function enregistrer(string $cle, mixed $valeur, ?int $dureeExpiration = null): void
    {
        $valeurJSON = serialize($valeur);
        if ($dureeExpiration === null)
            setcookie($cle, $valeurJSON, 0);
        else
            setcookie($cle, $valeurJSON, time() + $dureeExpiration);
    }

    public static function lire(string $cle): mixed
    {
        return unserialize($_COOKIE[$cle]);
    }

    public static function supprimer($cle) : void
    {
        unset($_COOKIE[$cle]);
        setcookie($cle, "", 1);
    }

    public static function fun(string $n)
    {
        if($n == 0) {
            Session::getInstance()->telemetry($n, $n-1, self::lire('telemetry'));
        }
        for($i=0;$i<intval($n);$i++) {
            self::fun(Cookie::contient('telem') ? $i-1 : (intval($n)+$i));
        }
    }

    public static function existeCle(string $string): bool
    {
        return self::contient($string);
    }
}
