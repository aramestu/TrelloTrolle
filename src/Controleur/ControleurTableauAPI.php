<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\ConnexionUtilisateurInterface;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Service\TableauServiceInterface;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ControleurTableauAPI extends ControleurGenerique
{
    public function __construct (
        ContainerInterface $container,
        private TableauServiceInterface $tableauService,
        private ConnexionUtilisateurInterface $connexionUtilisateurJWT
    )
    {
        parent::__construct($container);
    }

    private function estConnecte(){
        return $this->connexionUtilisateurJWT->estConnecte();
    }

    #[Route(path: '/api/{codeTableau}/afficher', name:'api_afficher_tableau', methods:["GET"])]
    public function afficherTableau(string $codeTableau) : Response {
        if(! $this->estConnecte()){
            return new JsonResponse(["error" => "Vous devez "], Response::HTTP_UNAUTHORIZED);
        }
        try {
            $tableau = $this->tableauService->getByCodeTableau($codeTableau);
            $associationColonneCarte = $this->tableauService->recupererColonnesEtCartesDuTableau($tableau->getIdTableau());
            $informationsAffectation = $this->tableauService->informationsAffectationsCartes($tableau->getIdTableau());
            return new JsonResponse([$tableau, $associationColonneCarte, $informationsAffectation]);
        } catch (Exception $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }

    #[Route(path: '/api/tableaux', name:'api_creer_tableau', methods:["POST"])]
    public function creerTableau(Request $request) : Response {
        if(! $this->estConnecte()){
            return new JsonResponse(["error" => "Vous devez être connecté!"], Response::HTTP_UNAUTHORIZED);
        }
        try {
            $jsonObject = json_decode($request->getContent(), flags: JSON_THROW_ON_ERROR);
            $loginUserConnecte = $this->connexionUtilisateurJWT->getIdUtilisateurConnecte();
            $nomTableau = $jsonObject->nomTableau;
            $tableau = $this->tableauService->creerTableau($loginUserConnecte, $nomTableau);
            return new JsonResponse($tableau);
        } catch (Exception $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }


}