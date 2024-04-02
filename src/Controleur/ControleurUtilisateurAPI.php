<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\ConnexionUtilisateurInterface;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\UtilisateurServiceInterface;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Classe ControleurUtilisateurAPI
 *
 * Cette classe est responsable de la gestion des requêtes API liées aux utilisateurs.
 * Elle hérite de la classe ControleurGenerique.
 *
 * @package App\Trellotrolle\Controleur
 */
class ControleurUtilisateurAPI extends ControleurGenerique
{

    /**
     * Constructeur de la classe.
     *
     * @param ContainerInterface $container L'interface du conteneur de dépendances.
     * @param UtilisateurServiceInterface $utilisateurService L'interface du service Utilisateur.
     * @param ConnexionUtilisateurInterface $connexionUtilisateurJWT L'interface de connexion de l'utilisateur JWT.
     * @return void
     */
    public function __construct(
        ContainerInterface                             $container,
        private readonly UtilisateurServiceInterface   $utilisateurService,
        private readonly ConnexionUtilisateurInterface $connexionUtilisateurJWT
    )
    {
        parent::__construct($container);
    }

    /**
     * Méthode estConnecte()
     *
     * Cette méthode retourne un booléen indiquant si l'utilisateur est connecté ou non.
     *
     * @return bool
     */
    private function estConnecte(): bool
    {
        return $this->connexionUtilisateurJWT->estConnecte();
    }

    /**
     * Méthode afficherDetail
     *
     * Cette méthode affiche les détails d'un utilisateur en utilisant son login.
     *
     * @param string $login Le login de l'utilisateur.
     * @return Response La réponse JSON contenant les détails de l'utilisateur.
     */
    #[Route(path: '/api/utilisateurs/{login}', name: 'api_detail_utilisateur', methods: ["GET"])]
    public function afficherDetail(string $login): Response
    { // Fonctionne
        if (!$this->estConnecte()) {
            return new JsonResponse(["error" => "Vous devez être connecté!"], Response::HTTP_UNAUTHORIZED);
        }
        try {
            $loginUserConnecte = $this->connexionUtilisateurJWT->getIdUtilisateurConnecte();
            $this->utilisateurService->verifierLoginConnecteEstLoginRenseigne($loginUserConnecte, $login);
            $user = $this->utilisateurService->getUtilisateur($loginUserConnecte);
            return new JsonResponse($user);
        } catch (Exception $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }

    /**
     * Méthode connecter
     *
     * Cette méthode permet de connecter un utilisateur en utilisant son identifiant.
     *
     * @param Request $request L'objet Request contenant les données de la requête.
     * @return Response La réponse JSON.
     * @throws \JsonException
     */
    #[Route(path: '/api/auth', name: 'api_auth', methods: ["POST"])]
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
        } catch (JsonException) {
            return new JsonResponse(
                ["error" => "Corps de la requête mal formé"],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Méthode deconnecter
     *
     * Cette méthode permet de déconnecter l'utilisateur.
     *
     * @return Response La réponse JSON.
     */
    #[Route(path: '/api/deconnexion', name: 'api_deconnexion', methods: ["POST"])]
    public function deconnecter(): Response
    { // Fonctionne
        try {
            $this->connexionUtilisateurJWT->deconnecter(); // Appel de la méthode de déconnexion
            return new JsonResponse();
        } catch (ServiceException $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        } catch (Exception) {
            return new JsonResponse(
                ["error" => "Une erreur est survenue lors de la déconnexion"],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Méthode mettreAJour
     *
     * Cette méthode permet de mettre à jour les informations d'un utilisateur.
     *
     * @param Request $request L'objet Request contenant les données de la requête.
     * @return Response La réponse JSON contenant les informations de l'utilisateur mis à jour.
     */
    #[Route(path: '/api/utilisateurs', name: 'api_modifier_utilisateur', methods: ["PATCH"])]
    public function mettreAJour(Request $request): Response
    { // Fonctionne
        if (!$this->estConnecte()) {
            return new JsonResponse(["error" => "Vous devez être connecté!"], Response::HTTP_UNAUTHORIZED);
        }
        try {
            $jsonObject = json_decode($request->getContent(), flags: JSON_THROW_ON_ERROR);
            $loginUserConnecte = $this->connexionUtilisateurJWT->getIdUtilisateurConnecte();
            $login = $jsonObject->login;
            $nom = $jsonObject->nom;
            $prenom = $jsonObject->prenom;
            $mdp = $jsonObject->mdp;
            $mdp2 = $jsonObject->mdp2;
            // Je vérifie que les 2 login sont identiques
            $this->utilisateurService->verifierLoginConnecteEstLoginRenseigne($loginUserConnecte, $login);
            $utilisateur = $this->utilisateurService->modifierUtilisateur($loginUserConnecte, $nom, $prenom, $mdp, $mdp2);
            return new JsonResponse($utilisateur);
        } catch (Exception $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }

    /**
     * Méthode supprimer
     *
     * Cette méthode permet de supprimer un utilisateur.
     *
     * @param Request $request L'objet Request contenant les données de la requête.
     * @return Response La réponse JSON.
     */
    #[Route(path: '/api/utilisateurs', name: 'api_supprimer_utilisateur', methods: ["DELETE"])]
    public function supprimer(Request $request): Response
    { // Fonctionne
        if (!$this->estConnecte()) {
            return new JsonResponse(["error" => "Vous devez être connecté!"], Response::HTTP_UNAUTHORIZED);
        }
        try {
            $jsonObject = json_decode($request->getContent(), flags: JSON_THROW_ON_ERROR);
            $loginUserConnecte = $this->connexionUtilisateurJWT->getIdUtilisateurConnecte();
            $login = $jsonObject->login;
            $this->utilisateurService->verifierLoginConnecteEstLoginRenseigne($loginUserConnecte, $login);
            $this->utilisateurService->supprimer($loginUserConnecte);
            return new JsonResponse('', Response::HTTP_NO_CONTENT);
        } catch (Exception $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }
}