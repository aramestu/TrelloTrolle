<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\ConnexionUtilisateurInterface;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Service\CarteServiceInterface;
use App\Trellotrolle\Service\ColonneServiceInterface;
use App\Trellotrolle\Service\TableauServiceInterface;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Classe ControleurCarte
 *
 * Cette classe est responsable de la gestion des cartes dans l'application Trellotrolle.
 * Elle implémente les fonctionnalités liées aux cartes telles que la création, la mise à jour et la suppression d'une carte.
 * Elle utilise les services CarteServiceInterface, ColonneServiceInterface et TableauServiceInterface pour effectuer les opérations sur les cartes.
 * Elle utilise également l'interface ConnexionUtilisateurInterface pour vérifier si un utilisateur est connecté.
 *
 * @package App\Trellotrolle\Controleur
 */
class ControleurCarte extends ControleurGenerique
{

    /**
     * Constructeur de la classe.
     *
     * @param ContainerInterface $container L'interface du conteneur.
     * @param CarteServiceInterface $carteService L'interface du service de carte.
     * @param ColonneServiceInterface $colonneService L'interface du service de colonne.
     * @param TableauServiceInterface $tableauService L'interface du service de tableau.
     * @param ConnexionUtilisateurInterface $connexionUtilisateurSession L'interface de la session de connexion utilisateur.
     */
    public function __construct(ContainerInterface                             $container,
                                private readonly CarteServiceInterface         $carteService,
                                private readonly ColonneServiceInterface       $colonneService,
                                private readonly TableauServiceInterface       $tableauService,
                                private readonly ConnexionUtilisateurInterface $connexionUtilisateurSession
    )
    {
        parent::__construct($container);
    }

    /**
     * Méthode afficherErreur
     *
     * Cette méthode affiche une erreur en utilisant la méthode afficherErreur de la classe parente.
     *
     * @param string $messageErreur Le message d'erreur à afficher.
     * @param string $controleur Le nom du contrôleur.
     * @return Response La réponse HTTP contenant le corps de la vue d'erreur rendue.
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function afficherErreur(string $messageErreur = "", string $controleur = ""): Response
    {
        return parent::afficherErreur($messageErreur, "carte");
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
        return $this->connexionUtilisateurSession->estConnecte();
    }

    /**
     * Méthode supprimerCarte
     *
     * Cette méthode supprime une carte en fonction de son identifiant.
     *
     * @param string $idCarte L'identifiant de la carte à supprimer
     * @return RedirectResponse La réponse de redirection vers une autre page
     */
    #[Route(path: '/carte/{idCarte}/suppression', name: 'supprimer_carte', methods: ["GET"])]
    public function supprimerCarte(string $idCarte): RedirectResponse
    {
        if (!$this->estConnecte()) {
            return $this->rediriger("connexion");
        }
        try {
            $tableau = $this->carteService->supprimerCarte($idCarte, $this->connexionUtilisateurSession->getIdUtilisateurConnecte());
        } catch (Exception $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->rediriger("mes_tableaux");
        }

        MessageFlash::ajouter("success", "La carte a bien été supprimé!");
        return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    /**
     * Affiche le formulaire de création d'une carte pour une colonne donnée.
     *
     * @param int $idColonne L'identifiant de la colonne
     * @return Response La réponse HTTP contenant le formulaire de création de carte
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    #[Route(path: '{idColonne}/carte/creation', name: 'creation_carte', methods: ["GET"])]
    public function afficherFormulaireCreationCarte(int $idColonne): Response
    {
        if (!$this->estConnecte()) {
            return $this->rediriger("connexion");
        }
        try {
            $colonne = $this->colonneService->getColonne($idColonne);
            $idTableau = $colonne->getTableau()->getIdTableau();
            $tableau = $this->tableauService->getByIdTableau($idTableau);
            $this->tableauService->verifierParticipant($this->connexionUtilisateurSession->getIdUtilisateurConnecte(), $idTableau);
            $colonnes = $this->colonneService->recupererColonnesTableau($idTableau);
        } catch (Exception $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->rediriger("mes_tableaux");
        }
        return $this->afficherTwig("carte/formulaireCreationCarte.html.twig", ["colonne" => $colonne, "colonnes" => $colonnes, "tableau" => $tableau, "pagetitle" => "Création carte"]);
    }

    /**
     * Méthode pour créer une carte.
     *
     * @return Response
     */
    #[Route(path: '/carte/creation', name: 'creer_carte', methods: ["POST"])]
    public function creerCarte(): Response
    {
        if (!$this->estConnecte()) {
            return $this->rediriger("connexion");
        }
        $idColonne = $_POST["idColonne"] ?? null;
        $titreCarte = $_POST["titreCarte"] ?? null;
        $descriptifCarte = $_POST["descriptifCarte"] ?? null;
        $couleurCarte = $_POST["couleurCarte"] ?? null;
        $affectationsCarte = $_POST["affectationsCarte"] ?? null;
        try {
            $this->carteService->creerCarte($idColonne, $titreCarte, $descriptifCarte, $couleurCarte, $this->connexionUtilisateurSession->getIdUtilisateurConnecte(), $affectationsCarte);
            $colonne = $this->colonneService->getColonne($idColonne);
            $tableau = $this->tableauService->getByIdTableau($colonne->getTableau()->getIdTableau());
        } catch (Exception $e) {
            MessageFlash::ajouter("warning", $e->getMessage());

            if (isset($_POST["idColonne"])) {
                return $this->rediriger("creation_carte", ["idColonne" => $_POST["idColonne"]]);
            }
            return $this->rediriger("mes_tableaux");
        }
        return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    /**
     * Affiche le formulaire de mise à jour d'une carte.
     *
     * @param string $idCarte L'identifiant de la carte.
     * @return Response La réponse HTTP.
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    #[Route(path: '/carte/{idCarte}/mise-a-jour', name: 'mise_a_jour_carte', methods: ["GET"])]
    public function afficherFormulaireMiseAJourCarte(string $idCarte): Response
    {
        if (!$this->estConnecte()) {
            return $this->rediriger("connexion");
        }
        try {
            $carte = $this->carteService->getCarte($idCarte);
            $colonne = $this->colonneService->getColonne($carte->getColonne()->getIdColonne());
            $idTableau = $colonne->getTableau()->getIdTableau();
            $tableau = $this->tableauService->getByIdTableau($idTableau);
            $this->tableauService->verifierParticipant($this->connexionUtilisateurSession->getIdUtilisateurConnecte(), $idTableau);
            $colonnes = $this->colonneService->recupererColonnesTableau($idTableau);
        } catch (Exception $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->rediriger("mes_tableaux");
        }
        return $this->afficherTwig("carte/formulaireMiseAJourCarte.html.twig", ["carte" => $carte, "tableau" => $tableau, "colonne" => $colonne, "colonnes" => $colonnes, "pagetitle" => "Modification d'une carte"]);
    }

    /**
     * Cette méthode met à jour une carte dans le système.
     *
     * @param string $idCarte L'identifiant de la carte à mettre à jour.
     * @return Response La réponse HTTP de la mise à jour de la carte.
     */
    #[Route(path: '/carte/{idCarte}/mettre_a_jour', name: 'mettre_a_jour_carte', methods: ["POST"])]
    public function mettreAJourCarte(string $idCarte): Response
    {
        if (!$this->estConnecte()) {
            return $this->rediriger("connexion");
        }
        try {
            $carte = $this->carteService->mettreAJourCarte($idCarte, $_POST["idColonne"], $_POST["titreCarte"], $_POST["descriptifCarte"], $_POST["couleurCarte"], $this->connexionUtilisateurSession->getIdUtilisateurConnecte(), $_POST["affectationsCarte"]);
            $colonne = $carte->getColonne();
            $idTableau = $colonne->getTableau()->getIdTableau();
            $tableau = $this->tableauService->getByIdTableau($idTableau);
        } catch (Exception $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->rediriger("mise_a_jour_carte", ["idCarte" => $idCarte]);
        }
        return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }
}