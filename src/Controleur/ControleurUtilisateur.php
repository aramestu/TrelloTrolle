<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Lib\ConnexionUtilisateurInterface;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Lib\MotDePasse;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\HTTP\Cookie;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\ColonneRepository;
use App\Trellotrolle\Modele\Repository\TableauRepository;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\UtilisateurServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ControleurUtilisateur extends ControleurGenerique
{
    public function __construct(ContainerInterface $container,
                                private UtilisateurServiceInterface $serviceUtilisateur,
                                private readonly ConnexionUtilisateurInterface $connexionUtilisateurSession,
                                private readonly ConnexionUtilisateurInterface $connexionUtilisateurJWT,
    ){
        parent::__construct($container);
    }

    public function afficherErreur($messageErreur = "", $controleur = ""): Response
    {
        return parent::afficherErreur($messageErreur, "utilisateur");
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    #[Route(path: '/utilisateur/details', name:'detail_utilisateur', methods:["GET"])]
    public function afficherDetail(): Response
    {
        try{
            $utilisateur = $this->serviceUtilisateur->getUtilisateur($this->connexionUtilisateurSession->getIdUtilisateurConnecte());
        } catch (ServiceException $e){
            MessageFlash::ajouter("error", $e->getMessage());
            return $this->rediriger("connexion");
        }
        return self::afficherTwig("utilisateur/detail.html.twig", ["utilisateur" => $utilisateur, "pagetitle" => "Détail de l'utilisateur {$utilisateur->getLogin()}"]);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    #[Route(path: 'utilisateur/inscription', name:'inscription', methods:["GET"])]
    public function afficherFormulaireCreation(): Response
    {
        if(ConnexionUtilisateur::estConnecte()) {
            return $this->rediriger("mes_tableaux");
        }
        return self::afficherTwig("utilisateur/formulaireCreation.html.twig");
    }

    #[Route(path: '/inscription', name:'inscrire', methods:["POST"])]
    public function creerDepuisFormulaire(): Response
    {
        if($this->connexionUtilisateurSession->estConnecte() || $this->connexionUtilisateurJWT->estConnecte()) {
            return $this->rediriger("mes_tableaux");
        }
        try{
            $this->serviceUtilisateur->creerUtilisateur($_POST["login"], $_POST["nom"], $_POST["prenom"],$_POST["email"] , $_POST["mdp"], $_POST["mdp2"]);
        }catch (\Exception $e){
            MessageFlash::ajouter("error", $e->getMessage());
            return $this->rediriger("inscription");
        }

        MessageFlash::ajouter("success", "L'utilisateur a bien été crée!");
        return $this->rediriger("connexion");
    }

    private function estConnecte(): bool{
        return $this->connexionUtilisateurSession->estConnecte();
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    #[Route(path: '/utilisateur/{login}/mise-a-jour', name:'mise_a_jour_utilisateur', methods:["GET"])]
    public function afficherFormulaireMiseAJour(string $login): Response
    {
        if(! $this->estConnecte()) {
            return $this->rediriger("connexion");
        }
        try{
            $utilisateur = $this->serviceUtilisateur->getUtilisateur($this->connexionUtilisateurSession->getIdUtilisateurConnecte());
        }catch (\Exception $e){
            MessageFlash::ajouter("error", $e->getMessage());
            return $this->rediriger("accueil");
        }
        return self::afficherTwig("utilisateur/formulaireMiseAJour.html.twig", ["utilisateur" => $utilisateur]);
    }

    #[Route(path: '/utilisateur/mise-a-jour', name:'mettre_a_jour_utilisateur', methods:["POST"])]
    public function mettreAJour(): Response
    {
        if(! $this->estConnecte()) {
            return $this->rediriger("connexion");
        }
        try{
            $login = $this->connexionUtilisateurSession->getIdUtilisateurConnecte();
            $this->serviceUtilisateur->modifierUtilisateur($login, $_POST["nom"], $_POST["prenom"], $_POST["mdp"], $_POST["mdp2"]);
        }catch (\Exception $e){
            MessageFlash::ajouter("error", $e->getMessage());
            return $this->rediriger("mise_a_jour_utilisateur");
        }
        MessageFlash::ajouter("success", "Utilisateur mis à jour");
        return $this->rediriger("accueil");
    }

    #[Route(path: '/supprimer', name:'supprimer', methods:["GET"])]
    public function supprimer(string $login): Response
    {
        if(! $this->estConnecte()) {
            return $this->rediriger("connexion");
        }
        try{
            $login = $this->connexionUtilisateurSession->getIdUtilisateurConnecte();
            $this->serviceUtilisateur->supprimer($login);
            $this->connexionUtilisateurSession->deconnecter();
            $this->connexionUtilisateurJWT->deconnecter();
        }catch (\Exception $e){
            MessageFlash::ajouter("error", $e->getMessage());
            return $this->rediriger("detail_utilisateur");
        }
        MessageFlash::ajouter("success", "Votre compte a bien été supprimé !");
        return $this->rediriger("connexion");
    }

    #[Route(path: '/connexion', name:'connexion', methods:["GET"])]
    public function afficherFormulaireConnexion(): Response
    {
        if($this->estConnecte()) {
            return $this->rediriger("mes_tableaux");
        }
        return $this->afficherTwig("utilisateur/formulaireConnexion.html.twig", ["pagetitle" => "Page de connexion"]);
    }

    #[Route(path: '/connexion', name:'connecter', methods:["POST"])]
    public function connecter(): Response
    {
        if($this->estConnecte()) {
            return $this->rediriger("mes_tableaux");
        }
        try{
            $login = $_POST["login"];
            $mdp = $_POST["mdp"];
            $this->serviceUtilisateur->verifierIdentifiantUtilisateur($login, $mdp);
            $this->connexionUtilisateurSession->connecter($login);
            $this->connexionUtilisateurJWT->connecter($login);
        }catch (\Exception $e){
            MessageFlash::ajouter("error", $e->getMessage());
            return $this->rediriger("connexion");
        }
        MessageFlash::ajouter("success", "Connexion effectué !");
        return $this->rediriger("mes_tableaux");
    }

    #[Route(path: '/deconnexion', name:'deconnecter', methods:["GET"])]
    public function deconnecter(): Response
    {
        if (! $this->estConnecte()) {
            MessageFlash::ajouter("danger", "Utilisateur non connecté.");
            return $this->rediriger("connexion");
        }
        $this->connexionUtilisateurSession->deconnecter();
        $this->connexionUtilisateurJWT->deconnecter();
        MessageFlash::ajouter("success", "L'utilisateur a bien été déconnecté.");
        return $this->rediriger("accueil");
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    #[Route(path: '/utilisateur/back-up', name:'recuperation_compte', methods:["GET"])]
    public function afficherFormulaireRecuperationCompte(): Response {
        if($this->estConnecte()) {
            return $this->rediriger("mes_tableaux");
        }
        return $this->afficherTwig("utilisateur/resetCompte.html.twig", ["pagetitle" => "Récupérer mon compte"]);
    }

    /*#[Route(path: '/utilisateur/back-up', name:'recuperer_compte', methods:["POST"])] // TODO
    public function recupererCompte(): Response {
        if(ConnexionUtilisateur::estConnecte()) {
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
        }
        return $this->afficherVue('vueGenerale.php', [
            "pagetitle" => "Récupérer mon compte",
            "cheminVueBody" => "utilisateur/resultatResetCompte.php",
            "utilisateurs" => $utilisateurs
        ]);
    }*/
}