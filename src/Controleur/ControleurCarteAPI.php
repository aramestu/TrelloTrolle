<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\ConnexionUtilisateurInterface;
use App\Trellotrolle\Service\CarteServiceInterface;
use App\Trellotrolle\Service\ColonneServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use App\Trellotrolle\Service\TableauServiceInterface;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Classe ControleurCarteAPI
 *
 * Cette classe est responsable de la gestion des requêtes API liées aux cartes.
 * Elle implémente les fonctionnalités de suppression de carte, création de carte et mise à jour de carte.
 *
 * @package App\Trellotrolle\Controleur
 */
class ControleurCarteAPI extends ControleurGenerique
{
    /**
     * Constructeur de la classe.
     *
     * @param ContainerInterface $container L'interface du conteneur de dépendances.
     * @param CarteServiceInterface $carteService L'interface du service de carte.
     * @param ConnexionUtilisateurInterface $connexionUtilisateurJWT L'interface de connexion de l'utilisateur JWT.
     */
    public function __construct(
        ContainerInterface                             $container,
        private readonly CarteServiceInterface         $carteService,
        private readonly TableauServiceInterface       $tableauService,
        private readonly ColonneServiceInterface       $colonneService,
        private readonly ConnexionUtilisateurInterface $connexionUtilisateurJWT
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

    #[Route(path: '/api/cartes/{idCarte}', name: 'api_details_carte', methods: ["GET"])]
    public function detailsCarte(string $idCarte): Response
    { // Fonctionne
        try {
            $carte = $this->carteService->getCarte((int)$idCarte);
            $colonne = $this->colonneService->getColonne($carte->getColonne()->getIdColonne());
            $tableau = $this->tableauService->getByIdTableau($colonne->getTableau()->getIdTableau());

            return new JsonResponse(["carte" => $carte, "tableau" => $tableau], Response::HTTP_OK); // True si ça a été supprimé
        } catch (Exception $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }

    /**
     * Méthode supprimerCarte
     *
     * Cette méthode supprime une carte en utilisant son identifiant.
     *
     * @param string $idCarte L'identifiant de la carte à supprimer.
     * @return Response La réponse JSON indiquant si la carte a été supprimée avec succès ou une erreur avec un message d'erreur.
     */
    #[Route(path: '/api/cartes/{idCarte}', name: 'api_supprimer_carte', methods: ["DELETE"])]
    public function supprimerCarte(string $idCarte): Response
    { // Fonctionne
        if (!$this->estConnecte()) {
            return new JsonResponse(["error" => "Vous devez "], Response::HTTP_UNAUTHORIZED);
        }
        try {
            $this->carteService->supprimerCarte((int)$idCarte, $this->connexionUtilisateurJWT->getIdUtilisateurConnecte());
            return new JsonResponse(true, Response::HTTP_OK); // True si ça a été supprimé
        } catch (Exception $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }

    /**
     * Fonction creerCarte
     *
     * Cette fonction crée une nouvelle carte en utilisant les données fournies dans la requête.
     *
     * @param Request $request L'objet Request contenant les données de la requête.
     * @return Response La réponse JSON contenant la carte créée ou une erreur avec un message d'erreur.
     */
    #[Route(path: '/api/cartes', name: 'api_creer_carte', methods: ["POST"])]
    public function creerCarte(Request $request): Response
    {
        if (!$this->estConnecte()) {
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

    /**
     * Méthode mettreAJour
     *
     * Cette méthode met à jour une carte en utilisant les données fournies dans la requête.
     *
     * @param Request $request L'objet Request contenant les données de la requête.
     * @return Response La réponse JSON contenant la carte mise à jour ou une erreur avec un message d'erreur.
     */
    #[Route(path: '/api/cartes', name: 'api_modifier_carte', methods: ["PATCH"])]
    public function mettreAJour(Request $request): Response
    { // Fonctionne
        if (!$this->estConnecte()) {
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

            $carte = $this->carteService->mettreAJourCarte($idCarte, $idColonne, $titreCarte, $descriptifCarte, $couleurCarte, $this->connexionUtilisateurJWT->getIdUtilisateurConnecte(), $affectationsCarte);
            return new JsonResponse($carte, Response::HTTP_OK); // Renvoie la colonne avec la carte crée en plus
        } catch (Exception $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }

}