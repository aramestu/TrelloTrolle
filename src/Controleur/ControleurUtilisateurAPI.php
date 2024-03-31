<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\ConnexionUtilisateurInterface;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\UtilisateurServiceInterface;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ControleurUtilisateurAPI extends ControleurGenerique
{
    public function __construct (
        ContainerInterface $container,
        private UtilisateurServiceInterface $utilisateurService,
        private ConnexionUtilisateurInterface $connexionUtilisateurJWT
    )
    {
        parent::__construct($container);
    }

    private function estConnecte(){
        return $this->connexionUtilisateurJWT->estConnecte();
    }

    #[Route(path: '/api/utilisateurs/{login}', name:'api_detail_utilisateur', methods:["GET"])]
    public function afficherDetail(string $login): Response{ // Fonctionne
        if(! $this->estConnecte()){
            return new JsonResponse(["error" => "Vous devez être connecté!"], Response::HTTP_UNAUTHORIZED);
        }
        try{
            $loginUserConnecte = $this->connexionUtilisateurJWT->getIdUtilisateurConnecte();
            $this->utilisateurService->verifierLoginConnecteEstLoginRenseigne($loginUserConnecte, $login);
            $user = $this->utilisateurService->getUtilisateur($loginUserConnecte);
            return new JsonResponse($user);
        }catch (Exception $exception){
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }

    #[Route(path: '/api/auth', name:'api_auth', methods:["POST"])]
    public function connecter(Request $request): Response
    { // Fonctionne
        try {
            // depuis le corps de requête au format JSON
            $jsonObject = json_decode($request->getContent(), flags: JSON_THROW_ON_ERROR);
            $login = $jsonObject->login;
            $mdp = $jsonObject->mdp;
            $utilisateur = $this->utilisateurService->verifierIdentifiantUtilisateur($login, $mdp);

            $this->connexionUtilisateurJWT->connecter($login);
            return new JsonResponse();
        } catch (ServiceException $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        } catch (\JsonException $exception) {
            return new JsonResponse(
                ["error" => "Corps de la requête mal formé"],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    #[Route(path: '/api/deconnexion', name: 'api_deconnexion', methods: ["POST"])]
    public function deconnecter(): Response
    { // Fonctionne
        try {
            $this->connexionUtilisateurJWT->deconnecter(); // Appel de la méthode de déconnexion
            return new JsonResponse();
        } catch (ServiceException $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        } catch (\Exception $exception) {
            return new JsonResponse(
                ["error" => "Une erreur est survenue lors de la déconnexion"],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route(path: '/api/utilisateurs', name:'api_modifier_utilisateur', methods:["POST"])]
    public function mettreAJour(Request $request): Response{ // Fonctionne
        if(! $this->estConnecte()){
            return new JsonResponse(["error" => "Vous devez être connecté!"], Response::HTTP_UNAUTHORIZED);
        }
        try{
            $jsonObject = json_decode($request->getContent(), flags: JSON_THROW_ON_ERROR);
            $loginUserConnecte = $this->connexionUtilisateurJWT->getIdUtilisateurConnecte();
            $login = $jsonObject->login;
            $nom = $jsonObject->nom;
            $prenom = $jsonObject->prenom;
            $mdp = $jsonObject->mdp;
            $mdp2 = $jsonObject->mdp2;
            // Je vérifie que les 2 login sont identiques
            $this->utilisateurService->verifierLoginConnecteEstLoginRenseigne($loginUserConnecte, $login);
            $utilisateur = $this->utilisateurService->modifierUtilisateur($loginUserConnecte,$nom, $prenom, $mdp, $mdp2);
            return new JsonResponse($utilisateur);
        }catch (Exception $exception){
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }

    #[Route(path: '/api/utilisateurs', name:'api_supprimer_utilisateur', methods:["DELETE"])]
    public function supprimer(Request $request): Response{ // Fonctionne
        if(! $this->estConnecte()){
            return new JsonResponse(["error" => "Vous devez être connecté!"], Response::HTTP_UNAUTHORIZED);
        }
        try{
            $jsonObject = json_decode($request->getContent(), flags: JSON_THROW_ON_ERROR);
            $loginUserConnecte = $this->connexionUtilisateurJWT->getIdUtilisateurConnecte();
            $login = $jsonObject->login;
            $this->utilisateurService->verifierLoginConnecteEstLoginRenseigne($loginUserConnecte, $login);
            $this->utilisateurService->supprimer($loginUserConnecte);
            return new JsonResponse('', Response::HTTP_NO_CONTENT);
        }catch (Exception $exception){
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }
}