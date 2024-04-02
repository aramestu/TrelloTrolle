<?php

namespace App\Trellotrolle\Controleur;


use App\Trellotrolle\Lib\ConnexionUtilisateurInterface;
use App\Trellotrolle\Lib\MessageFlash;
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

/**
 * Classe ControleurTableau
 *
 * Cette classe est responsable de la gestion des tableaux dans l'application.
 * Elle contient des méthodes pour afficher, créer, mettre à jour et supprimer des tableaux.
 * Elle gère également l'ajout et la suppression de membres dans un tableau.
 * Les méthodes de cette classe sont utilisées dans le contexte d'un contrôleur Symfony.
 */
class ControleurTableau extends ControleurGenerique
{

    /**
     * Constructeur de la classe.
     *
     * @param ContainerInterface $container L'interface du conteneur.
     * @param TableauServiceInterface $tableauService L'interface du service de tableau.
     * @param ConnexionUtilisateurInterface $connexionUtilisateurSession L'interface de la connexion utilisateur.
     * @param ColonneServiceInterface $colonneService L'interface du service de colonne.
     * @param CarteServiceInterface $carteService L'interface du service de carte.
     * @param UtilisateurServiceInterface $utilisateurService L'interface du service d'utilisateur.
     */
    public function __construct(ContainerInterface                             $container, private readonly TableauServiceInterface $tableauService,
                                private readonly ConnexionUtilisateurInterface $connexionUtilisateurSession, private readonly ColonneServiceInterface $colonneService,
                                private readonly CarteServiceInterface                  $carteService,
                                private readonly UtilisateurServiceInterface   $utilisateurService)
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
     * @return Response La réponse HTTP contenant le corps de l'erreur rendue.
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function afficherErreur(string $messageErreur = "", string $controleur = ""): Response
    {
        return parent::afficherErreur($messageErreur, "tableau");
    }

    /**
     * Méthode pour afficher un tableau.
     *
     * @param string $codeTableau Le code du tableau à afficher.
     * @return Response La réponse HTTP contenant le rendu de la vue du tableau.
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    #[Route(path: '/tableau/{codeTableau}/afficher', name: 'afficher_tableau', methods: ["GET"])]
    public function afficherTableau(string $codeTableau): Response
    {
        try {
            $tableau = $this->tableauService->getByCodeTableau($codeTableau);
            $associationColonneCarte = $this->tableauService->recupererColonnesEtCartesDuTableau($tableau->getIdTableau());
            $informationsAffectation = $this->tableauService->informationsAffectationsCartes($tableau->getIdTableau());
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->rediriger("accueil");
        }

        return $this->afficherTwig("tableau/tableau.html.twig", [
            "tableau" => $tableau,
            "associationColonneCarte" => $associationColonneCarte,
            "informationsAffectation" => $informationsAffectation
        ]);
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
     *
     * Méthode afficherFormulaireMiseAJourTableau
     *
     * Cette méthode affiche le formulaire de mise à jour d'un tableau.
     * Elle vérifie d'abord si l'utilisateur est connecté, sinon le redirige vers la page de connexion.
     * Ensuite, elle récupère le tableau correspondant à l'id donné en paramètre.
     * Elle vérifie également si l'utilisateur connecté est participant du tableau.
     * Si une exception est levée lors de ces vérifications, un message d'avertissement est ajouté aux messages flash
     * et l'utilisateur est redirigé vers la page de ses tableaux.
     * Enfin, elle affiche le formulaire de mise à jour du tableau en utilisant le template "tableau/formulaireMiseAJourTableau.html.twig".
     *
     * @param int $idTableau L'id du tableau à mettre à jour
     * @return Response La réponse HTTP contenant le formulaire de mise à jour du tableau
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    #[Route(path: '/tableau/{idTableau}/mise-a-jour', name: 'mise_a_jour_tableau', methods: ["GET"])] // Nom route modifié car elle exisait déjà pour mettreAJourTableau
    public function afficherFormulaireMiseAJourTableau(int $idTableau): Response
    {
        if (!$this->estConnecte()) {
            return $this->rediriger("connexion");
        }
        try {
            $tableau = $this->tableauService->getByIdTableau($idTableau);
            $this->tableauService->verifierParticipant($this->connexionUtilisateurSession->getIdUtilisateurConnecte(), $idTableau);
            $nomTableau = $tableau->getTitreTableau();
        } catch (\Exception $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->rediriger("mes_tableaux");
        }
        return self::afficherTwig("tableau/formulaireMiseAJourTableau.html.twig", ["tableau" => $tableau, "pagetitle" => "Mise à jour Tableau"]);
    }

    /**
     *
     * Méthode pour afficher le formulaire de création d'un tableau.
     *
     * @return Response La réponse HTTP contenant le formulaire de création de tableau.
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    #[Route(path: '/tableau/creation', name: 'creation_tableau', methods: ["GET"])]
    public function afficherFormulaireCreationTableau(): Response
    {
        if (!$this->estConnecte()) {
            return $this->rediriger("connexion");
        }
        return self::afficherTwig("tableau/formulaireCreationTableau.html.twig", ["pagetitle" => "Création Tableau"]);
    }

    /**
     * Méthode creerTableau
     *
     * Cette méthode crée un nouveau tableau.
     * Elle vérifie d'abord si l'utilisateur est connecté.
     * Si l'utilisateur n'est pas connecté, il est redirigé vers la page de connexion.
     * Ensuite, elle utilise le service TableauService pour créer le tableau en utilisant les paramètres suivants :
     * - loginUtilisateurConnecte : l'identifiant de l'utilisateur connecté
     * - nomTableau : le nom du tableau à créer (récupéré à partir de la variable POST)
     * Si une exception est levée lors de la création du tableau, un message d'avertissement est ajouté aux messages flash
     * et l'utilisateur est redirigé vers la page de création du tableau.
     * Enfin, l'utilisateur est redirigé vers la page d'affichage du tableau créé en utilisant le code du tableau retourné.
     *
     * @return Response La réponse HTTP de la redirection vers la page d'affichage du tableau créé
     */
    #[Route(path: '/tableau/creation', name: 'creer_tableau', methods: ["POST"])]
    public function creerTableau(): Response
    {
        if (!$this->estConnecte()) {
            return $this->rediriger("connexion");
        }
        try {
            $tableau = $this->tableauService->creerTableau($this->connexionUtilisateurSession->getIdUtilisateurConnecte(), $_POST["nomTableau"]);
        } catch (\Exception $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->rediriger("creation_tableau");
        }
        return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    /**
     * Méthode mettreAJourTableau
     *
     * Cette méthode est une action du contrôleur qui met à jour un tableau.
     * Elle vérifie d'abord si l'utilisateur est connecté, sinon redirige vers la page de connexion.
     * Ensuite, elle récupère les paramètres POST "idTableau" et "nomTableau".
     * Elle utilise le service TableauService pour mettre à jour le tableau avec les paramètres fournis.
     * Si une exception est levée lors de la mise à jour, un message d'avertissement est ajouté aux messages flash
     * et l'utilisateur est redirigé vers la page de mise à jour du tableau avec l'identifiant du tableau.
     * Enfin, si la mise à jour est réussie, l'utilisateur est redirigé vers la page d'affichage du tableau avec le code du tableau.
     *
     * @return Response La réponse HTTP de la redirection
     */
    #[Route(path: '/tableau/mise-a-jour', name: 'mettre_a_jour_tableau', methods: ["POST"])]
    public function mettreAJourTableau(): Response
    {
        if (!$this->estConnecte()) {
            return $this->rediriger("connexion");
        }
        $idTableau = $_POST["idTableau"] ?? null;
        $nomTableau = $_POST["nomTableau"] ?? null;
        try {
            $tableau = $this->tableauService->mettreAJourTableau($idTableau, $this->connexionUtilisateurSession->getIdUtilisateurConnecte(), $nomTableau);
        } catch (\Exception $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->rediriger("mise_a_jour_tableau", ["idTableau" => $idTableau]);
        }
        return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    /**
     * Affiche le formulaire d'ajout de membre pour un tableau spécifié.
     *
     * @param int $idTableau L'identifiant du tableau.
     * @return Response La réponse HTTP contenant le formulaire d'ajout de membre.
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    #[Route(path: '/tableau/{idTableau}/ajout-membre', name: 'ajout_membre', methods: ["GET"])]
    public function afficherFormulaireAjoutMembre(int $idTableau): Response
    {
        if (!$this->estConnecte()) {
            return $this->rediriger("connexion");
        }
        try {
            $tableau = $this->tableauService->verifierProprietaire($this->connexionUtilisateurSession->getIdUtilisateurConnecte(), $idTableau);
        } catch (\Exception $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->rediriger("accueil");
        }
        return $this->afficherTwig("tableau/formulaireAjoutMembreTableau.html.twig", ["tableau" => $tableau]);
    }

    /**
     * Méthode pour ajouter un membre à un tableau.
     *
     * @return Response La réponse HTTP de la méthode.
     */
    #[Route(path: '/tableau/ajout-membre', name: 'ajouter_membre', methods: ["POST"])]
    public function ajouterMembre(): Response
    {
        if (!$this->estConnecte()) {
            return $this->rediriger("connexion");
        }
        $idTableau = $_POST["idTableau"] ?? null;
        $login = $_POST["login"] ?? null;
        try {
            $tableau = $this->tableauService->ajouterMembre($idTableau, $this->connexionUtilisateurSession->getIdUtilisateurConnecte(), $login);
        } catch (\Exception $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->rediriger("ajout_membre", ["idTableau" => $idTableau]);
        }
        return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    /**
     * Méthode supprimerMembre
     *
     * Cette méthode supprime un membre d'un tableau spécifié.
     *
     * @param int $idTableau L'identifiant du tableau
     * @param string $login Le login du membre à supprimer
     * @return Response La réponse de la requête
     */
    #[Route(path: '/tableau/{idTableau}/supprimer-membre/{login}', name: 'supprimer_membre', methods: ["GET"])]
    public function supprimerMembre(int $idTableau, string $login): Response
    {
        if (!$this->estConnecte()) {
            return $this->rediriger("connexion");
        }
        try {
            $tableau = $this->tableauService->supprimerMembre($idTableau, $this->connexionUtilisateurSession->getIdUtilisateurConnecte(), $login);
        } catch (\Exception $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->rediriger("accueil");
        }
        return $this->rediriger("afficher_tableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    /**
     * Méthode pour afficher la liste des tableaux de l'utilisateur connecté.
     *
     * @return Response La réponse HTTP contenant la liste des tableaux de l'utilisateur.
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    #[Route(path: '/mes-tableaux', name: 'mes_tableaux', methods: ["GET"])]
    public function afficherListeMesTableaux(): Response
    {
        if (!$this->estConnecte()) {
            return $this->rediriger("connexion");
        }
        try {
            $tableaux = $this->utilisateurService->recupererTableauxOuUtilisateurEstMembre($this->connexionUtilisateurSession->getIdUtilisateurConnecte());
        } catch (\Exception $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->rediriger("accueil");
        }
        return $this->afficherTwig("tableau/listeTableauxUtilisateur.html.twig", ["tableaux" => $tableaux]);
    }

    /**
     * Méthode quitterTableau
     *
     * Cette méthode est utilisée pour quitter un tableau.
     *
     * @param string $idTableau L'identifiant du tableau à quitter
     * @return Response La réponse HTTP de redirection vers la page "mes_tableaux"
     */
    #[Route(path: '/tableau/{idTableau}/quitter', name: 'quitter_tableau', methods: ["GET"])]
    public function quitterTableau(string $idTableau): Response
    {
        if (!$this->estConnecte()) {
            MessageFlash::ajouter("warning", "Vous devez être connecté pour quitter un tableau");
            return $this->rediriger("connexion");
        }
        try {
            $this->tableauService->quitterTableau($this->connexionUtilisateurSession->getIdUtilisateurConnecte(), $idTableau);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->rediriger("mes_tableaux");
        }
        return $this->rediriger("mes_tableaux");
    }

    /**
     * Méthode supprimerTableau
     *
     * Cette méthode supprime un tableau.
     *
     * @param string $idTableau L'identifiant du tableau à supprimer.
     * @return Response La réponse HTTP de la suppression du tableau.
     */
    #[Route(path: '/tableau/{idTableau}/supprimer', name: 'supprimer_tableau', methods: ["GET"])]
    public function supprimerTableau(string $idTableau): Response
    {
        if (!$this->estConnecte()) {
            MessageFlash::ajouter("warning", "Vous devez être connecté pour supprimer un tableau");
            return $this->rediriger("connexion");
        }
        try {
            $this->tableauService->supprimer($this->connexionUtilisateurSession->getIdUtilisateurConnecte(), $idTableau);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            $this->rediriger("mes_tableaux");
        }
        return $this->rediriger("mes_tableaux");
    }
}