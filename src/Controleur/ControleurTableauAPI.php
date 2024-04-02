<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\ConnexionUtilisateurInterface;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\TableauServiceInterface;
use App\Trellotrolle\Service\UtilisateurServiceInterface;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Classe ControleurTableauAPI
 *
 * Cette classe est responsable de la gestion des requêtes API liées aux tableaux.
 * Elle implémente les fonctionnalités suivantes :
 * - Récupérer toutes les informations d'un tableau
 * - Créer un nouveau tableau
 * - Mettre à jour un tableau existant
 * - Ajouter un membre à un tableau
 * - Supprimer un membre d'un tableau
 * - Récupérer la liste des tableaux d'un utilisateur
 * - Quitter un tableau
 * - Supprimer un tableau
 */
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

    /**
     * Fonction estConnecte
     *
     * Cette fonction retourne un booléen indiquant si l'utilisateur est connecté ou non.
     *
     * @return bool
     */
    private function estConnecte(): bool
    {
        return $this->connexionUtilisateurJWT->estConnecte();
    }

    /**
     * Fonction getToutesInfosTableau
     *
     * Cette fonction récupère toutes les informations d'un tableau en utilisant une requête API.
     *
     * @param string $codeTableau Le code du tableau à récupérer les informations.
     * @return Response La réponse JSON contenant les informations du tableau ou une erreur avec le message d'erreur correspondant.
     */
    #[Route(path: '/api/tableaux/{codeTableau}', name:'api_afficher_tableau', methods:["GET"])]
    public function getToutesInfosTableau(string $codeTableau) : Response { // Fonctionne
        try {
            return new JsonResponse($this->recupererToutesInfosTableau($codeTableau), Response::HTTP_OK);
        } catch (Exception $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }

    /**
     * Fonction creerTableau
     *
     * Cette fonction crée un tableau en utilisant les données fournies dans la requête.
     * Elle vérifie d'abord si l'utilisateur est connecté. Si ce n'est pas le cas, elle renvoie une réponse d'erreur avec un code HTTP 401 (Unauthorized).
     * Ensuite, elle récupère les données JSON de la requête et extrait le nom du tableau.
     * En utilisant le service de tableau, elle crée le tableau en utilisant le nom du tableau et l'identifiant de l'utilisateur connecté.
     * Enfin, elle renvoie une réponse JSON contenant le tableau créé avec un code HTTP 200 (OK).
     * Si une exception est levée pendant le processus, elle renvoie une réponse d'erreur avec le message de l'exception et le code de l'exception.
     *
     * @param Request $request L'objet Request contenant les données de la requête
     * @return Response La réponse JSON contenant le tableau créé ou une réponse d'erreur avec un code HTTP approprié
     */
    #[Route(path: '/api/tableaux', name: 'api_creer_tableau', methods: ["POST"])]
    public function creerTableau(Request $request): Response
    { // Fonctionne
        if (!$this->estConnecte()) {
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

    /**
     * Méthode pour mettre à jour un tableau.
     *
     * @param Request $request L'objet Request contenant les données de la requête.
     * @return Response La réponse JSON contenant les informations mises à jour du tableau.
     */
    #[Route(path: '/api/tableaux', name: 'api_modifier_tableau', methods: ["PATCH"])]
    public function mettreAJour(Request $request): Response
    { // Fonctionne
        if (!$this->estConnecte()) {
            return new JsonResponse(["error" => "Vous devez être connecté!"], Response::HTTP_UNAUTHORIZED);
        }
        try {
            $jsonObject = json_decode($request->getContent(), flags: JSON_THROW_ON_ERROR);
            $idTableau = $jsonObject->idTableau;
            $nomTableau = $jsonObject->nomTableau;
            $tableau = $this->tableauService->mettreAJourTableau($idTableau, $this->connexionUtilisateurJWT->getIdUtilisateurConnecte(), $nomTableau);
            return new JsonResponse($this->recupererToutesInfosTableau($tableau->getCodeTableau()), Response::HTTP_OK);
        } catch (Exception $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }


    /**
     * Cette fonction privée récupère toutes les informations d'un tableau en fonction de son code.
     *
     * @param string|null $codeTableau Le code du tableau
     * @return array Un tableau contenant les informations du tableau, l'association des colonnes et des cartes, et les informations d'affectation
     * @throws ServiceException
     */
    private function recupererToutesInfosTableau(?string $codeTableau): array
    {
        $tableau = $this->tableauService->getByCodeTableau($codeTableau);
        $associationColonneCarte = $this->tableauService->recupererColonnesEtCartesDuTableau($tableau->getIdTableau());
        $informationsAffectation = $this->tableauService->informationsAffectationsCartes($tableau->getIdTableau());

        return ["tableau" => $tableau, "associationColonneCarte" => $associationColonneCarte, "informationsAffectation" => $informationsAffectation];
    }

    /**
     * Méthode pour ajouter un membre à un tableau via une requête GET.
     *
     * @param int $idTableau L'identifiant du tableau.
     * @param string $login Le login du membre à ajouter.
     * @return Response La réponse JSON contenant les informations du tableau mis à jour.
     */
    #[Route(path: '/api/tableaux/{idTableau}/ajouter-membre/{login}', name: 'api_ajouter_membre_tableau', methods: ["GET"])]
    public function ajouterMembre(int $idTableau, string $login): Response
    { // Fonctionne
        if (!$this->estConnecte()) {
            return new JsonResponse(["error" => "Vous devez être connecté!"], Response::HTTP_UNAUTHORIZED);
        }
        try {
            $tableau = $this->tableauService->ajouterMembre((int) $idTableau, $this->connexionUtilisateurJWT->getIdUtilisateurConnecte() , $login);
            return new JsonResponse($this->recupererToutesInfosTableau($tableau->getCodeTableau()), Response::HTTP_OK);
        } catch (Exception $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }

    /**
     * Supprime un membre d'un tableau.
     *
     * @param int $idTableau L'identifiant du tableau.
     * @param string $login Le login du membre à supprimer.
     * @return Response La réponse JSON contenant les informations mises à jour du tableau.
     */
    #[Route(path: '/api/tableaux/{idTableau}/supprimer-membre/{login}', name: 'api_supprimer_membre_tableau', methods: ["DELETE"])]
    public function supprimerMembre(int $idTableau, string $login): Response
    { // Fonctionne
        if (!$this->estConnecte()) {
            return new JsonResponse(["error" => "Vous devez être connecté!"], Response::HTTP_UNAUTHORIZED);
        }
        try {
            $tableau = $this->tableauService->supprimerMembre((int) $idTableau, $this->connexionUtilisateurJWT->getIdUtilisateurConnecte() , $login);
            return new JsonResponse($this->recupererToutesInfosTableau($tableau->getCodeTableau()), Response::HTTP_OK);
        } catch (Exception $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }

    /**
     * Fonction getListeMesTableau
     *
     * Cette fonction permet de récupérer la liste des tableaux d'un utilisateur connecté.
     *
     * @return JsonResponse La liste des tableaux de l'utilisateur connecté au format JSON.
     * @throws Exception Si une erreur se produit lors de la récupération des tableaux.
     */
    #[Route(path: '/api/tableaux', name: 'api_liste_mes_tableau', methods: ["GET"])]
    public function getListeMesTableau(): JsonResponse
    { // Fonctionne
        if (!$this->estConnecte()) {
            return new JsonResponse(["error" => "Vous devez être connecté!"], Response::HTTP_UNAUTHORIZED);
        }
        try {
            $tableaux = $this->utilisateurService->recupererTableauxOuUtilisateurEstMembre($this->connexionUtilisateurJWT->getIdUtilisateurConnecte());
            return new JsonResponse($tableaux, Response::HTTP_OK);
        } catch (Exception $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }

    /**
     * Méthode pour quitter un tableau.
     *
     * @param int $idTableau L'identifiant du tableau.
     * @return JsonResponse La réponse JSON contenant les tableaux.
     * @throws Exception En cas d'erreur lors de l'exécution de la méthode.
     */
    #[Route(path: '/api/tableaux/{idTableau}', name: 'api_quitter_tableau', methods: ["PATCH"])]
    public function quitterTableau(int $idTableau): JsonResponse
    {
        if (!$this->estConnecte()) {
            return new JsonResponse(["error" => "Vous devez être connecté!"], Response::HTTP_UNAUTHORIZED);
        }
        try {
            $tableaux = $this->tableauService->quitterTableau($this->connexionUtilisateurJWT->getIdUtilisateurConnecte(), (int) $idTableau);
            return new JsonResponse($tableaux, Response::HTTP_OK);
        } catch (Exception $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }

    /**
     * Supprime un tableau.
     *
     * @param int $idTableau L'identifiant du tableau à supprimer.
     * @return JsonResponse La réponse JSON avec le statut HTTP approprié.
     */
    #[Route(path: '/api/tableaux/{idTableau}', name: 'api_supprimer_tableau', methods: ["DELETE"])]
    public function supprimerTableau(int $idTableau): JsonResponse
    {
        if (!$this->estConnecte()) {
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