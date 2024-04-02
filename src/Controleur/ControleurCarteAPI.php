<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\ConnexionUtilisateurInterface;
use App\Trellotrolle\Service\CarteServiceInterface;
use App\Trellotrolle\Service\ColonneServiceInterface;
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
        private CarteServiceInterface $carteService,
        private TableauServiceInterface $tableauService,
        private ColonneServiceInterface $colonneService,
        private ConnexionUtilisateurInterface $connexionUtilisateurJWT
    )
    {
        parent::__construct($container);
    }

    private function estConnecte(): bool{
        return $this->connexionUtilisateurJWT->estConnecte();
    }

    #[Route(path: '/api/cartes/{idCarte}', name:'api_details_carte', methods:["GET"])]
    public function detailsCarte(string $idCarte) : Response { // Fonctionne
        if(! $this->estConnecte()){
            return new JsonResponse(["error" => "Vous devez "], Response::HTTP_UNAUTHORIZED);
        }
        try {
            $carte = $this->carteService->getCarte((int)$idCarte);
            $colonne = $this->colonneService->getColonne($carte->getColonne()->getIdColonne());
            $tableau = $this->tableauService->getByIdTableau($colonne->getTableau()->getIdTableau());

            $this->tableauService->verifierParticipant($this->connexionUtilisateurJWT->getIdUtilisateurConnecte(), $tableau->getIdTableau());
            return new JsonResponse(["carte" => $carte, "tableau" => $tableau], Response::HTTP_OK); // True si ça a été supprimé
        } catch (Exception $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }

    #[Route(path: '/api/cartes/{idCarte}', name:'api_supprimer_carte', methods:["DELETE"])]
    public function supprimerCarte(string $idCarte) : Response { // Fonctionne
        if(! $this->estConnecte()){
            return new JsonResponse(["error" => "Vous devez "], Response::HTTP_UNAUTHORIZED);
        }
        try {
            $this->carteService->supprimerCarte((int)$idCarte, $this->connexionUtilisateurJWT->getIdUtilisateurConnecte());
            return new JsonResponse(true, Response::HTTP_OK); // True si ça a été supprimé
        } catch (Exception $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }

    #[Route(path: '/api/cartes', name:'api_creer_carte', methods:["POST"])]
    public function creerCarte(Request $request) : Response { // Fonctionne
        if(! $this->estConnecte()){
            return new JsonResponse(["error" => "Vous devez "], Response::HTTP_UNAUTHORIZED);
        }
        try {
            $jsonObject = json_decode($request->getContent(), flags: JSON_THROW_ON_ERROR);
            $idColonne = $jsonObject->idColonne;
            $titreCarte = $jsonObject->titreCarte;
            $descriptifCarte = $jsonObject->descriptifCarte;
            $couleurCarte = $jsonObject->couleurCarte;
            $affectationsCarte = $jsonObject->affectationsCarte;
            $idCarte = $this->carteService->creerCarte($idColonne, $titreCarte, $descriptifCarte, $couleurCarte, $this->connexionUtilisateurJWT->getIdUtilisateurConnecte(), $affectationsCarte);

            $carte = $this->carteService->getCarte($idCarte);
            return new JsonResponse($carte, Response::HTTP_OK); // Renvoie la carte
        } catch (Exception $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }

    #[Route(path: '/api/cartes', name:'api_modifier_carte', methods:["PATCH"])]
    public function mettreAJour(Request $request) : Response { // Fonctionne
        if(! $this->estConnecte()){
            return new JsonResponse(["error" => "Vous devez "], Response::HTTP_UNAUTHORIZED);
        }
        try {
            $jsonObject = json_decode($request->getContent(), flags: JSON_THROW_ON_ERROR);
            $idCarte = $jsonObject->idCarte;
            $idColonne = $jsonObject->idColonne;
            $titreCarte = $jsonObject->titreCarte;
            $descriptifCarte = $jsonObject->descriptifCarte;
            $couleurCarte = $jsonObject->couleurCarte;
            $affectationsCarte = $jsonObject->affectationsCarte;

            $carte = $this->carteService->mettreAJourCarte($idCarte, $idColonne,$titreCarte,$descriptifCarte,$couleurCarte, $this->connexionUtilisateurJWT->getIdUtilisateurConnecte(), $affectationsCarte);
            return new JsonResponse($carte, Response::HTTP_OK); // Renvoie la colonne avec la carte crée en plus
        } catch (Exception $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }

}