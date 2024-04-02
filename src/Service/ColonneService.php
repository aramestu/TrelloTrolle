<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\Repository\ColonneRepositoryInterface;
use App\Trellotrolle\Service\Exception\ServiceException;
use Exception;
use Symfony\Component\HttpFoundation\Response;

/**
 * Classe ColonneService
 *
 * Cette classe implémente l'interface ColonneServiceInterface et fournit les fonctionnalités liées aux colonnes d'un tableau.
 *
 * @package App\Trellotrolle\Service
 */
class ColonneService implements ColonneServiceInterface
{

    /**
     * Constructeur de la classe.
     *
     * @param ColonneRepositoryInterface $colonneRepository L'instance de l'interface ColonneRepositoryInterface.
     * @param TableauServiceInterface $tableauService L'instance de l'interface TableauServiceInterface.
     */
    public function __construct(private readonly ColonneRepositoryInterface $colonneRepository,
                                private readonly TableauServiceInterface    $tableauService)
    {
    }

    /**
     * Récupère une colonne en fonction de son identifiant.
     *
     * @param int|null $idColonne L'identifiant de la colonne.
     * @return Colonne La colonne récupérée.
     * @throws ServiceException Si la colonne n'existe pas.
     */
    public function getColonne(?int $idColonne): Colonne
    {
        $this->verifierIdColonneCorrect($idColonne);
        /**
         * @var Colonne $colonne
         */
        $colonne = $this->colonneRepository->recupererParClePrimaire($idColonne);

        if (is_null($colonne)) {
            throw new ServiceException("La colonne n'existe pas", Response::HTTP_NOT_FOUND);
        }
        return $colonne;
    }

    /**
     * Vérifie si l'identifiant de la colonne est correct.
     *
     * @param int|null $idColonne L'identifiant de la colonne.
     * @throws ServiceException Si l'identifiant de la colonne n'est pas renseigné.
     * @return void
     */
    private function verifierIdColonneCorrect(?int $idColonne): void
    {
        if (is_null($idColonne)) {
            throw new ServiceException("La colonne n'est pas renseigné", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Vérifie si l'identifiant du tableau est renseigné.
     *
     * @param int|null $idTableau L'identifiant du tableau.
     * @throws ServiceException Si le tableau n'est pas renseigné.
     * @return void
     */
    private function verifierIdTableau(?int $idTableau): void
    {
        if (is_null($idTableau)) {
            throw new ServiceException("Le tableau n'est pas renseigné", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Supprime une colonne.
     *
     * @param int|null $idColonne L'identifiant de la colonne à supprimer.
     * @param string|null $loginUtilisateurConnecte Le login de l'utilisateur connecté.
     * @throws ServiceException Si l'utilisateur n'a pas les droits nécessaires.
     * @return void
     */
    public function supprimerColonne(?int $idColonne, ?string $loginUtilisateurConnecte): void
    {
        $this->verifierIdColonneCorrect($idColonne);

        $colonne = $this->getColonne($idColonne);

        $tableau = $this->tableauService->getByIdTableau($colonne->getTableau()->getIdTableau());

        if (!$tableau->estParticipantOuProprietaire($loginUtilisateurConnecte)) {
            throw new ServiceException("Vous n'avez pas les droits nécessaires", Response::HTTP_UNAUTHORIZED);
        }

        $this->colonneRepository->supprimer($idColonne);
    }

    /**
     * Vérifie si le nom de la colonne est correct.
     *
     * @param string|null $nomColonne Le nom de la colonne à vérifier.
     * @return void
     * @throws ServiceException Si le nom de la colonne est null, vide ou dépasse 64 caractères.
     */
    private function verifierNomColonneCorrect(?string $nomColonne): void
    {
        $nb = strlen($nomColonne);
        if (is_null($nomColonne) || $nb == 0 || $nb > 64) {
            throw new ServiceException("Le nom de la colonne ne peut pas faire plus de 64 caractères et doit être renseigné", Response::HTTP_BAD_REQUEST);
        }
    }


    /**
     * Crée une colonne dans un tableau.
     *
     * @param int|null $idTableau L'identifiant du tableau.
     * @param string|null $nomColonne Le nom de la colonne.
     * @param string|null $loginUtilisateurConnecte Le login de l'utilisateur connecté.
     * @return int L'identifiant de la colonne créée.
     * @throws ServiceException Si une erreur de service se produit.
     * @throws Exception Si une erreur générale se produit.
     */
    public function creerColonne(?int $idTableau, ?string $nomColonne, ?string $loginUtilisateurConnecte): int
    {
        $this->verifierNomColonneCorrect($nomColonne);
        $this->verifierIdTableau($idTableau);

        $tableau = $this->tableauService->getByIdTableau($idTableau);

        if (!$tableau->estParticipantOuProprietaire($loginUtilisateurConnecte)) {
            throw new ServiceException("Vous devez être participant au tableau pour pouvoir créer une Colonne!", Response::HTTP_UNAUTHORIZED);
        }

        $colonne = Colonne::create(1, $nomColonne, $tableau);
        $this->colonneRepository->ajouter($colonne);
        return $this->colonneRepository->lastInsertId();
    }

    /**
     * Met à jour le titre d'une colonne.
     *
     * @param int|null $idColonne L'identifiant de la colonne à mettre à jour.
     * @param string|null $nomColonne Le nouveau titre de la colonne.
     * @param string|null $loginUtilisateurConnecte Le login de l'utilisateur connecté.
     * @return Colonne La colonne mise à jour.
     * @throws ServiceException Si l'utilisateur n'a pas les droits nécessaires.
     */
    public function mettreAJour(?int $idColonne, ?string $nomColonne, ?string $loginUtilisateurConnecte): Colonne
    {
        $this->verifierNomColonneCorrect($nomColonne);
        $this->verifierIdColonneCorrect($idColonne);

        $colonne = $this->getColonne($idColonne);

        $tableau = $this->tableauService->getByIdTableau($colonne->getTableau()->getIdTableau());

        if (!$tableau->estParticipantOuProprietaire($loginUtilisateurConnecte)) {
            throw new ServiceException("Vous n'avez pas les droits nécessaires", Response::HTTP_UNAUTHORIZED);
        }

        $colonne->setTitreColonne($nomColonne);
        $this->colonneRepository->mettreAJour($colonne);

        return $colonne;
    }

    /**
     * Méthode pour récupérer les colonnes d'un tableau.
     *
     * @param int|null $idTableau L'identifiant du tableau.
     * @return array Les colonnes du tableau.
     * @throws ServiceException
     */
    public function recupererColonnesTableau(int|null $idTableau): array
    {
        $this->verifierIdTableau($idTableau);
        return $this->colonneRepository->recupererColonnesTableau($idTableau);
    }
}