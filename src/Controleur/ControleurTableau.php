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
use App\Trellotrolle\Modele\Repository\TableauRepositoryInterface;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;
use App\Trellotrolle\Service\CarteServiceInterface;
use App\Trellotrolle\Service\ColonneServiceInterface;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\TableauServiceInterface;
use App\Trellotrolle\Service\UtilisateurServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ControleurTableau extends ControleurGenerique
{

    public function __construct(ContainerInterface $container, private readonly TableauServiceInterface $tableauService,
                                 private ConnexionUtilisateurInterface $connexionUtilisateurSession, private ColonneServiceInterface $colonneService,
                                 private CarteServiceInterface $carteService, private TableauRepositoryInterface $tableauRepository,
                                 private UtilisateurServiceInterface $utilisateurService){
        parent::__construct($container);
    }

    public function afficherErreur($messageErreur = "", $controleur = ""): Response
    {
        return parent::afficherErreur($messageErreur, "tableau");
    }

    #[Route(path: '/tableau/{codeTableau}/afficher', name:'afficher_tableau', methods:["GET"])]
    public function afficherTableau(string $codeTableau) : Response {
        try {
            $tableau = $this->tableauService->getByCodeTableau($codeTableau);
            $associationColonneCarte = $this->tableauService->recupererColonnesEtCartesDuTableau($tableau->getIdTableau());
            $informationsAffectation = $this->tableauService->informationsAffectationsCartes($tableau->getIdTableau());
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->rediriger("accueil");
        }

        return $this->afficherTwig("tableau/tableau.html.twig",[
            "tableau" => $tableau,
            "associationColonneCarte" => $associationColonneCarte,
            "informationsAffectation" => $informationsAffectation
        ]);
    }

    private function estConnecte(): bool{
        return $this->connexionUtilisateurSession->estConnecte();
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    #[Route(path: '/tableau/{idTableau}/mise-a-jour', name:'mise_a_jour_tableau', methods:["GET"])] // Nom route modifié car elle exisait déjà pour mettreAJourTableau
    public function afficherFormulaireMiseAJourTableau(int $idTableau): Response {
        if(! $this->estConnecte()) {
            return $this->rediriger("connexion");
        }
        try{
            $tableau = $this->tableauService->getByIdTableau($idTableau);
            $this->tableauService->verifierParticipant($this->connexionUtilisateurSession->getIdUtilisateurConnecte(), $idTableau);
            $nomTableau = $tableau->getTitreTableau();
        }catch (\Exception $e){
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->rediriger("mes_tableaux");
        }
        return self::afficherTwig("tableau/formulaireMiseAJourTableau.html.twig", ["idTableau" => $idTableau, "nomTableau" => $nomTableau, "pagetitle" => "Mise à jour Tableau"]);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    #[Route(path: '/tableau/creation', name:'creation_tableau', methods:["GET"])]
    public function afficherFormulaireCreationTableau(): Response {
        if(! $this->estConnecte()) {
            return $this->rediriger("connexion");
        }
        return self::afficherTwig("tableau/formulaireCreationTableau.html.twig", ["pagetitle" => "Création Tableau"]);
    }

    #[Route(path: '/tableau/creation', name:'creer_tableau', methods:["POST"])]
    public function creerTableau(): Response {
        if(! $this->estConnecte()) {
            return $this->rediriger("connexion");
        }
        try{
            $tableau = $this->tableauService->creerTableau($this->connexionUtilisateurSession->getIdUtilisateurConnecte(), $_POST["nomTableau"]);
        }catch (\Exception $e){
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->rediriger("creation_tableau");
        }
        return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    #[Route(path: '/tableau/mise-a-jour', name:'mettre_a_jour_tableau', methods:["POST"])]
    public function mettreAJourTableau(): Response {
        if(! $this->estConnecte()) {
            return $this->rediriger("connexion");
        }
        $idTableau = $_POST["idTableau"] ?? null;
        $nomTableau = $_POST["nomTableau"] ?? null;
        try{
            $tableau = $this->tableauService->mettreAJourTableau($idTableau, $this->connexionUtilisateurSession->getIdUtilisateurConnecte() , $nomTableau);
        }catch (\Exception $e){
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->rediriger("mise_a_jour_tableau", ["idTableau" => $idTableau]);
        }
        return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    #[Route(path: '/tableau/{idTableau}/ajout-membre', name:'ajout_membre', methods:["GET"])]
    public function afficherFormulaireAjoutMembre($idTableau): Response {
        if(! $this->estConnecte()) {
            return $this->rediriger("connexion");
        }
        try{
            $tableau = $this->tableauService->verifierProprietaire($this->connexionUtilisateurSession->getIdUtilisateurConnecte() , $idTableau);
            $utilisateurs = $this->tableauService->recupererUtilisateursPasMembreOuPasProprietaireTableau($tableau);
        }catch (\Exception $e){
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->rediriger("accueil");
        }
        return $this->afficherTwig("tableau/formulaireAjoutMembreTableau.html.twig", ["tableau" => $tableau, "utilisateurs" => $utilisateurs]);
    }

    #[Route(path: '/tableau/ajout-membre', name:'ajouter_membre', methods:["POST"])]
    public function ajouterMembre(): Response {
        if(! $this->estConnecte()) {
            return $this->rediriger("connexion");
        }
        try{
            $tableau = $this->tableauService->ajouterMembre($_POST["idTableau"], $this->connexionUtilisateurSession->getIdUtilisateurConnecte() ,$_POST["login"]);
        }catch (\Exception $e){
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->rediriger("mes_tableaux");
        }
        return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    #[Route(path: '/tableau/{idTableau}/supprimer-membre/{login}', name:'supprimer_membre', methods:["GET"])]
    public function supprimerMembre($idTableau, $login): Response {
        if(! $this->estConnecte()) {
            return $this->rediriger("connexion");
        }
        try{
            $tableau = $this->tableauService->supprimerMembre($idTableau, $this->connexionUtilisateurSession->getIdUtilisateurConnecte() , $login);
        }catch (\Exception $e){
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->rediriger("accueil");
        }
        return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    #[Route(path: '/mes-tableaux', name:'mes_tableaux', methods:["GET"])]
    public function afficherListeMesTableaux() : Response {
        if(! $this->estConnecte()) {
            return $this->rediriger("connexion");
        }
        try{
            $tableaux = $this->utilisateurService->recupererTableauxOuUtilisateurEstMembre($this->connexionUtilisateurSession->getIdUtilisateurConnecte());
        } catch (\Exception $e){
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->rediriger("accueil");
        }
        return $this->afficherTwig("tableau/listeTableauxUtilisateur.html.twig", ["tableaux" => $tableaux]);
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
        $repository = $this->tableauRepository;
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
        $repository = $this->tableauRepository;
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