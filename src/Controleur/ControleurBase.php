<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Configuration\ConfigurationBaseDeDonnees;
use App\Trellotrolle\Modele\HTTP\Cookie;
use App\Trellotrolle\Modele\Repository\ConnexionBaseDeDonnees;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ControleurBase extends ControleurGenerique
{
    #[Route(path: '/', name:'accueil', methods:["GET", "POST"])]
    public function accueil(): Response {
        return $this->afficherTwig("base/accueil.html.twig", ["pagetitle" => "Accueil"]);
    }

}