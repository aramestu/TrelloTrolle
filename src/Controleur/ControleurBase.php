<?php

namespace App\Trellotrolle\Controleur;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ControleurBase extends ControleurGenerique
{
    #[Route(path: '/', name:'accueil', methods:["GET", "POST"])]
    public function accueil(): Response {
        return $this->afficherVuePHP('vueGenerale.php', [
            "pagetitle" => "Accueil",
            "cheminVueBody" => "base/accueil.php"
        ]);
    }
}