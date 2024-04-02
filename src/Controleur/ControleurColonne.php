<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\ConnexionUtilisateurSession;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Service\ColonneServiceInterface;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\TableauServiceInterface;

use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Classe ControleurColonne
 *
 * Cette classe est responsable de la gestion des colonnes dans l'application Trellotrolle.
 * Elle contient des méthodes pour supprimer une colonne, créer une colonne, mettre à jour une colonne, etc.
 * Chaque méthode est associée à une route spécifique et est appelée lorsque l'utilisateur effectue une action correspondante.
 * Les méthodes utilisent les services ColonneServiceInterface et TableauServiceInterface pour effectuer les opérations nécessaires.
 * La classe utilise également la classe ConnexionUtilisateurSession pour gérer la connexion de l'utilisateur.
 *
 * @package App\Trellotrolle\Controleur
 */
class ControleurColonne extends ControleurGenerique
{

    /**
     * Constructeur de la classe.
     *
     * @param ContainerInterface $container L'instance du conteneur de dépendances.
     * @param ConnexionUtilisateurSession $session L'instance de la classe ConnexionUtilisateurSession.
     * @param ColonneServiceInterface $colonneService L'instance de l'interface ColonneServiceInterface.
     * @param TableauServiceInterface $tableauService L'instance de l'interface TableauServiceInterface.
     */
    public function __construct(ContainerInterface                           $container,
                                private readonly ConnexionUtilisateurSession $session,
                                private readonly ColonneServiceInterface     $colonneService,
                                private readonly TableauServiceInterface     $tableauService)
    {
        parent::__construct($container);
    }

    /**
     * Méthode supprimerColonne
     *
     * Cette méthode supprime une colonne en fonction de son identifiant.
     * Si l'utilisateur n'est pas connecté, il est redirigé vers la page de connexion.
     * Après la suppression de la colonne, l'utilisateur est redirigé vers la page d'affichage du tableau auquel la colonne appartenait.
     * En cas d'erreur lors de la suppression de la colonne, un message d'erreur est affiché et l'utilisateur est redirigé vers la page de ses tableaux.
     *
     * @param int $idColonne L'identifiant de la colonne à supprimer
     * @return Response La réponse HTTP de la redirection
     */
    #[Route(path: '/colonne/{idColonne}/supprimer', name: 'supprimer_colonne', methods: ["GET"])]
    public function supprimerColonne(int $idColonne): Response
    {
        if (!$this->session->estConnecte()) {
            return $this->rediriger("connexion");
        }

        $tableau = null;

        try {
            $colonne = $this->colonneService->getColonne($idColonne);
            $this->colonneService->supprimerColonne($idColonne, $this->session->getIdUtilisateurConnecte());
            $tableau = $this->tableauService->getByIdTableau($colonne->getTableau()->getIdTableau());
            MessageFlash::ajouter("success", "La colonne '" . $colonne->getTitreColonne() . "' a été supprimée !");
            return $this->rediriger("afficher_tableau", ['codeTableau' => $tableau->getCodeTableau()]);
        } catch (ServiceException $exception) {
            MessageFlash::ajouter("danger", $exception->getMessage());
            if ($tableau === null) {
                return $this->rediriger("mes_tableaux");
            }
            return $this->rediriger("afficher_tableau", ['codeTableau' => $tableau->getCodeTableau()]);
        }
    }

    /**
     * Méthode afficherFormulaireCreationColonne
     *
     * Cette méthode affiche le formulaire de création d'une colonne.
     *
     * @return Response La réponse HTTP contenant le formulaire de création de colonne
     *
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    #[Route(path: '/colonne/creation', name: 'creation_colonne', methods: ["GET"])]
    public function afficherFormulaireCreationColonne(): Response
    {
        if (!$this->session->estConnecte()) {
            return $this->rediriger("connexion");
        }

        $idTableau = $_GET['idTableau'] ?? null;

        try {
            $this->tableauService->verifierParticipant($this->session->getIdUtilisateurConnecte(), $idTableau);
            return $this->afficherTwig('colonne/formulaireCreationColonne.html.twig', [
                "idTableau" => $idTableau
            ]);
        } catch (ServiceException $exception) {
            MessageFlash::ajouter("danger", $exception->getMessage());
            return $this->rediriger("mes_tableaux");
        }

    }

    /**
     * Méthode creerColonne
     *
     * Cette méthode crée une colonne dans un tableau.
     *
     * @return Response
     *
     * @throws Exception si une erreur inattendue se produit
     */
    #[Route(path: '/colonne/creation', name: 'creer_colonne', methods: ["POST"])]
    public function creerColonne(): Response
    {
        if (!$this->session->estConnecte()) {
            return $this->rediriger("connexion");
        }

        $idTableau = $_POST['idTableau'] ?? null;
        $nomColonne = $_POST['nomColonne'] ?? null;

        $tableau = null;

        try {
            $tableau = $this->tableauService->getByIdTableau($idTableau);
            $this->colonneService->creerColonne($tableau->getIdTableau(), $nomColonne,
                $this->session->getIdUtilisateurConnecte());

            MessageFlash::ajouter("success", "La colonne '$nomColonne' a été créée !");
            return $this->rediriger("afficher_tableau", ['codeTableau' => $tableau->getCodeTableau()]);
        } catch (ServiceException|Exception $exception) {
            MessageFlash::ajouter("danger", $exception->getMessage());
            if ($tableau === null) {
                return $this->rediriger("mes_tableaux");
            }
            return $this->rediriger("afficher_tableau", ['codeTableau' => $tableau->getCodeTableau()]);
        }

    }

    /**
     * Affiche le formulaire de mise à jour d'une colonne.
     *
     * @param int $idColonne L'identifiant de la colonne.
     * @return Response La réponse HTTP.
     */
    #[Route(path: '/colonne/{idColonne}/mise-a-jour', name: 'mise_a_jour_colonne', methods: ["GET"])]
    public function afficherFormulaireMiseAJourColonne(int $idColonne): Response
    {
        if (!$this->session->estConnecte()) {
            return $this->rediriger("connexion");
        }

        $tableau = null;

        try {
            $colonne = $this->colonneService->getColonne($idColonne);
            $tableau = $this->tableauService->getByIdTableau($colonne->getTableau()->getIdTableau());
            return $this->afficherTwig('colonne/formulaireMiseAJourColonne.html.twig', [
                "colonne" => $colonne
            ]);
        } catch (ServiceException|Exception $exception) {
            MessageFlash::ajouter("danger", $exception->getMessage());
            if ($tableau === null) {
                return $this->rediriger("mes_tableaux");
            }
            return $this->rediriger("afficher_tableau", ['codeTableau' => $tableau->getCodeTableau()]);
        }

    }

    /**
     * Méthode mettreAJourColonne
     *
     * Cette méthode est utilisée pour mettre à jour une colonne.
     * Elle vérifie d'abord si l'utilisateur est connecté, sinon redirige vers la page de connexion.
     * Ensuite, elle récupère les paramètres de la requête POST (idColonne et nomColonne).
     * Elle appelle ensuite le service de colonne pour récupérer la colonne correspondante.
     * Elle renomme la colonne avec le nouveau nom en utilisant le service de colonne.
     * Enfin, elle redirige vers la page d'affichage du tableau correspondant avec un message flash de succès.
     * En cas d'erreur, elle affiche un message flash d'erreur et redirige vers la page appropriée en fonction de l'erreur.
     *
     * @return Response
     */
    #[Route(path: '/colonne/mise_a_jour', name: 'mettre_a_jour_colonne', methods: ["POST"])]
    public function mettreAJourColonne(): Response
    {
        if (!$this->session->estConnecte()) {
            return $this->rediriger("connexion");
        }

        $idColonne = $_POST['idColonne'] ?? null;
        $nomColonne = $_POST['nomColonne'] ?? null;

        try {
            $colonne = $this->colonneService->getColonne($idColonne);
            $ancienNom = $colonne->getTitreColonne();
            $this->colonneService->mettreAJour($idColonne, $nomColonne, $this->session->getIdUtilisateurConnecte());
            $tableau = $this->tableauService->getByIdTableau($colonne->getTableau()->getIdTableau());
            MessageFlash::ajouter("success", "La colonne '$ancienNom' a été renommée en '$nomColonne' !");
            return $this->rediriger("afficher_tableau", ['codeTableau' => $tableau->getCodeTableau()]);
        } catch (ServiceException|Exception $exception) {
            MessageFlash::ajouter("danger", $exception->getMessage());
            if ($idColonne === null) {
                return $this->rediriger("mes_tableaux");
            }
            return $this->rediriger("mise_a_jour_colonne", ["idColonne" => $idColonne]);
        }

    }

}