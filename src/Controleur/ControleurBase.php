<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Modele\Repository\ColonneRepository;
use App\Trellotrolle\Modele\Repository\TableauRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ControleurBase extends ControleurGenerique
{
    #[Route(path: '/', name:'accueil', methods:["GET", "POST"])]
    public function accueil(): Response {
        return $this->afficherVue('vueGenerale.php', [
            "pagetitle" => "Accueil",
            "cheminVueBody" => "base/accueil.php"
        ]);
    }

    #[Route(path: '/test', name:'accueil', methods:["GET"])]
    public function test(): Response
    {
        /*ob_start();
        var_dump(;
        $corpsReponse = ob_get_clean();*/
        return new JsonResponse((new ColonneRepository())->recupererParClePrimaire(1));
    }
}