<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\ColonneRepository;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ControleurCarte extends ControleurGenerique
{
    public function afficherErreur($messageErreur = "", $controleur = ""): Response
    {
        return parent::afficherErreur($messageErreur, "carte");
    }

    #[Route(path: '/carte/{idCarte}/suppression', name:'supprimer_carte', methods:["GET"])]
    public function supprimerCarte(string $idCarte): RedirectResponse {
        if(!ConnexionUtilisateur::estConnecte()) {
            return $this->rediriger("connexion");
        }
        $carteRepository = new CarteRepository();
        /**
         * @var Carte $carte
         */
        $carte = $carteRepository->recupererParClePrimaire($idCarte);
        if(!$carte) {
            MessageFlash::ajouter("danger", "Carte inexistante");
            return $this->rediriger("accueil");
        }

        $tableau = $carte->getColonne()->getTableau();

        if(!$tableau->estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        if($carteRepository->getNombreCartesTotalUtilisateur($tableau->getUtilisateur()->getLogin()) == 1) {
            MessageFlash::ajouter("danger", "Vous ne pouvez pas supprimer cette carte car cela entrainera la supression du compte du propriétaire du tableau");
            return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        $carteRepository->supprimer($idCarte);
        $cartes = $carteRepository->recupererCartesTableau($tableau->getIdTableau());
        if(count($cartes) > 0) {
            return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        else {
            return $this->rediriger("mes_tableaux");
        }
    }

    #[Route(path: '{idColonne}/carte/creation', name:'creation_carte', methods:["GET"])]
    public function afficherFormulaireCreationCarte(string $idColonne): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return $this->rediriger("connexion");
        }
        $colonneRepository = new ColonneRepository();
        /**
         * @var Colonne $colonne
         */
        $colonne = $colonneRepository->recupererParClePrimaire($idColonne);
        if(!$colonne) {
            MessageFlash::ajouter("warning", "Colonne inexistante");
            return $this->rediriger("accueil");
        }
        $tableau = $colonne->getTableau();
        if(!$tableau->estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        $colonnes = $colonneRepository->recupererColonnesTableau($tableau->getIdTableau());
        return $this->afficherVue('vueGenerale.php', [
            "pagetitle" => "Création d'une carte",
            "cheminVueBody" => "carte/formulaireCreationCarte.php",
            "colonne" => $colonne,
            "colonnes" => $colonnes
        ]);
    }

    #[Route(path: '/carte/creation', name:'creer_carte', methods:["POST"])]
    public function creerCarte(): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return $this->rediriger("connexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idColonne"])) {
            MessageFlash::ajouter("warning", "Identifiant de colonne manquant");
            return $this->rediriger("accueil");
        }
        $colonneRepository = new ColonneRepository();
        /**
         * @var Colonne $colonne
         */
        $colonne = $colonneRepository->recupererParClePrimaire($_REQUEST["idColonne"]);
        if(!$colonne) {
            MessageFlash::ajouter("warning", "Colonne inexistante");
            return $this->rediriger("accueil");
        }
        if(!ControleurCarte::issetAndNotNull(["titreCarte", "descriptifCarte", "couleurCarte"])) {
            MessageFlash::ajouter("danger", "Attributs manquants");
            return $this->rediriger("creation_carte", ["idColonne" => $_REQUEST["idColonne"]]);
        }
        $tableau = $colonne->getTableau();
        if(!$tableau->estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        $affectations = [];
        $utilisateurRepository = new UtilisateurRepository();
        if(ControleurCarte::issetAndNotNull(["affectationsCarte"])) {
            foreach ($_REQUEST["affectationsCarte"] as $affectation) {
                /**
                 * @var Utilisateur $utilisateur
                 */
                $utilisateur = $utilisateurRepository->recupererParClePrimaire($affectation);
                if(!$utilisateur) {
                    MessageFlash::ajouter("danger", "Un des membres affecté à la tâche n'existe pas");
                    return $this->rediriger("creation_carte", ["idColonne" => $_REQUEST["idColonne"]]);
                }
                if(!$tableau->estParticipantOuProprietaire($utilisateur->getLogin())) {
                    MessageFlash::ajouter("danger", "Un des membres affecté à la tâche n'est pas affecté au tableau.");
                    return $this->rediriger("creation_carte", ["idColonne" => $_REQUEST["idColonne"]]);
                }
                $affectations[] = $utilisateur;
            }
        }
        $carteRepository = new CarteRepository();
        $carte = new Carte(
            $colonne,
            $carteRepository->getNextIdCarte(),
            $_REQUEST["titreCarte"],
            $_REQUEST["descriptifCarte"],
            $_REQUEST["couleurCarte"],
            $affectations
        );
        $carteRepository->ajouter($carte);
        return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    #[Route(path: '/carte/{idCarte}/mise-a-jour', name:'mise_a_jour_carte', methods:["GET"])]
    public function afficherFormulaireMiseAJourCarte(string $idCarte): Response{
        if(!ConnexionUtilisateur::estConnecte()) {
            return $this->rediriger("connexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idCarte"])) {
            MessageFlash::ajouter("warning", "Identifiant de la carte manquant");
            return $this->rediriger("accueil");
        }
        $carteRepository = new CarteRepository();
        /**
         * @var Carte $carte
         */
        $carte = $carteRepository->recupererParClePrimaire($_REQUEST["idCarte"]);
        if(!$carte) {
            MessageFlash::ajouter("warning", "Carte inexistante");
            return $this->rediriger("accueil");
        }
        $tableau = $carte->getColonne()->getTableau();
        if(!$tableau->estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        $colonneRepository = new ColonneRepository();
        $colonnes = $colonneRepository->recupererColonnesTableau($tableau->getIdTableau());
        return ControleurTableau::afficherVue('vueGenerale.php', [
            "pagetitle" => "Modification d'une carte",
            "cheminVueBody" => "carte/formulaireMiseAJourCarte.php",
            "carte" => $carte,
            "colonnes" => $colonnes
        ]);
    }

    #[Route(path: '/carte/{idCarte}/mettre_a_jour', name:'mettre_a_jour_carte', methods:["POST"])]
    public function mettreAJourCarte(string $idCarte): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return $this->rediriger("connexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idCarte"])) {
            MessageFlash::ajouter("warning", "Identifiant de la carte manquant");
            return $this->rediriger("accueil");
        }
        if(!ControleurCarte::issetAndNotNull(["idColonne"])) {
            MessageFlash::ajouter("warning", "Identifiant de colonne manquant");
            return $this->rediriger("accueil");
        }
        $carteRepository = new CarteRepository();
        /**
         * @var Carte $carte
         */
        $carte = $carteRepository->recupererParClePrimaire($idCarte);

        $colonnesRepository = new ColonneRepository();
        /**
         * @var Colonne $colonne
         */
        $colonne = $colonnesRepository->recupererParClePrimaire($_REQUEST["idColonne"]);
        if(!$carte) {
            MessageFlash::ajouter("warning", "Carte inexistante");
            return $this->rediriger("accueil");
        }
        if(!$colonne) {
            MessageFlash::ajouter("warning", "Colonne inexistante");
            return $this->rediriger("accueil");
        }
        if(!ControleurCarte::issetAndNotNull(["titreCarte", "descriptifCarte", "couleurCarte"])) {
            MessageFlash::ajouter("danger", "Attributs manquants");
            return $this->rediriger("mise_a_jour_carte", ["idCarte" => $idCarte]);
        }

        $originalColonne = $carte->getColonne();
        if($originalColonne->getTableau()->getIdTableau() !== $colonne->getTableau()->getIdTableau()) {
            MessageFlash::ajouter("danger", "Le tableau de cette colonne n'est pas le même que celui de la colonne d'origine de la carte!");
            return $this->rediriger("mise_a_jour_carte", ["idCarte" => $idCarte]);
        }
        $tableau = $colonne->getTableau();
        if(!$tableau->estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }

        $carte->setColonne($colonne);
        $carte->setTitreCarte($_REQUEST["titreCarte"]);
        $carte->setDescriptifCarte($_REQUEST["descriptifCarte"]);
        $carte->setCouleurCarte($_REQUEST["couleurCarte"]);
        $affectations = [];
        $utilisateurRepository = new UtilisateurRepository();
        if(ControleurCarte::issetAndNotNull(["affectationsCarte"])) {
            foreach ($_REQUEST["affectationsCarte"] as $affectation) {
                /**
                 * @var Utilisateur $utilisateur
                 */
                $utilisateur = $utilisateurRepository->recupererParClePrimaire($affectation);
                if(!$utilisateur) {
                    MessageFlash::ajouter("danger", "Un des membres affecté à la tâche n'existe pas");
                    return $this->rediriger("mise_a_jour_carte", ["idCarte" => $idCarte]);
                }
                if(!$tableau->estParticipantOuProprietaire($utilisateur->getLogin())) {
                    MessageFlash::ajouter("danger", "Un des membres affecté à la tâche n'est pas affecté au tableau.");
                    return $this->rediriger("creation_carte", ["idColonne" => $_REQUEST["idColonne"]]);
                }
                $affectations[] = $utilisateur;
            }
        }
        $carte->setAffectationsCarte($affectations);
        $carteRepository->mettreAJour($carte);
        return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }
}