<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\ConnexionUtilisateurInterface;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Service\TableauServiceInterface;
use App\Trellotrolle\Service\UtilisateurServiceInterface;
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
        private UtilisateurServiceInterface $utilisateurService,
        private ConnexionUtilisateurInterface $connexionUtilisateurJWT
    )
    {
        parent::__construct($container);
    }

    private function estConnecte(){
        return $this->connexionUtilisateurJWT->estConnecte();
    }

    #[Route(path: '/api/tableaux/{codeTableau}', name:'api_afficher_tableau', methods:["GET"])]
    public function getToutesInfosTableau(string $codeTableau) : Response { // Fonctionne
        if(! $this->estConnecte()){
            return new JsonResponse(["error" => "Vous devez "], Response::HTTP_UNAUTHORIZED);
        }
        try {
            return new JsonResponse($this->recupererToutesInfosTableau($codeTableau), Response::HTTP_OK);
        } catch (Exception $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }

    #[Route(path: '/api/tableaux', name:'api_creer_tableau', methods:["POST"])]
    public function creerTableau(Request $request) : Response { // Fonctionne
        if(! $this->estConnecte()){
            return new JsonResponse(["error" => "Vous devez être connecté!"], Response::HTTP_UNAUTHORIZED);
        }
        try {
            $jsonObject = json_decode($request->getContent(), flags: JSON_THROW_ON_ERROR);
            $loginUserConnecte = $this->connexionUtilisateurJWT->getIdUtilisateurConnecte();
            $nomTableau = $jsonObject->nomTableau;

            $tableau = $this->tableauService->creerTableau($loginUserConnecte, $nomTableau);
            return new JsonResponse($tableau, Response::HTTP_OK);
        } catch (Exception $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }

    #[Route(path: '/api/tableaux', name:'api_modifier_tableau', methods:["PATCH"])]
    public function mettreAJour(Request $request) : Response { // Fonctionne
        if(! $this->estConnecte()){
            return new JsonResponse(["error" => "Vous devez être connecté!"], Response::HTTP_UNAUTHORIZED);
        }
        try {
            $jsonObject = json_decode($request->getContent(), flags: JSON_THROW_ON_ERROR);
            $idTableau =  $jsonObject->idTableau;
            $nomTableau =  $jsonObject->nomTableau;
            $tableau = $this->tableauService->mettreAJourTableau($idTableau, $this->connexionUtilisateurJWT->getIdUtilisateurConnecte() , $nomTableau);
            return new JsonResponse($this->recupererToutesInfosTableau($tableau->getCodeTableau()), Response::HTTP_OK);
        } catch (Exception $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }

    private function recupererToutesInfosTableau(?string $codeTableau){
        $tableau = $this->tableauService->getByCodeTableau($codeTableau);
        $associationColonneCarte = $this->tableauService->recupererColonnesEtCartesDuTableau($tableau->getIdTableau());
        $informationsAffectation = $this->tableauService->informationsAffectationsCartes($tableau->getIdTableau());

        return ["tableau" => $tableau, "associationColonneCarte" => $associationColonneCarte, "informationsAffectation" => $informationsAffectation];
    }

    #[Route(path: '/api/tableaux/{idTableau}/ajouter-membre/{login}', name:'api_ajouter_membre_tableau', methods:["GET"])]
    public function ajouterMembre(string $idTableau, string $login) : Response { // Fonctionne
        if(! $this->estConnecte()){
            return new JsonResponse(["error" => "Vous devez être connecté!"], Response::HTTP_UNAUTHORIZED);
        }
        try {
            $tableau = $this->tableauService->ajouterMembre((int) $idTableau, $this->connexionUtilisateurJWT->getIdUtilisateurConnecte() , $login);
            return new JsonResponse($this->recupererToutesInfosTableau($tableau->getCodeTableau()), Response::HTTP_OK);
        } catch (Exception $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }

    #[Route(path: '/api/tableaux/{idTableau}/supprimer-membre/{login}', name:'api_supprimer_membre_tableau', methods:["DELETE"])]
    public function supprimerMembre(string $idTableau, string $login) : Response { // Fonctionne
        if(! $this->estConnecte()){
            return new JsonResponse(["error" => "Vous devez être connecté!"], Response::HTTP_UNAUTHORIZED);
        }
        try {
            $tableau = $this->tableauService->supprimerMembre((int) $idTableau, $this->connexionUtilisateurJWT->getIdUtilisateurConnecte() , $login);
            return new JsonResponse($this->recupererToutesInfosTableau($tableau->getCodeTableau()), Response::HTTP_OK);
        } catch (Exception $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }

    #[Route(path: '/api/tableaux', name:'api_liste_mes_tableau', methods:["GET"])]
    public function getListeMesTableau(){ // Fonctionne
        if(! $this->estConnecte()){
            return new JsonResponse(["error" => "Vous devez être connecté!"], Response::HTTP_UNAUTHORIZED);
        }
        try {
            $tableaux = $this->utilisateurService->recupererTableauxOuUtilisateurEstMembre($this->connexionUtilisateurJWT->getIdUtilisateurConnecte());
            return new JsonResponse($tableaux, Response::HTTP_OK);
        } catch (Exception $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }

    #[Route(path: '/api/tableaux/{idTableau}', name:'api_quitter_tableau', methods:["PATCH"])]
    public function quitterTableau(string $idTableau){ // Fonctionne
        if(! $this->estConnecte()){
            return new JsonResponse(["error" => "Vous devez être connecté!"], Response::HTTP_UNAUTHORIZED);
        }
        try {
            $tableaux = $this->tableauService->quitterTableau($this->connexionUtilisateurJWT->getIdUtilisateurConnecte(), (int) $idTableau);
            return new JsonResponse($tableaux, Response::HTTP_OK);
        } catch (Exception $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }

    #[Route(path: '/api/tableaux/{idTableau}', name:'api_supprimer_tableau', methods:["DELETE"])]
    public function supprimerTableau(string $idTableau){ // Fonctionne
        if(! $this->estConnecte()){
            return new JsonResponse(["error" => "Vous devez être connecté!"], Response::HTTP_UNAUTHORIZED);
        }
        try {
            $this->tableauService->supprimer($this->connexionUtilisateurJWT->getIdUtilisateurConnecte(), (int) $idTableau);
            return new JsonResponse('', Response::HTTP_NO_CONTENT);
        } catch (Exception $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }



}