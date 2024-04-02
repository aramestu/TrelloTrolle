<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\ConnexionUtilisateurInterface;
use App\Trellotrolle\Service\ColonneServiceInterface;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Classe ControleurColonneAPI
 *
 * Cette classe est responsable de la gestion des colonnes via une API.
 * Elle hérite de la classe ControleurGenerique.
 *
 * @package App\Trellotrolle\Controleur
 */
class ControleurColonneAPI extends ControleurGenerique
{

    /**
     * Constructeur de la classe.
     *
     * @param ContainerInterface $container L'interface du conteneur de dépendances.
     * @param ColonneServiceInterface $colonneService L'interface du service de colonne.
     * @param ConnexionUtilisateurInterface $connexionUtilisateurJWT L'interface de connexion de l'utilisateur JWT.
     * @return void
     */
    public function __construct(
        ContainerInterface                             $container,
        private readonly ColonneServiceInterface       $colonneService,
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
     * Méthode supprimerColonne
     *
     * Cette méthode supprime une colonne en utilisant l'identifiant de la colonne.
     *
     * @param int $idColonne L'identifiant de la colonne à supprimer.
     * @return Response La réponse JSON indiquant si la colonne a été supprimée avec succès.
     */
    #[Route(path: '/api/colonnes/{idColonne}', name: 'api_supprimer_colonne', methods: ["DELETE"])]
    public function supprimerColonne(int $idColonne): Response
    {
        if (!$this->estConnecte()) {
            return new JsonResponse(["error" => "Vous devez être connecté"], Response::HTTP_UNAUTHORIZED);
        }
        try {
            $this->colonneService->supprimerColonne($idColonne, $this->connexionUtilisateurJWT->getIdUtilisateurConnecte());
            return new JsonResponse(true, Response::HTTP_NO_CONTENT); // True si ça a été supprimé
        } catch (Exception $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }

    /**
     * Méthode creerColonne
     *
     * Cette méthode crée une colonne en utilisant les données fournies dans la requête.
     *
     * @param Request $request La requête contenant les données nécessaires pour créer la colonne.
     * @return Response La réponse JSON contenant la colonne créée ou une erreur en cas d'échec.
     */
    #[Route(path: '/api/colonnes', name: 'api_creer_colonne', methods: ["POST"])]
    public function creerColonne(Request $request): Response
    {
        if (!$this->estConnecte()) {
            return new JsonResponse(["error" => "Vous devez être connecté"], Response::HTTP_UNAUTHORIZED);
        }
        try {
            $jsonObject = json_decode($request->getContent(), flags: JSON_THROW_ON_ERROR);
            $idTableau = $jsonObject->idTableau;
            $nomColonne = $jsonObject->nomColonne;

            $idColonne = $this->colonneService->creerColonne($idTableau, $nomColonne, $this->connexionUtilisateurJWT->getIdUtilisateurConnecte());
            $colonne = $this->colonneService->getColonne($idColonne);
            return new JsonResponse($colonne, Response::HTTP_OK);
        } catch (Exception $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }

    /**
     * Méthode mettreAJour
     *
     * Cette méthode met à jour une colonne en utilisant les données fournies dans la requête.
     *
     * @param Request $request La requête contenant les données nécessaires pour mettre à jour la colonne.
     * @return Response La réponse JSON contenant la colonne mise à jour ou une erreur en cas d'échec.
     */
    #[Route(path: '/api/colonnes', name: 'api_modifier_colonne', methods: ["PATCH"])]
    public function mettreAJour(Request $request): Response
    {
        if (!$this->estConnecte()) {
            return new JsonResponse(["error" => "Vous devez être connecté"], Response::HTTP_UNAUTHORIZED);
        }
        try {
            $jsonObject = json_decode($request->getContent(), flags: JSON_THROW_ON_ERROR);
            $idColonne = $jsonObject->idColonne;
            $nomColonne = $jsonObject->nomColonne;

            $colonne = $this->colonneService->mettreAJour($idColonne, $nomColonne, $this->connexionUtilisateurJWT->getIdUtilisateurConnecte());
            return new JsonResponse($colonne, Response::HTTP_OK);
        } catch (Exception $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }
}