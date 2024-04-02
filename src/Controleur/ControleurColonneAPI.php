<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\ConnexionUtilisateurInterface;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Service\CarteServiceInterface;
use App\Trellotrolle\Service\ColonneServiceInterface;
use App\Trellotrolle\Service\Exception\ServiceException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ControleurColonneAPI extends ControleurGenerique
{
    public function __construct (
        ContainerInterface $container,
        private ColonneServiceInterface $colonneService,
        private ConnexionUtilisateurInterface $connexionUtilisateurJWT
    )
    {
        parent::__construct($container);
    }

    private function estConnecte(): bool{
        return $this->connexionUtilisateurJWT->estConnecte();
    }

    #[Route(path: '/api/colonnes/{idColonne}', name:'api_supprimer_colonne', methods:["DELETE"])]
    public function supprimerColonne($idColonne): Response
    {
        if(! $this->estConnecte()){
            return new JsonResponse(["error" => "Vous devez être connecté"], Response::HTTP_UNAUTHORIZED);
        }
        try
        {
            $this->colonneService->supprimerColonne($idColonne, $this->connexionUtilisateurJWT->getIdUtilisateurConnecte());
            return new JsonResponse(true, Response::HTTP_NO_CONTENT); // True si ça a été supprimé
        }
        catch(\Exception $exception)
        {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }

    #[Route(path: '/api/colonnes', name:'api_creer_colonne', methods:["POST"])]
    public function creerColonne(Request $request): Response
    {
        if(! $this->estConnecte()){
            return new JsonResponse(["error" => "Vous devez être connecté"], Response::HTTP_UNAUTHORIZED);
        }
        try
        {
            $jsonObject = json_decode($request->getContent(), flags: JSON_THROW_ON_ERROR);
            $idTableau = $jsonObject->idTableau;
            $nomColonne = $jsonObject->nomColonne;

            $idColonne = $this->colonneService->creerColonne($idTableau, $nomColonne, $this->connexionUtilisateurJWT->getIdUtilisateurConnecte());
            $colonne = $this->colonneService->getColonne($idColonne);
            return new JsonResponse($colonne, Response::HTTP_OK);
        }
        catch(\Exception $exception)
        {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }

    #[Route(path: '/api/colonnes', name:'api_modifier_colonne', methods:["PATCH"])]
    public function mettreAJour(Request $request): Response
    {
        if(! $this->estConnecte()){
            return new JsonResponse(["error" => "Vous devez être connecté"], Response::HTTP_UNAUTHORIZED);
        }
        try
        {
            $jsonObject = json_decode($request->getContent(), flags: JSON_THROW_ON_ERROR);
            $idColonne = $jsonObject->idColonne;
            $nomColonne = $jsonObject->nomColonne;

            $colonne = $this->colonneService->mettreAJour($idColonne, $nomColonne, $this->connexionUtilisateurJWT->getIdUtilisateurConnecte());
            return new JsonResponse($colonne, Response::HTTP_OK);
        }
        catch(\Exception $exception)
        {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }
}