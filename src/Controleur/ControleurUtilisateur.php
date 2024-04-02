<?php

namespace App\Trellotrolle\Controleur;


use App\Trellotrolle\Lib\ConnexionUtilisateurInterface;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\UtilisateurServiceInterface;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Classe ControleurUtilisateur
 *
 * Cette classe est responsable de la gestion des utilisateurs dans l'application.
 * Elle contient des méthodes pour afficher les détails d'un utilisateur, créer un nouvel utilisateur,
 * mettre à jour les informations d'un utilisateur, supprimer un utilisateur, se connecter et se déconnecter,
 * et récupérer un compte utilisateur.
 *
 * @package App\Trellotrolle\Controleur
 */
class ControleurUtilisateur extends ControleurGenerique
{

    /**
     * Constructeur de la classe.
     *
     * @param ContainerInterface $container L'interface du conteneur de dépendances.
     * @param UtilisateurServiceInterface $serviceUtilisateur Le service Utilisateur.
     * @param ConnexionUtilisateurInterface $connexionUtilisateurSession L'interface de connexion utilisateur en session.
     * @param ConnexionUtilisateurInterface $connexionUtilisateurJWT L'interface de connexion utilisateur JWT.
     */
    public function __construct(ContainerInterface                             $container,
                                private readonly UtilisateurServiceInterface   $serviceUtilisateur,
                                private readonly ConnexionUtilisateurInterface $connexionUtilisateurSession,
                                private readonly ConnexionUtilisateurInterface $connexionUtilisateurJWT,
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
        return parent::afficherErreur($messageErreur, "utilisateur");
    }

    /**
     * Méthode afficherDetail
     *
     * Cette méthode affiche les détails d'un utilisateur.
     *
     * @return Response La réponse HTTP contenant la vue des détails de l'utilisateur.
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    #[Route(path: '/utilisateur/details', name: 'detail_utilisateur', methods: ["GET"])]
    public function afficherDetail(): Response
    {
        try {
            $utilisateur = $this->serviceUtilisateur->getUtilisateur($this->connexionUtilisateurSession->getIdUtilisateurConnecte());
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->rediriger("connexion");
        }
        return self::afficherTwig("utilisateur/detail.html.twig", ["utilisateur" => $utilisateur, "pagetitle" => "Détail de l'utilisateur {$utilisateur->getLogin()}"]);
    }

    /**
     * Affiche le formulaire de création d'utilisateur.
     *
     * @return Response La réponse HTTP contenant le formulaire de création d'utilisateur.
     *
     * @throws SyntaxError En cas d'erreur de syntaxe dans le template Twig.
     * @throws RuntimeError En cas d'erreur d'exécution dans le template Twig.
     * @throws LoaderError En cas d'erreur de chargement du template Twig.
     */
    #[Route(path: 'utilisateur/inscription', name: 'inscription', methods: ["GET"])]
    public function afficherFormulaireCreation(): Response
    {
        if (self::estConnecte()) {
            return $this->rediriger("mes_tableaux");
        }
        return self::afficherTwig("utilisateur/formulaireCreation.html.twig");
    }

    /**
     * Méthode pour créer un utilisateur depuis un formulaire d'inscription.
     *
     * @return Response La réponse HTTP de la création de l'utilisateur.
     */
    #[Route(path: '/inscription', name: 'inscrire', methods: ["POST"])]
    public function creerDepuisFormulaire(): Response
    {
        if ($this->connexionUtilisateurSession->estConnecte() || $this->connexionUtilisateurJWT->estConnecte()) {
            return $this->rediriger("mes_tableaux");
        }
        try {
            $this->serviceUtilisateur->creerUtilisateur($_POST["login"], $_POST["nom"], $_POST["prenom"], $_POST["email"], $_POST["mdp"], $_POST["mdp2"]);
        } catch (Exception $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->rediriger("inscription");
        }

        MessageFlash::ajouter("success", "L'utilisateur a bien été crée!");
        return $this->rediriger("connexion");
    }

    /**
     * Vérifie si l'utilisateur est connecté.
     *
     * @return bool Retourne true si l'utilisateur est connecté, sinon false.
     */
    private function estConnecte(): bool
    {
        return $this->connexionUtilisateurSession->estConnecte();
    }

    /**
     * Affiche le formulaire de mise à jour d'un utilisateur.
     *
     * @param string $login Le login de l'utilisateur.
     * @return Response La réponse HTTP contenant le formulaire de mise à jour.
     *
     * @throws RuntimeError En cas d'erreur d'exécution du template Twig.
     * @throws SyntaxError En cas d'erreur de syntaxe du template Twig.
     * @throws LoaderError En cas d'erreur de chargement du template Twig.
     */
    #[Route(path: '/utilisateur/{login}/mise-a-jour', name: 'mise_a_jour_utilisateur', methods: ["GET"])]
    public function afficherFormulaireMiseAJour(string $login): Response
    {
        if (!$this->estConnecte()) {
            return $this->rediriger("connexion");
        }
        try {
            $utilisateur = $this->serviceUtilisateur->getUtilisateur($this->connexionUtilisateurSession->getIdUtilisateurConnecte());
        } catch (Exception $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->rediriger("accueil");
        }
        return self::afficherTwig("utilisateur/formulaireMiseAJour.html.twig", ["utilisateur" => $utilisateur]);
    }

    /**
     * Met à jour les informations d'un utilisateur.
     *
     * @return Response La réponse HTTP.
     */
    #[Route(path: '/utilisateur/mise-a-jour', name: 'mettre_a_jour_utilisateur', methods: ["POST"])]
    public function mettreAJour(): Response
    {
        if (!$this->estConnecte()) {
            return $this->rediriger("connexion");
        }
        $login = $this->connexionUtilisateurSession->getIdUtilisateurConnecte();
        $nom = $_POST["nom"] ?? null;
        $prenom = $_POST["prenom"] ?? null;
        $email = $_POST["email"] ?? null;
        $mdpAncien = $_POST["mdpAncien"] ?? null;
        $mdpNouveau = $_POST["mdp"] ?? null;
        $mdpNouveau2 = $_POST["mdp2"] ?? null;

        try {
            $this->serviceUtilisateur->modifierUtilisateur($login, $nom, $prenom, $email, $mdpAncien, $mdpNouveau, $mdpNouveau2);
        } catch (Exception $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->rediriger("mise_a_jour_utilisateur", ["login" => $login]);
        }
        MessageFlash::ajouter("success", "Utilisateur mis à jour");
        return $this->rediriger("accueil");
    }

    /**
     * Méthode supprimer
     *
     * Cette méthode supprime un utilisateur.
     *
     * @param string $login Le login de l'utilisateur à supprimer
     * @return Response La réponse HTTP de la suppression de l'utilisateur
     */
    #[Route(path: '/supprimer', name: 'supprimer', methods: ["GET"])]
    public function supprimer(string $login): Response
    {
        if (!$this->estConnecte()) {
            return $this->rediriger("connexion");
        }
        try {
            $login = $this->connexionUtilisateurSession->getIdUtilisateurConnecte();
            $this->serviceUtilisateur->supprimer($login);
            $this->connexionUtilisateurSession->deconnecter();
            $this->connexionUtilisateurJWT->deconnecter();
        } catch (Exception $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->rediriger("detail_utilisateur");
        }
        MessageFlash::ajouter("success", "Votre compte a bien été supprimé !");
        return $this->rediriger("connexion");
    }

    /**
     * Affiche le formulaire de connexion.
     *
     * @return Response
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    #[Route(path: '/connexion', name: 'connexion', methods: ["GET"])]
    public function afficherFormulaireConnexion(): Response
    {
        if ($this->estConnecte()) {
            return $this->rediriger("mes_tableaux");
        }
        return $this->afficherTwig("utilisateur/formulaireConnexion.html.twig", ["pagetitle" => "Page de connexion"]);
    }

    /**
     * Méthode pour se connecter à l'application.
     *
     * @return Response La réponse HTTP de la connexion.
     */
    #[Route(path: '/connexion', name: 'connecter', methods: ["POST"])]
    public function connecter(): Response
    {
        if ($this->estConnecte()) {
            return $this->rediriger("mes_tableaux");
        }
        try {
            $login = $_POST["login"];
            $mdp = $_POST["mdp"];
            $this->serviceUtilisateur->verifierIdentifiantUtilisateur($login, $mdp);
            $this->connexionUtilisateurSession->connecter($login);
            $this->connexionUtilisateurJWT->connecter($login);
        } catch (Exception $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->rediriger("connexion");
        }
        MessageFlash::ajouter("success", "Connexion effectué !");
        return $this->rediriger("mes_tableaux");
    }

    /**
     * Méthode deconnexion
     *
     * Cette méthode permet de déconnecter un utilisateur.
     * Si l'utilisateur n'est pas connecté, un message d'erreur est ajouté aux messages flash et l'utilisateur est redirigé vers la page de connexion.
     * Sinon, la méthode déconnecte l'utilisateur des deux connexions (session et JWT), ajoute un message de succès aux messages flash et redirige l'utilisateur vers la page d'accueil.
     *
     * @return Response La réponse HTTP de la déconnexion
     */
    #[Route(path: '/deconnexion', name: 'deconnecter', methods: ["GET"])]
    public function deconnecter(): Response
    {
        if (!$this->estConnecte()) {
            MessageFlash::ajouter("danger", "Utilisateur non connecté.");
            return $this->rediriger("connexion");
        }
        $this->connexionUtilisateurSession->deconnecter();
        $this->connexionUtilisateurJWT->deconnecter();
        MessageFlash::ajouter("success", "L'utilisateur a bien été déconnecté.");
        return $this->rediriger("accueil");
    }

    /**
     * Affiche le formulaire de récupération de compte utilisateur.
     *
     * @return Response La réponse HTTP contenant le formulaire de récupération de compte.
     *
     * @throws RuntimeError En cas d'erreur d'exécution de Twig.
     * @throws SyntaxError En cas d'erreur de syntaxe dans le template Twig.
     * @throws LoaderError En cas d'erreur de chargement du template Twig.
     */
    #[Route(path: '/utilisateur/back-up', name: 'recuperation_compte', methods: ["GET"])]
    public function afficherFormulaireRecuperationCompte(): Response
    {
        if ($this->estConnecte()) {
            return $this->rediriger("mes_tableaux");
        }
        return $this->afficherTwig("utilisateur/resetCompte.html.twig", ["pagetitle" => "Récupérer mon compte"]);
    }

    /**
     * Méthode de la classe ControleurUtilisateur qui permet de récupérer un compte utilisateur.
     *
     * @return Response La réponse HTTP générée par la méthode.
     * @throws RuntimeError En cas d'erreur d'exécution de Twig.
     * @throws SyntaxError En cas d'erreur de syntaxe dans le template Twig.
     * @throws LoaderError En cas d'erreur de chargement du template Twig.
     */
    #[Route(path: '/utilisateur/back-up', name: 'recuperer_compte', methods: ["POST"])] // TODO
    public function recupererCompte(): Response
    {
        /*if(ConnexionUtilisateur::estConnecte()) {
            return $this->rediriger("mes_tableaux");
        }
        if (!ControleurUtilisateur::issetAndNotNull(["email"])) {
            MessageFlash::ajouter("warning", "Adresse email manquante");
            return $this->rediriger("connexion");
        }
        $repository = new UtilisateurRepository();
        $utilisateurs = $repository->recupererUtilisateursParEmail($_REQUEST["email"]);
        if(empty($utilisateurs)) {
            MessageFlash::ajouter("warning", "Aucun compte associé à cette adresse email");
            return $this->rediriger("connexion");
        }*/
        return $this->afficherTwig("utilisateur/resultatResetCompte.html.twig");
    }
}