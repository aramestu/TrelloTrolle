<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\ColonneRepository;
use App\Trellotrolle\Modele\Repository\TableauRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ControleurColonne extends ControleurGenerique
{
    public function afficherErreur($messageErreur = "", $controleur = ""): Response
    {
        return parent::afficherErreur($messageErreur, "colonne");
    }

    #[Route(path: '/colonne/{idColonne}/supprimer', name:'supprimer_colonne', methods:["GET"])]
    public function supprimerColonne(): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return $this->rediriger("connexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idColonne"])) {
            MessageFlash::ajouter("danger", "Code de colonne manquant");
            return $this->rediriger("accueil");
        }
        $colonneRepository = new ColonneRepository();
        $idColonne = $_REQUEST["idColonne"];
        /**
         * @var Colonne $colonne
         */
        $colonne = $colonneRepository->recupererParClePrimaire($idColonne);
        if(!$colonne) {
            MessageFlash::ajouter("danger", "Colonne inexistante");
            return $this->rediriger("accueil");
        }
        $tableau = $colonne->getTableau();

        if(!$tableau->estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        $carteRepository = new CarteRepository();

        if($carteRepository->getNombreCartesTotalUtilisateur($tableau->getUtilisateur()->getLogin()) == 1) {
            MessageFlash::ajouter("danger", "Vous ne pouvez pas supprimer cette colonne car cela entrainera la supression du compte du propriétaire du tableau");
            return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }

        $colonneRepository->supprimer($idColonne);
        $colonneRepository = new ColonneRepository();
        if($colonneRepository->getNombreColonnesTotalTableau($tableau->getIdTableau()) > 0) {
            return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }

        return $this->rediriger("mes_tableaux");
    }

    #[Route(path: '/colonne/creation', name:'creation_colonne', methods:["GET"])]
    public function afficherFormulaireCreationColonne(): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return $this->rediriger("connexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idTableau"])) {
            MessageFlash::ajouter("warning", "Identifiant du tableau manquant");
            return $this->rediriger("accueil");
        }
        $repository = new TableauRepository();
        /**
         * @var Tableau $tableau
         */
        $tableau = $repository->recupererParClePrimaire($_REQUEST["idTableau"]);
        if(!$tableau) {
            MessageFlash::ajouter("warning", "Tableau inexistant");
            return $this->rediriger("accueil");
        }
        if(!$tableau->estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        return ControleurTableau::afficherVue('vueGenerale.php', [
            "pagetitle" => "Création d'une colonne",
            "cheminVueBody" => "colonne/formulaireCreationColonne.php",
            "idTableau" => $_REQUEST["idTableau"],
        ]);
    }

    #[Route(path: '/colonne/creation', name:'creer_colonne', methods:["POST"])]
    public function creerColonne(): Response {
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
        if(!ControleurCarte::issetAndNotNull(["nomColonne"])) {
            MessageFlash::ajouter("danger", "Nom de colonne manquant");
            return $this->rediriger("creation_colonne", ["idTableau" => $_REQUEST["idTableau"]]);
        }
        if(!$tableau->estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        $colonneRepository = new ColonneRepository();
        $carteRepository = new CarteRepository();
        $colonne = new Colonne(
            $tableau,
            $colonneRepository->getNextIdColonne(),
            $_REQUEST["nomColonne"]
        );
        $carte = new Carte(
            $colonne,
            $carteRepository->getNextIdCarte(),
            "Exemple",
            "Exemple de carte",
            "#FFFFFF",
            []
        );
        $carteRepository->ajouter($carte);
        return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    #[Route(path: '/colonne/{idColonne}/mise-a-jour', name:'mise_a_jour_colonne', methods:["GET"])]
    public function afficherFormulaireMiseAJourColonne(): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return $this->rediriger("connexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idColonne"])) {
            MessageFlash::ajouter("danger", "Identifiant du colonne manquant");
            return $this->rediriger("accueil");
        }
        $colonneRepository = new ColonneRepository();
        /**
         * @var Colonne $colonne
         */
        $colonne = $colonneRepository->recupererParClePrimaire($_REQUEST["idColonne"]);
        if(!$colonne) {
            MessageFlash::ajouter("danger", "Colonne inexistante");
            return $this->rediriger("accueil");
        }
        $tableau = $colonne->getTableau();
        if(!$tableau->estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        return ControleurTableau::afficherVue('vueGenerale.php', [
            "pagetitle" => "Modification d'une colonne",
            "cheminVueBody" => "colonne/formulaireMiseAJourColonne.php",
            "idColonne" => $_REQUEST["idColonne"],
            "nomColonne" => $colonne->getTitreColonne()
        ]);
    }

    #[Route(path: '/colonne/{idColonne}/mise_a_jour', name:'mettre_a_jour_colonne', methods:["POST"])]
    public function mettreAJourColonne(): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return $this->rediriger("connexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idColonne"])) {
            MessageFlash::ajouter("danger", "Identifiant du colonne manquant");
            return $this->rediriger("accueil");
        }
        $colonneRepository = new ColonneRepository();
        /**
         * @var Colonne $colonne
         */
        $colonne = $colonneRepository->recupererParClePrimaire($_REQUEST["idColonne"]);
        if(!$colonne) {
            MessageFlash::ajouter("danger", "Colonne inexistante");
            return $this->rediriger("accueil");
        }
        if(!ControleurCarte::issetAndNotNull(["nomColonne"])) {
            MessageFlash::ajouter("danger", "Nom de colonne manquant");
            return $this->rediriger("mise_a_jour_colonne", ["idColonne" => $_REQUEST["idColonne"]]);
        }
        $tableau = $colonne->getTableau();
        if(!$tableau->estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        $colonne->setTitreColonne($_REQUEST["nomColonne"]);
        $colonneRepository->mettreAJour($colonne);
        return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }
}