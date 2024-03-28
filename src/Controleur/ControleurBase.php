<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\ConnexionUtilisateurSession;
use App\Trellotrolle\Lib\MotDePasse;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\ColonneRepository;
use App\Trellotrolle\Modele\Repository\TableauRepository;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;
use App\Trellotrolle\Service\UtilisateurService;
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

    #[Route(path: '/test', name:'test', methods:["GET"])]
    public function test(): Response {
        ob_start();
        $serviceUtilisateur = new UtilisateurService(new UtilisateurRepository(), new TableauRepository(), new MotDePasse());
        $connexionUtilisateurSession = new ConnexionUtilisateurSession();
        echo "-----------------";
        var_dump($serviceUtilisateur->getUtilisateur("thibaut"));
        echo "-----------------";
        $bodyResponse = ob_get_clean();
        return new Response($bodyResponse);
    }

}