<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Lib\ConnexionUtilisateurInterface;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\CarteRepositoryInterface;
use App\Trellotrolle\Modele\Repository\ColonneRepository;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;
use App\Trellotrolle\Service\CarteServiceInterface;
use App\Trellotrolle\Service\ColonneServiceInterface;
use App\Trellotrolle\Service\TableauServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ControleurCarte extends ControleurGenerique
{
    public function __construct(ContainerInterface $container,
                                private CarteServiceInterface $carteService,
                                private ColonneServiceInterface $colonneService,
                                private TableauServiceInterface $tableauService,
                                private readonly ConnexionUtilisateurInterface $connexionUtilisateurSession
    ){
        parent::__construct($container);
    }
    public function afficherErreur($messageErreur = "", $controleur = ""): Response
    {
        return parent::afficherErreur($messageErreur, "carte");
    }

    private function estConnecte(): bool{
        return $this->connexionUtilisateurSession->estConnecte();
    }

    #[Route(path: '/carte/{idCarte}/suppression', name:'supprimer_carte', methods:["GET"])]
    public function supprimerCarte(string $idCarte): RedirectResponse {
        if(! $this->estConnecte()) {
            return $this->rediriger("connexion");
        }
        try{
            $tableau = $this->carteService->supprimerCarte($idCarte, $this->connexionUtilisateurSession->getIdUtilisateurConnecte());
        }catch (\Exception $e){
            MessageFlash::ajouter("error", $e->getMessage());
            return $this->rediriger("mes_tableaux");
        }

        MessageFlash::ajouter("success", "La carte a bien été supprimé!");
        return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    #[Route(path: '{idColonne}/carte/creation', name:'creation_carte', methods:["GET"])]
    public function afficherFormulaireCreationCarte(int $idColonne): Response {
        if(! $this->estConnecte()) {
            return $this->rediriger("connexion");
        }
        try{
            $colonne = $this->colonneService->getColonne($idColonne);
            $idTableau = $colonne->getTableau()->getIdTableau();

            $this->tableauService->verifierParticipant($this->connexionUtilisateurSession->getIdUtilisateurConnecte(), $idTableau);
            $colonnes = $this->colonneService->recupererColonnesTableau($idTableau);
        }catch (\Exception $e){
            MessageFlash::ajouter("error", $e->getMessage());
            return $this->rediriger("mes_tableaux");
        }
        return $this->afficherTwig("carte/formulaireCreationCarte.html.twig", ["colonne" => $colonne, "colonnes" => $colonnes, "pagetitle" => "Création carte"]);
    }

    #[Route(path: '/carte/creation', name:'creer_carte', methods:["POST"])]
    public function creerCarte(): Response {
        if(! $this->estConnecte()) {
            return $this->rediriger("connexion");
        }
        try{
            $tableau = $this->carteService->creerCarte($_POST["idColonne"], $_POST["titreCarte"],$_POST["descriptifCarte"],$_POST["couleurCarte"], $this->connexionUtilisateurSession->getIdUtilisateurConnecte(), $_POST["affectationsCarte"]);
        }catch (\Exception $e){
            MessageFlash::ajouter("error", $e->getMessage());

            if(isset($_POST["idColonne"])){
                return $this->rediriger("creation_carte", ["idColonne" => $_POST["idColonne"]]);
            }
            return $this->rediriger("mes_tableaux");
        }
        return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    #[Route(path: '/carte/{idCarte}/mise-a-jour', name:'mise_a_jour_carte', methods:["GET"])]
    public function afficherFormulaireMiseAJourCarte(string $idCarte): Response{
        if(! $this->estConnecte()) {
            return $this->rediriger("connexion");
        }
        try{
            $carte = $this->carteService->getCarte($idCarte);
            $colonne = $this->colonneService->getColonne($carte->getColonne()->getIdColonne());
            $idTableau = $colonne->getTableau()->getIdTableau();

            $this->tableauService->verifierParticipant($this->connexionUtilisateurSession->getIdUtilisateurConnecte(), $idTableau);
            $colonnes = $this->colonneService->recupererColonnesTableau($idTableau);
        }catch (\Exception $e){
            MessageFlash::ajouter("error", $e->getMessage());
            return $this->rediriger("mes_tableaux");
        }
        return $this->afficherTwig("carte/formulaireMiseAJourCarte.html.twig", ["carte" => $carte, "colonnes" => $colonnes, "pagetitle" => "Modification d'une carte"]);
    }

    #[Route(path: '/carte/{idCarte}/mettre_a_jour', name:'mettre_a_jour_carte', methods:["POST"])]
    public function mettreAJourCarte(string $idCarte): Response {
        if(! $this->estConnecte()) {
            return $this->rediriger("connexion");
        }
        try{
            $tableau = $this->carteService->mettreAJourCarte($idCarte, $_POST["idColonne"],$_POST["titreCarte"],$_POST["descriptifCarte"],$_POST["couleurCarte"], $this->connexionUtilisateurSession->getIdUtilisateurConnecte(), $_POST["affectationsCarte"]);
        }catch (\Exception $e){
            MessageFlash::ajouter("error", $e->getMessage());
            return $this->rediriger("mise_a_jour_carte", ["idCarte" => $idCarte]);
        }
        return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }
}