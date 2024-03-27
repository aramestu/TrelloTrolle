<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\ConnexionUtilisateur;
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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ControleurUtilisateur extends ControleurGenerique
{

    public function afficherErreur($messageErreur = "", $controleur = ""): Response
    {
        return parent::afficherErreur($messageErreur, "utilisateur");
    }

    #[Route(path: '/utilisateur/details', name:'detail_utilisateur', methods:["GET"])]
    public function afficherDetail(): Response
    {
        if(!ConnexionUtilisateur::estConnecte()) {
            return self::rediriger("connexion");
        }
        $utilisateur = (new UtilisateurRepository())->recupererParClePrimaire(ConnexionUtilisateur::getLoginUtilisateurConnecte());
        return $this->afficherVue('vueGenerale.php', [
            "utilisateur" => $utilisateur,
            "pagetitle" => "Détail de l'utilisateur {$utilisateur->getLogin()}",
            "cheminVueBody" => "utilisateur/detail.php"
        ]);
    }

    #[Route(path: 'utilisateur/inscription', name:'inscription', methods:["GET"])]
    public function afficherFormulaireCreation(): Response
    {
        if(ConnexionUtilisateur::estConnecte()) {
            return $this->rediriger("mes_tableaux");
        }
        return $this->afficherVue('vueGenerale.php', [
            "pagetitle" => "Création d'un utilisateur",
            "cheminVueBody" => "utilisateur/formulaireCreation.php"
        ]);
    }

    #[Route(path: 'utilisateur/inscription', name:'inscrire', methods:["POST"])]
    public function creerDepuisFormulaire(): Response
    {
        if(ConnexionUtilisateur::estConnecte()) {
            return $this->rediriger("mes_tableaux");
        }
        if (ControleurUtilisateur::issetAndNotNull(["login", "prenom", "nom", "mdp", "mdp2", "email"])) {
            if ($_REQUEST["mdp"] !== $_REQUEST["mdp2"]) {
                MessageFlash::ajouter("warning", "Mots de passe distincts.");
                return $this->rediriger("inscription");
            }

            if (!filter_var($_REQUEST["email"], FILTER_VALIDATE_EMAIL)) {
                MessageFlash::ajouter("warning", "Email non valide");
                return $this->rediriger("inscription");
            }

            $utilisateurRepository = new UtilisateurRepository();

            $checkUtilisateur = $utilisateurRepository->recupererParClePrimaire($_REQUEST["login"]);
            if($checkUtilisateur) {
                MessageFlash::ajouter("warning", "Le login est déjà pris.");
                return $this->rediriger("inscription");
            }

            $tableauRepository = new TableauRepository();
            $colonneRepository = new ColonneRepository();
            $carteRepository = new CarteRepository();

            $mdpHache = MotDePasse::hacher($_REQUEST["mdp"]);
            $idTableau = $tableauRepository->getNextIdTableau();
            $codeTableau = hash("sha256", $_REQUEST["login"].$idTableau);
            $tableauInitial = "Mon tableau";

            $idColonne1 = $colonneRepository->getNextIdColonne();
            $colonne1 = "TODO";

            $colonne2 = "DOING";
            $idColonne2 = $idColonne1 + 1;

            $colonne3 = "DONE";
            $idColonne3 = $idColonne1 + 2;

            $carteInitiale = "Exemple";
            $descriptifInitial = "Exemple de carte";
            $idCarte1 = $carteRepository->getNextIdCarte();
            $idCarte2 = $idCarte1 + 1;
            $idCarte3 = $idCarte1 + 2;

            $tableau = new Tableau(
                new Utilisateur(
                    $_REQUEST["login"],
                    $_REQUEST["nom"],
                    $_REQUEST["prenom"],
                    $_REQUEST["email"],
                    $mdpHache,
                    $_REQUEST["mdp"],
                ),
                $idTableau,
                $codeTableau,
                $tableauInitial,
                [],
            );

            $carte1 = new Carte(
                new Colonne(
                    $tableau,
                    $idColonne1,
                    $colonne1,
                ),
                $idCarte1,
                $carteInitiale,
                $descriptifInitial,
                "#FFFFFF",
                []
            );

            $carte2 = new Carte(
                new Colonne(
                    $tableau,
                    $idColonne2,
                    $colonne2,
                ),
                $idCarte2,
                $carteInitiale,
                $descriptifInitial,
                "#FFFFFF",
                []
            );

            $carte3 = new Carte(
                new Colonne(
                    $tableau,
                    $idColonne3,
                    $colonne3,
                ),
                $idCarte3,
                $carteInitiale,
                $descriptifInitial,
                "#FFFFFF",
                []
            );

            $succesSauvegarde = $carteRepository->ajouter($carte1) && $carteRepository->ajouter($carte2) && $carteRepository->ajouter($carte3);
            if ($succesSauvegarde) {
                Cookie::enregistrer("login", $_REQUEST["login"]);
                Cookie::enregistrer("mdp", $_REQUEST["mdp"]);
                MessageFlash::ajouter("success", "L'utilisateur a bien été créé !");
                return $this->rediriger("connexion");
            }
            else {
                MessageFlash::ajouter("warning", "Une erreur est survenue lors de la création de l'utilisateur.");
                return $this->rediriger("inscription");
            }
        } else {
            MessageFlash::ajouter("danger", "Login, nom, prenom, email ou mot de passe manquant.");
            return $this->rediriger("inscription");
        }
    }

    #[Route(path: '/utilisateur/mise-a-jour', name:'mise_a_jour_utilisateur', methods:["GET"])]
    public function afficherFormulaireMiseAJour(): Response
    {
        if(!ConnexionUtilisateur::estConnecte()) {
            $this->rediriger("connexion");
        }
        $login = ConnexionUtilisateur::getLoginUtilisateurConnecte();
        $repository = new UtilisateurRepository();
        $utilisateur = $repository->recupererParClePrimaire($login);
        return $this->afficherVue('vueGenerale.php', [
            "pagetitle" => "Mise à jour du profil",
            "cheminVueBody" => "utilisateur/formulaireMiseAJour.php",
            "utilisateur" => $utilisateur,
        ]);
    }

    #[Route(path: '/utilisateur/mise-a-jour', name:'mettre_a_jour_utilisateur', methods:["POST"])]
    public function mettreAJour(): Response
    {
        if(!ConnexionUtilisateur::estConnecte()) {
            return $this->rediriger("connexion");
        }
        if (ControleurUtilisateur::issetAndNotNull(["login", "prenom", "nom", "mdp", "mdp2", "email"])) {
            $login = $_REQUEST['login'];
            $repository = new UtilisateurRepository();

            /**
             * @var Utilisateur $utilisateur
             */
            $utilisateur = $repository->recupererParClePrimaire($login);

            if(!$utilisateur) {
                MessageFlash::ajouter("danger", "L'utilisateur n'existe pas");
                return $this->rediriger("mise_a_jour_utilisateur");
            }

            if (!filter_var($_REQUEST["email"], FILTER_VALIDATE_EMAIL)) {
                MessageFlash::ajouter("warning", "Email non valide");
                return $this->rediriger("mise_a_jour_utilisateur");
            }

            if (!(MotDePasse::verifier($_REQUEST["mdpAncien"], $utilisateur->getMdpHache()))) {
                MessageFlash::ajouter("warning", "Ancien mot de passe erroné.");
                return $this->rediriger("mise_a_jour_utilisateur");
            }

            if ($_REQUEST["mdp"] !== $_REQUEST["mdp2"]) {
                MessageFlash::ajouter("warning", "Mots de passe distincts.");
                return $this->rediriger("mise_a_jour_utilisateur");
            }

            $utilisateur->setNom($_REQUEST["nom"]);
            $utilisateur->setPrenom($_REQUEST["prenom"]);
            $utilisateur->setEmail($_REQUEST["email"]);
            $utilisateur->setMdpHache(MotDePasse::hacher($_REQUEST["mdp"]));
            $utilisateur->setMdp($_REQUEST["mdp"]);

            $repository->mettreAJour($utilisateur);

            $carteRepository = new CarteRepository();
            $cartes = $carteRepository->recupererCartesUtilisateur($login);
            foreach ($cartes as $carte) {
                $participants = $carte->getAffectationsCarte();
                $participants = array_filter($participants, function ($u) use ($login) {return $u->getLogin() !== $login;});
                $participants[] = $utilisateur;
                $carte->setAffectationsCarte($participants);
                $carteRepository->mettreAJour($carte);
            }

            $tableauRepository = new TableauRepository();
            $tableaux = $tableauRepository->recupererTableauxParticipeUtilisateur($login);
            foreach ($tableaux as $tableau) {
                $participants = $tableau->getParticipants();
                $participants = array_filter($participants, function ($u) use ($login) {return $u->getLogin() !== $login;});
                $participants[] = $utilisateur;
                $tableau->setParticipants($participants);
                $tableauRepository->mettreAJour($tableau);
            }

            Cookie::enregistrer("mdp", $_REQUEST["mdp"]);

            MessageFlash::ajouter("success", "L'utilisateur a bien été modifié !");
            return $this->rediriger("mes_tableaux");
        } else {
            MessageFlash::ajouter("danger", "Login, nom, prenom, email ou mot de passe manquant.");
            return $this->rediriger("mise_a_jour_utilisateur");
        }
    }

    #[Route(path: '/utilisateur/{login}/supprimer', name:'supprimer', methods:["GET"])]
    public function supprimer(string $login): Response
    {
        if(!ConnexionUtilisateur::estConnecte()) {
            return $this->rediriger("connexion");
        }
        if (!ControleurUtilisateur::issetAndNotNull(["login"])) {
            MessageFlash::ajouter("warning", "Login manquant");
            return $this->rediriger("detail_utilisateur");
        }
        $login = $_REQUEST["login"];

        $carteRepository = new CarteRepository();
        $cartes = $carteRepository->recupererCartesUtilisateur($login);
        foreach ($cartes as $carte) {
            $participants = $carte->getAffectationsCarte();
            $participants = array_filter($participants, function ($u) use ($login) {return $u->getLogin() !== $login;});
            $carte->setAffectationsCarte($participants);
            $carteRepository->mettreAJour($carte);
        }

        $tableauRepository = new TableauRepository();
        $tableaux = $tableauRepository->recupererTableauxParticipeUtilisateur($login);
        foreach ($tableaux as $tableau) {
            $participants = $tableau->getParticipants();
            $participants = array_filter($participants, function ($u) use ($login) {return $u->getLogin() !== $login;});
            $tableau->setParticipants($participants);
            $tableauRepository->mettreAJour($tableau);
        }
        $repository = new UtilisateurRepository();
        $repository->supprimer($login);
        Cookie::supprimer("login");
        Cookie::supprimer("mdp");
        ConnexionUtilisateur::deconnecter();
        MessageFlash::ajouter("success", "Votre compte a bien été supprimé !");
        return $this->rediriger("connexion");
    }

    #[Route(path: '/utilisateur/connexion', name:'connexion', methods:["GET"])]
    public function afficherFormulaireConnexion(): Response
    {
        if(ConnexionUtilisateur::estConnecte()) {
            return $this->rediriger("mes_tableaux");
        }
        return $this->afficherVue('vueGenerale.php', [
            "pagetitle" => "Formulaire de connexion",
            "cheminVueBody" => "utilisateur/formulaireConnexion.php"
        ]);
    }

    #[Route(path: '/utilisateur/connexion', name:'connecter', methods:["POST"])]
    public function connecter(): Response
    {
        if(ConnexionUtilisateur::estConnecte()) {
            self::rediriger("mes_tableaux");
        }
        if (!ControleurUtilisateur::issetAndNotNull(["login", "mdp"])) {
            MessageFlash::ajouter("danger", "Login ou mot de passe manquant.");
            return $this->rediriger("connexion");
        }
        $utilisateurRepository = new UtilisateurRepository();
        /** @var Utilisateur $utilisateur */
        $utilisateur = $utilisateurRepository->recupererParClePrimaire($_REQUEST["login"]);

        if ($utilisateur == null) {
            MessageFlash::ajouter("warning", "Login inconnu.");
            return $this->rediriger("connexion");
        }

        if (!MotDePasse::verifier($_REQUEST["mdp"], $utilisateur->getMdpHache())) {
            MessageFlash::ajouter("warning", "Mot de passe incorrect.");
            return $this->rediriger("connexion");
        }

        ConnexionUtilisateur::connecter($utilisateur->getLogin());
        Cookie::enregistrer("login", $_REQUEST["login"]);
        Cookie::enregistrer("mdp", $_REQUEST["mdp"]);
        MessageFlash::ajouter("success", "Connexion effectuée.");
        return $this->rediriger("mes_tableaux");
    }

    #[Route(path: '/utilisateur/deconnexion', name:'deconnecter', methods:["GET"])]
    public function deconnecter(): Response
    {
        if (!ConnexionUtilisateur::estConnecte()) {
            MessageFlash::ajouter("danger", "Utilisateur non connecté.");
            return $this->rediriger("accueil");
        }
        ConnexionUtilisateur::deconnecter();
        MessageFlash::ajouter("success", "L'utilisateur a bien été déconnecté.");
        return $this->rediriger("accueil");
    }

    #[Route(path: '/utilisateur/back-up', name:'recuperation_compte', methods:["GET"])]
    public function afficherFormulaireRecuperationCompte(): Response {
        if(ConnexionUtilisateur::estConnecte()) {
            return $this->rediriger("mes_tableaux");
        }
        return $this->afficherVue('vueGenerale.php', [
            "pagetitle" => "Récupérer mon compte",
            "cheminVueBody" => "utilisateur/resetCompte.php"
        ]);
    }

    #[Route(path: '/utilisateur/back-up', name:'recuperer_compte', methods:["POST"])]
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
    }
}