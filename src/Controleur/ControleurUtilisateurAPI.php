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

    #[Route(path: '/api/utilisateurs/{idUtilisateur}', name:'detail_utilisateurAPI', methods:["GET"])]
    public function afficherDetail($idUtilisateur): Response{
        try{
            $user = $this->utilisateurService->getUtilisateur($idUtilisateur);
            return new JsonResponse($user);
        }catch (Exception $exception){
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }

    #[Route(path: '/api/auth', name:'api_auth', methods:["POST"])]
    public function connecter(Request $request): Response
    {
        try {
            // depuis le corps de requête au format JSON
            $jsonObject = json_decode($request->getContent(), flags: JSON_THROW_ON_ERROR);
            $login = $jsonObject->login;
            $password = $jsonObject->password;
            $utilisateur = $this->utilisateurService->verifierIdentifiantUtilisateur($login, $password);

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
    public function deconnecter(Request $request): Response
    {
        try {
            $jsonObject = json_decode($request->getContent(), flags: JSON_THROW_ON_ERROR);
            $login = $jsonObject->login;

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

}