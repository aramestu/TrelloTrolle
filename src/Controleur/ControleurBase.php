<?php

namespace App\Trellotrolle\Controleur;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ControleurBase extends ControleurGenerique
{
    #[Route(path: '/', name:'accueil', methods:["GET", "POST"])]
    public function accueil(): Response {
        return $this->afficherTwig("base/accueil.html.twig", ["pagetitle" => "Accueil"]);
    }

    #[Route(path: '/test', name:'test', methods:["GET", "POST"])]
    public function test(): Response {
        return new JsonResponse($this->container->get("tableau_service")->informationsAffectationsCartes('1'));
    }

}