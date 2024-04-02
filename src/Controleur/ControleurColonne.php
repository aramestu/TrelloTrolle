<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Lib\ConnexionUtilisateurSession;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\ColonneRepository;
use App\Trellotrolle\Modele\Repository\TableauRepository;
use App\Trellotrolle\Service\ColonneService;
use App\Trellotrolle\Service\ColonneServiceInterface;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\TableauServiceInterface;
use PHPUnit\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ControleurColonne extends ControleurGenerique
{

    public function __construct(ContainerInterface $container,
                                private readonly ConnexionUtilisateurSession $session,
                                private readonly ColonneServiceInterface $colonneService,
                                private readonly TableauServiceInterface $tableauService)
    {
        parent::__construct($container);
    }

    #[Route(path: '/colonne/{idColonne}/supprimer', name:'supprimer_colonne', methods:["GET"])]
    public function supprimerColonne($idColonne): Response
    {
        if(!$this->session->estConnecte())
        {
            return $this->rediriger("connexion");
        }

        $tableau = null;

        try
        {
            $colonne = $this->colonneService->getColonne($idColonne);
            $this->colonneService->supprimerColonne($idColonne, $this->session->getIdUtilisateurConnecte());
            $tableau = $this->tableauService->getByIdTableau($colonne->getTableau()->getIdTableau());
            MessageFlash::ajouter("success", "La colonne '" . $colonne->getTitreColonne() . "' a été supprimée !");
            return $this->rediriger("afficher_tableau", ['codeTableau' => $tableau->getCodeTableau()]);
        }
        catch(ServiceException $exception)
        {
            MessageFlash::ajouter("danger", $exception->getMessage());
            if($tableau === null)
            {
                return $this->rediriger("mes_tableaux");
            }
            return $this->rediriger("afficher_tableau", ['codeTableau' => $tableau->getCodeTableau()]);
        }
    }

    #[Route(path: '/colonne/creation', name:'creation_colonne', methods:["GET"])]
    public function afficherFormulaireCreationColonne(): Response
    {
        if(!$this->session->estConnecte())
        {
            return $this->rediriger("connexion");
        }

        $idTableau = $_GET['idTableau'] ?? null;

        try
        {
            $this->tableauService->verifierParticipant($this->session->getIdUtilisateurConnecte(), $idTableau);
            return $this->afficherTwig('colonne/formulaireCreationColonne.html.twig', [
                "idTableau" => $idTableau
            ]);
        }
        catch(ServiceException $exception)
        {
            MessageFlash::ajouter("danger", $exception->getMessage());
            return $this->rediriger("mes_tableaux");
        }

    }

    #[Route(path: '/colonne/creation', name:'creer_colonne', methods:["POST"])]
    public function creerColonne(): Response
    {
        if(!$this->session->estConnecte())
        {
            return $this->rediriger("connexion");
        }

        $idTableau = $_POST['idTableau'] ?? null;
        $nomColonne = $_POST['nomColonne'] ?? null;

        $tableau = null;

        try
        {
            $tableau = $this->tableauService->getByIdTableau($idTableau);
            $this->colonneService->creerColonne($tableau->getIdTableau(), $nomColonne,
                $this->session->getIdUtilisateurConnecte());

            MessageFlash::ajouter("success", "La colonne '$nomColonne' a été créée !");
            return $this->rediriger("afficher_tableau", ['codeTableau' => $tableau->getCodeTableau()]);
        }
        catch(ServiceException | \Exception $exception)
        {
            MessageFlash::ajouter("danger", $exception->getMessage());
            if($tableau === null)
            {
                return $this->rediriger("mes_tableaux");
            }
            return $this->rediriger("afficher_tableau", ['codeTableau' => $tableau->getCodeTableau()]);
        }

    }

    #[Route(path: '/colonne/{idColonne}/mise-a-jour', name:'mise_a_jour_colonne', methods:["GET"])]
    public function afficherFormulaireMiseAJourColonne($idColonne): Response
    {
        if(!$this->session->estConnecte())
        {
            return $this->rediriger("connexion");
        }

        $tableau= null;

        try
        {
            $colonne = $this->colonneService->getColonne($idColonne);
            $tableau = $this->tableauService->getByIdTableau($colonne->getTableau()->getIdTableau());
            return $this->afficherTwig('colonne/formulaireMiseAJourColonne.html.twig', [
                "colonne" => $colonne
            ]);
        }
        catch(ServiceException | \Exception $exception)
        {
            MessageFlash::ajouter("danger", $exception->getMessage());
            if($tableau === null)
            {
                return $this->rediriger("mes_tableaux");
            }
            return $this->rediriger("afficher_tableau", ['codeTableau' => $tableau->getCodeTableau()]);
        }

    }

    #[Route(path: '/colonne/mise_a_jour', name:'mettre_a_jour_colonne', methods:["POST"])]
    public function mettreAJourColonne(): Response
    {
        if(!$this->session->estConnecte())
        {
            return $this->rediriger("connexion");
        }

        $idColonne = $_POST['idColonne'] ?? null;
        $nomColonne = $_POST['nomColonne'] ?? null;

        try
        {
            $colonne = $this->colonneService->getColonne($idColonne);
            $ancienNom = $colonne->getTitreColonne();
            $this->colonneService->mettreAJour($idColonne, $nomColonne, $this->session->getIdUtilisateurConnecte());
            $tableau = $this->tableauService->getByIdTableau($colonne->getTableau()->getIdTableau());
            MessageFlash::ajouter("success", "La colonne '$ancienNom' a été renommée en '$nomColonne' !");
            return $this->rediriger("afficher_tableau", ['codeTableau' => $tableau->getCodeTableau()]);
        }
        catch(ServiceException | \Exception $exception)
        {
            MessageFlash::ajouter("danger", $exception->getMessage());
            if($idColonne === null)
            {
                return $this->rediriger("mes_tableaux");
            }
            return $this->rediriger("mise_a_jour_colonne", ["idColonne" => $idColonne]);
        }

    }

}