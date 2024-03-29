<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Lib\ConnexionUtilisateurInterface;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\ColonneRepository;
use App\Trellotrolle\Modele\Repository\TableauRepository;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;
use App\Trellotrolle\Service\ColonneServiceInterface;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\TableauServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ControleurTableau extends ControleurGenerique
{

    private function __construct(ContainerInterface $container, private readonly TableauServiceInterface $tableauService,
                                 private ConnexionUtilisateurInterface $utilisateur, private ColonneServiceInterface $colonneService,
                                 private CarteServiceInterface $carteService){
        parent::__construct($container);
    }

    public function afficherErreur($messageErreur = "", $controleur = ""): Response
    {
        return parent::afficherErreur($messageErreur, "tableau");
    }

    #[Route(path: '/tableau/{codeTableau}/afficher', name:'afficher_tableau', methods:["GET"])]
    public function afficherTableau() : Response {
        try {
            $tableau = $this->tableauService->getByCodeTableau($_REQUEST["codeTableau"]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("error", $e);
            return $this->rediriger("acceuil");
        }

        $colonnes = $this->colonneService->recupererColonnesTableau($tableau->getIdTableau());
        $data = [];
        $participant = [];

        foreach ($colonnes as $colonne){
            $cartes = $this->carteService->
        }


        /*if(!ControleurTableau::issetAndNotNull(["codeTableau"])) {
            MessageFlash::ajouter("warning", "Code de tableau manquant");
            return $this->rediriger("accueil");
        }
        $code = $_REQUEST["codeTableau"];
        $tableauRepository = new TableauRepository();

        /**
         * @var Tableau $tableau
         */
        /*$tableau = $tableauRepository->recupererParCodeTableau($code);
        if(!$tableau) {
            MessageFlash::ajouter("warning", "Tableau inexistant");
            return $this->rediriger("accueil");
        }
        $colonneRepository = new ColonneRepository();

        /**
         * @var Colonne[] $colonnes
         */
        /*$colonnes = $colonneRepository->recupererColonnesTableau($tableau->getIdTableau());
        $data = [];
        $participants = [];

        $carteRepository = new CarteRepository();
        foreach ($colonnes as $colonne) {
            /**
             * @var Carte[] $cartes
             */
            /*$cartes = $carteRepository->recupererCartesColonne($colonne->getIdColonne());
            foreach ($cartes as $carte) {
                foreach ($carte->getAffectationsCarte() as $utilisateur) {
                    if(!isset($participants[$utilisateur->getLogin()])) {
                        $participants[$utilisateur->getLogin()] = ["infos" => $utilisateur, "colonnes" => []];
                    }
                    if(!isset($participants[$utilisateur->getLogin()]["colonnes"][$colonne->getIdColonne()])) {
                        $participants[$utilisateur->getLogin()]["colonnes"][$colonne->getIdColonne()] = [$colonne->getTitreColonne(), 0];
                    }
                    $participants[$utilisateur->getLogin()]["colonnes"][$colonne->getIdColonne()][1]++;
                }
            }
            $data[] = $cartes;
        }

        return ControleurTableau::afficherVue('vueGenerale.php', [
            "pagetitle" => "{$tableau->getTitreTableau()}",
            "cheminVueBody" => "tableau/tableau.php",
            "tableau" => $tableau,
            "colonnes" => $colonnes,
            "participants" => $participants,
            "data" => $data,
        ]);*/
    }

    #[Route(path: '/tableau/{idTableau}/mise-a-jour', name:'mise_a_jour_tableau', methods:["GET"])]
    public function afficherFormulaireMiseAJourTableau(): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return self::rediriger("connexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idTableau"])) {
            MessageFlash::ajouter("danger", "Identifiant du tableau manquant");
            return $this->rediriger("accueil");
        }
        $repository = new TableauRepository();
        /**
         * @var Tableau $tableau
         */
        $tableau = $repository->recupererParClePrimaire($_REQUEST["idTableau"]);
        if(!$tableau) {
            MessageFlash::ajouter("danger", "Tableau inexistant");
            return $this->rediriger("accueil");
        }
        if(!$tableau->estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        return ControleurTableau::afficherVue('vueGenerale.php', [
            "pagetitle" => "Modification d'un tableau",
            "cheminVueBody" => "tableau/formulaireMiseAJourTableau.php",
            "idTableau" => $_REQUEST["idTableau"],
            "nomTableau" => $tableau->getTitreTableau()
        ]);
    }

    #[Route(path: '/tableau/{idTableau}/mise-a-jour', name:'creation_tableau', methods:["GET"])]
    public function afficherFormulaireCreationTableau(): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return $this->rediriger("connexion");
        }
        return ControleurTableau::afficherVue('vueGenerale.php', [
            "pagetitle" => "Ajout d'un tableau",
            "cheminVueBody" => "tableau/formulaireCreationTableau.php",
        ]);
    }

    #[Route(path: '/tableau/creation', name:'creer_tableau', methods:["GET"])]
    public function creerTableau(): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return $this->rediriger("connexion");
        }
        $utilisateurRepository = new UtilisateurRepository();

        /**
         * @var Utilisateur $utilisateur
         */
        $utilisateur = $utilisateurRepository->recupererParClePrimaire(ConnexionUtilisateur::getLoginUtilisateurConnecte());
        if(!ControleurCarte::issetAndNotNull(["nomTableau"])) {
            MessageFlash::ajouter("danger", "Nom de tableau manquant");
            return $this->rediriger("creation_tableau");
        }
        $tableauRepository = new TableauRepository();
        $idTableau = $tableauRepository->getNextIdTableau();
        $codeTableau = hash("sha256", $utilisateur->getLogin().$idTableau);

        $colonneRepository = new ColonneRepository();
        $idColonne1 = $colonneRepository->getNextIdColonne();
        $nomColonne1 = "TODO";

        $nomColonne2 = "DOING";
        $idColonne2 = $idColonne1 + 1;

        $nomColonne3 = "DONE";
        $idColonne3 = $idColonne1 + 2;

        $carteInitiale = "Exemple";
        $descriptifInitial = "Exemple de carte";

        $carteRepository = new CarteRepository();

        $idCarte1 = $carteRepository->getNextIdCarte();
        $idCarte2 = $idCarte1 + 1;
        $idCarte3 = $idCarte1 + 2;

        $tableau = new Tableau(
            $utilisateur,
            $idTableau,
            $codeTableau,
            $_REQUEST["nomTableau"],
            []
        );

        $carte1 = new Carte(
            new Colonne(
                $tableau,
                $idColonne1,
                $nomColonne1
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
                $nomColonne2
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
                $nomColonne3
            ),
            $idCarte3,
            $carteInitiale,
            $descriptifInitial,
            "#FFFFFF",
            []
        );

        $carteRepository->ajouter($carte1);
        $carteRepository->ajouter($carte2);
        $carteRepository->ajouter($carte3);

        return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    #[Route(path: '/tableau/mise-a-jour', name:'mise_a_jour_tableau', methods:["POST"])]
    public function mettreAJourTableau(): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return $this->rediriger("connexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idTableau"])) {
            MessageFlash::ajouter("danger", "Identifiant de tableau manquant");
            return $this->rediriger("accueil");
        }
        $repository = new TableauRepository();

        /**
         * @var Tableau $tableau
         */
        $tableau = $repository->recupererParClePrimaire($_REQUEST["idTableau"]);
        if(!$tableau) {
            MessageFlash::ajouter("danger", "Tableau inexistant");
            return $this->rediriger("accueil");
        }
        if(!ControleurCarte::issetAndNotNull(["nomTableau"])) {
            MessageFlash::ajouter("danger", "Nom de tableau manquant");
            ControleurTableau::redirection("tableau", "afficherFormulaireMiseAJourTableau", ["idTableau" => $_REQUEST["idTableau"]]);
        }
        if(!$tableau->estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
        }
        else {
            $tableau->setTitreTableau($_REQUEST["nomTableau"]);
            $repository->mettreAJour($tableau);
        }
        return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    #[Route(path: '/tableau/ajout-membre', name:'ajout_membre', methods:["GET"])]
    public function afficherFormulaireAjoutMembre(): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return $this->rediriger("connexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idTableau"])) {
            MessageFlash::ajouter("danger", "Identifiant du tableau manquant");
            return $this->rediriger("accueil");
        }
        $repository = new TableauRepository();
        /**
         * @var Tableau $tableau
         */
        $tableau = $repository->recupererParClePrimaire($_REQUEST["idTableau"]);
        if(!$tableau) {
            MessageFlash::ajouter("danger", "Tableau inexistant");
            return $this->rediriger("accueil");
        }
        if(!$tableau->estProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'êtes pas propriétaire de ce tableau");
            return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }

        $utilisateurRepository = new UtilisateurRepository();

        /**
         * @var Utilisateur[] $utilisateurs
         */
        $utilisateurs = $utilisateurRepository->recupererUtilisateursOrderedPrenomNom();
        $filtredUtilisateurs = array_filter($utilisateurs, function ($u) use ($tableau) {return !$tableau->estParticipantOuProprietaire($u->getLogin());});

        if(empty($filtredUtilisateurs)) {
            MessageFlash::ajouter("warning", "Il n'est pas possible d'ajouter plus de membre à ce tableau.");
            return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }

        return ControleurTableau::afficherVue('vueGenerale.php', [
            "pagetitle" => "Ajout d'un membre",
            "cheminVueBody" => "tableau/formulaireAjoutMembreTableau.php",
            "tableau" => $tableau,
            "utilisateurs" => $filtredUtilisateurs
        ]);
    }

    #[Route(path: '/tableau/ajout-membre', name:'ajouter_membre', methods:["POST"])]
    public function ajouterMembre(): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return $this->rediriger("connexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idTableau"])) {
            MessageFlash::ajouter("danger", "Identifiant du tableau manquant");
            return $this->rediriger("accueil");
        }
        $repository = new TableauRepository();

        /**
         * @var Tableau $tableau
         */
        $tableau = $repository->recupererParClePrimaire($_REQUEST["idTableau"]);
        if(!$tableau) {
            MessageFlash::ajouter("danger", "Tableau inexistant");
            return $this->rediriger("accueil");
        }
        if(!$tableau->estProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'êtes pas propriétaire de ce tableau");
            return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        if(!ControleurCarte::issetAndNotNull(["login"])) {
            MessageFlash::ajouter("danger", "Login du membre à ajouter manquant");
            return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }

        $utilisateurRepository = new UtilisateurRepository();
        /**
         * @var Utilisateur $utilisateur
         */
        $utilisateur = $utilisateurRepository->recupererParClePrimaire($_REQUEST["login"]);
        if(!$utilisateur) {
            MessageFlash::ajouter("danger", "Utlisateur inexistant");
            return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        if($tableau->estParticipantOuProprietaire($utilisateur->getLogin())) {
            MessageFlash::ajouter("warning", "Ce membre est déjà membre du tableau.");
            return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }

        $participants = $tableau->getParticipants();
        $participants[] = $utilisateur;
        $tableau->setParticipants($participants);
        $repository->mettreAJour($tableau);

        return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    #[Route(path: '/tableau/supprimer-membre', name:'supprimer_membre', methods:["POST"])]
    public function supprimerMembre(): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return $this->rediriger("connexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idTableau"])) {
            MessageFlash::ajouter("danger", "Identifiant du tableau manquant");
            return $this->rediriger("accueil");
        }
        $repository = new TableauRepository();
        /**
         * @var Tableau $tableau
         */
        $tableau = $repository->recupererParClePrimaire($_REQUEST["idTableau"]);
        if(!$tableau) {
            MessageFlash::ajouter("danger", "Tableau inexistant");
            return $this->rediriger("accueil");
        }
        if(!$tableau->estProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'êtes pas propriétaire de ce tableau");
            return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        if(!ControleurCarte::issetAndNotNull(["login"])) {
            MessageFlash::ajouter("danger", "Login du membre à supprimer manquant");
            return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        $utilisateurRepository = new UtilisateurRepository();
        /**
         * @var Utilisateur $utilisateur
         */
        $utilisateur = $utilisateurRepository->recupererParClePrimaire($_REQUEST["login"]);
        if(!$utilisateur) {
            MessageFlash::ajouter("danger", "Utlisateur inexistant");
            return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        if($tableau->estProprietaire($utilisateur->getLogin())) {
            MessageFlash::ajouter("danger", "Vous ne pouvez pas vous supprimer du tableau.");
            return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        if(!$tableau->estParticipant($utilisateur->getLogin())) {
            MessageFlash::ajouter("danger", "Cet utilisateur n'est pas membre du tableau");
            return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }

        $participants = array_filter($tableau->getParticipants(), function ($u) use ($utilisateur) {return $u->getLogin() !== $utilisateur->getLogin();});
        $tableau->setParticipants($participants);
        $repository->mettreAJour($tableau);

        $cartesRepository = new CarteRepository();
        $cartes = $cartesRepository->recupererCartesTableau($tableau->getIdTableau());
        foreach ($cartes as $carte) {
            $affectations = array_filter($carte->getAffectationsCarte(), function ($u) use ($utilisateur) {return $u->getLogin() != $utilisateur->getLogin();});
            $carte->setAffectationsCarte($affectations);
            $cartesRepository->mettreAJour($carte);
        }
        return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    #[Route(path: '/mes-tableaux', name:'mes_tableaux', methods:["GET"])]
    public function afficherListeMesTableaux() : Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return $this->rediriger("connexion");
        }
        $repository = new TableauRepository();
        $login = ConnexionUtilisateur::getLoginUtilisateurConnecte();
        $tableaux = $repository->recupererTableauxOuUtilisateurEstMembre($login);
        return ControleurTableau::afficherVue('vueGenerale.php', [
            "pagetitle" => "Liste des tableaux de $login",
            "cheminVueBody" => "tableau/listeTableauxUtilisateur.php",
            "tableaux" => $tableaux
        ]);
    }

    #[Route(path: '/tableau/{idTableau}/quitter', name:'quitter_tableau', methods:["GET"])]
    public function quitterTableau(string $idtableau): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return $this->rediriger("connexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idTableau"])) {
            MessageFlash::ajouter("danger", "Identifiant du tableau manquant");
            return $this->rediriger("mes_tableaux");
        }
        $repository = new TableauRepository();
        /**
         * @var Tableau $tableau
         */
        $tableau = $repository->recupererParClePrimaire($_REQUEST["idTableau"]);
        if(!$tableau) {
            MessageFlash::ajouter("danger", "Tableau inexistant");
            return $this->rediriger("mes_tableaux");
        }

        $utilisateurRepository = new UtilisateurRepository();

        /**
         * @var Utilisateur $utilisateur
         */
        $utilisateur = $utilisateurRepository->recupererParClePrimaire(ConnexionUtilisateur::getLoginUtilisateurConnecte());
        if($tableau->estProprietaire($utilisateur->getLogin())) {
            MessageFlash::ajouter("danger", "Vous ne pouvez pas quitter ce tableau");
            return $this->rediriger("mes_tableaux");
        }
        if(!$tableau->estParticipant(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'appartenez pas à ce tableau");
            return $this->rediriger("mes_tableaux");
        }
        $participants = array_filter($tableau->getParticipants(), function ($u) use ($utilisateur) {return $u->getLogin() !== $utilisateur->getLogin();});
        $tableau->setParticipants($participants);
        $repository->mettreAJour($tableau);

        $carteRepository = new CarteRepository();

        /**
         * @var Carte[] $cartes
         */
        $cartes = $carteRepository->recupererCartesTableau($tableau->getIdTableau());
        foreach ($cartes as $carte) {
            $affectations = array_filter($carte->getAffectationsCarte(), function ($u) use ($utilisateur) {return $u->getLogin() != $utilisateur->getLogin();});
            $carte->setAffectationsCarte($affectations);
            $carteRepository->mettreAJour($carte);
        }
        return $this->rediriger("mes_tableaux");
    }

    #[Route(path: '/tableau/{idTableau}/supprimer', name:'supprimer_tableau', methods:["GET"])]
    public function supprimerTableau(string $idTableau): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return $this->rediriger("connexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idTableau"])) {
            MessageFlash::ajouter("danger", "Identifiant de tableau manquant");
            return $this->rediriger("mes_tableaux");
        }
        $repository = new TableauRepository();
        $idTableau = $_REQUEST["idTableau"];
        /**
         * @var Tableau $tableau
         */
        $tableau = $repository->recupererParClePrimaire($idTableau);
        if(!$tableau) {
            MessageFlash::ajouter("danger", "Tableau inexistant");
            return $this->rediriger("mes_tableaux");
        }
        if(!$tableau->estProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'êtes pas propriétaire de ce tableau");
            return $this->rediriger("mes_tableaux");
        }
        if($repository->getNombreTableauxTotalUtilisateur(ConnexionUtilisateur::getLoginUtilisateurConnecte()) == 1) {
            MessageFlash::ajouter("danger", "Vous ne pouvez pas supprimer ce tableau car cela entrainera la supression du compte");
            return $this->rediriger("mes_tableaux");
        }
        $repository->supprimer($idTableau);
        return $this->rediriger("mes_tableaux");
    }
}