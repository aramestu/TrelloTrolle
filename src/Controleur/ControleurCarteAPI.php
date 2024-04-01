<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\ConnexionUtilisateurInterface;
use App\Trellotrolle\Service\CarteServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Service\TableauServiceInterface;
use App\Trellotrolle\Service\UtilisateurServiceInterface;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
class ControleurCarteAPI extends ControleurGenerique
{
    public function __construct (
        ContainerInterface $container,
        private TableauServiceInterface $tableauService,
        private CarteServiceInterface $carteService,
        private ConnexionUtilisateurInterface $connexionUtilisateurJWT
    )
    {
        parent::__construct($container);
    }

    private function estConnecte(): bool{
        return $this->connexionUtilisateurJWT->estConnecte();
    }

    private function recupererToutesInfosTableau(?string $codeTableau){
        $tableau = $this->tableauService->getByCodeTableau($codeTableau);
        $associationColonneCarte = $this->tableauService->recupererColonnesEtCartesDuTableau($tableau->getIdTableau());
        $informationsAffectation = $this->tableauService->informationsAffectationsCartes($tableau->getIdTableau());

        return ["tableau" => $tableau, "associationColonneCarte" => $associationColonneCarte, "informationsAffectation" => $informationsAffectation];
    }

    #[Route(path: '/api/cartes/{idCarte}', name:'api_supprimer_carte', methods:["DELETE"])]
    public function supprimerCarte(int $idCarte) : Response { // Fonctionne
        if(! $this->estConnecte()){
            return new JsonResponse(["error" => "Vous devez "], Response::HTTP_UNAUTHORIZED);
        }
        try {
            $this->carteService->supprimerCarte($idCarte, $this->connexionUtilisateurJWT->getIdUtilisateurConnecte());
            return new JsonResponse(true, Response::HTTP_OK); // True si ça a été supprimé
        } catch (Exception $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }

    /*#[Route(path: '/api/cartes', name:'api_creer_carte', methods:["POST"])]
    public function creerCarte(Request $request) : Response { // Fonctionne
        if(! $this->estConnecte()){
            return new JsonResponse(["error" => "Vous devez "], Response::HTTP_UNAUTHORIZED);
        }
        try {
            $jsonObject = json_decode($request->getContent(), flags: JSON_THROW_ON_ERROR);

            $this->carteService->creerCarte($this->connexionUtilisateurJWT->getIdUtilisateurConnecte());
            return new JsonResponse(true, Response::HTTP_OK); // True si ça a été supprimé
        } catch (Exception $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }*/

}