<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\Repository\CarteRepositoryInterface;
use App\Trellotrolle\Service\Exception\ServiceException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Classe CarteService
 *
 * Cette classe implémente l'interface CarteServiceInterface et fournit des méthodes pour gérer les cartes dans l'application Trellotrolle.
 * Elle utilise les services CarteRepositoryInterface, ColonneServiceInterface et TableauServiceInterface pour effectuer les opérations nécessaires.
 *
 * @package App\Trellotrolle\Service
 */
class CarteService implements CarteServiceInterface
{
    public function __construct(private readonly CarteRepositoryInterface $carteRepository,
                                private readonly ColonneServiceInterface  $colonneService,
                                private readonly TableauServiceInterface  $tableauService)
    {
    }

    /**
     * Récupère une carte en fonction de son identifiant.
     *
     * @param int|null $idCarte L'identifiant de la carte.
     * @return Carte|null La carte correspondante, ou null si elle n'existe pas.
     * @throws ServiceException Si la carte n'existe pas.
     */
    public function getCarte(?int $idCarte): ?Carte
    {
        $this->verifierIdCarteCorrect($idCarte);

        /**
         * @var Carte $carte
         */
        $carte = $this->carteRepository->recupererParClePrimaire($idCarte);

        if (is_null($carte)) {
            throw new ServiceException("La carte n'existe pas", Response::HTTP_NOT_FOUND);
        }
        return $carte;
    }

    /**
     * Vérifie si l'identifiant de la carte est correct.
     *
     * @param int|null $idCarte
     * @return void
     * @throws ServiceException Si la carte n'est pas renseignée.
     */
    private function verifierIdCarteCorrect(?int $idCarte): void
    {
        if (is_null($idCarte)) {
            throw new ServiceException("La carte n'est pas renseigné", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Vérifie si le titre de la carte est correct.
     *
     * @param string|null $titreCarte Le titre de la carte.
     * @return void
     * @throws ServiceException Si le titre de la carte est null, vide ou dépasse 64 caractères.
     */
    private function verifierTitreCarteCorrect(?string $titreCarte): void
    {
        $nb = strlen($titreCarte);
        if (is_null($titreCarte) || $nb == 0 || $nb > 64) {
            throw new ServiceException("Le nom de la carte ne peut pas faire plus de 64 caractères et doit être renseigné", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Vérifie si le descriptif de la carte est correct.
     *
     * @param string|null $descriptifCarte Le descriptif de la carte.
     * @throws ServiceException Si le descriptif de la carte n'est pas renseigné.
     */
    private function verifierDescriptifCarteCorrect(?string $descriptifCarte): void
    {
        $nb = strlen($descriptifCarte);
        if (is_null($descriptifCarte) || $nb == 0) {
            throw new ServiceException("La description de la carte doit être renseigné", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Vérifie si la couleur de la carte est correcte.
     *
     * @param string|null $couleurCarte La couleur de la carte.
     * @return void
     * @throws ServiceException Si la couleur de la carte ne peut pas faire plus de 7 caractères et doit être renseignée.
     */
    private function verifierCouleurCarteCorrect(?string $couleurCarte): void
    {
        $nb = strlen($couleurCarte);
        if (is_null($couleurCarte) || $nb == 0 || $nb > 7) {
            throw new ServiceException("La couleur de la carte ne peut pas faire plus de 7 caractères et doit être renseigné", Response::HTTP_BAD_REQUEST);
        }
    }

    //A vérifier potentielement source d'erreur

    /**
     * Vérifie si les affectations sont correctes pour un tableau donné.
     *
     * @param array|null $affectations Les affectations à vérifier.
     * @param Tableau $tableau Le tableau concerné.
     * @return void
     * @throws ServiceException Si l'un des membres n'est pas affecté au tableau ou n'existe pas.
     */
    private function verifierAffectationsCorrect(?array $affectations, Tableau $tableau): void
    {
        $loginsParticipantsTableau = [];
        foreach ($tableau->getParticipants() as $participant) {
            $loginsParticipantsTableau[] = $participant->getLogin();
        }

        foreach ($affectations as $loginAffectation) {
            if ($loginAffectation != $tableau->getProprietaireTableau()->getLogin() && !in_array($loginAffectation, $loginsParticipantsTableau)) {
                throw new ServiceException("L'un des membres n'est pas affecté au tableau ou n'existe pas", Response::HTTP_BAD_REQUEST);
            }
        }
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
     * Supprime une carte.
     *
     * @param int|null $idCarte L'identifiant de la carte à supprimer.
     * @param string|null $loginUtilisateurConnecte Le login de l'utilisateur connecté.
     * @return Tableau Le tableau auquel la carte appartenait.
     * @throws ServiceException Si l'utilisateur n'a pas les droits nécessaires.
     */
    public function supprimerCarte(?int $idCarte, ?string $loginUtilisateurConnecte): Tableau
    {
        $carte = $this->getCarte($idCarte);
        $colonne = $this->colonneService->getColonne($carte->getColonne()->getIdColonne());
        $tableau = $this->tableauService->getByIdTableau($colonne->getTableau()->getIdTableau());

        if (!$tableau->estParticipantOuProprietaire($loginUtilisateurConnecte)) {
            throw new ServiceException("Vous n'avez pas les droits nécessaires!", Response::HTTP_UNAUTHORIZED);
        }

        $this->carteRepository->supprimer($idCarte);

        return $tableau;
    }

    /**
     * Crée une carte dans une colonne spécifiée avec les informations fournies.
     *
     * @param int|null $idColonne L'identifiant de la colonne dans laquelle créer la carte.
     * @param string|null $titreCarte Le titre de la carte.
     * @param string|null $descriptifCarte Le descriptif de la carte.
     * @param string|null $couleurCarte La couleur de la carte.
     * @param string|null $loginUtilisateurConnecte Le login de l'utilisateur connecté.
     * @param array|null $affectations Les affectations de la carte.
     * @return int L'identifiant de la carte créée.
     * @throws ServiceException Si une erreur survient lors de la création de la carte.
     */
    public function creerCarte(?int $idColonne, ?string $titreCarte, ?string $descriptifCarte, ?string $couleurCarte, ?string $loginUtilisateurConnecte, ?array $affectations): int
    {
        $this->verifierTitreCarteCorrect($titreCarte);
        $this->verifierDescriptifCarteCorrect($descriptifCarte);
        $this->verifierCouleurCarteCorrect($couleurCarte);
        $this->verifierIdColonneCorrect($idColonne);

        $colonne = $this->colonneService->getColonne($idColonne);
        $tableau = $this->tableauService->getByIdTableau($colonne->getTableau()->getIdTableau());

        $this->verifierAffectationsCorrect($affectations, $tableau);

        if (!$tableau->estParticipantOuProprietaire($loginUtilisateurConnecte)) {
            throw new ServiceException("Vous n'avez pas les droits nécessaires", Response::HTTP_UNAUTHORIZED);
        }

        $carte = Carte::create(1, $titreCarte, $descriptifCarte, $couleurCarte, $colonne, $affectations ?? []);
        $this->carteRepository->ajouter($carte);
        $idCarte = $this->carteRepository->lastInsertId();
        foreach ($affectations as $login) {
            $this->carteRepository->ajouterAffectation($login, $idCarte);
        }

        return $idCarte;
    }

    /**
     * Met à jour une carte avec les nouvelles informations fournies.
     *
     * @param int|null $idCarte L'identifiant de la carte à mettre à jour.
     * @param int|null $idColonne L'identifiant de la nouvelle colonne de la carte.
     * @param string|null $titreCarte Le nouveau titre de la carte.
     * @param string|null $descriptifCarte Le nouveau descriptif de la carte.
     * @param string|null $couleurCarte La nouvelle couleur de la carte.
     * @param string|null $loginUtilisateurConnecte Le login de l'utilisateur connecté.
     * @param array|null $affectations Les nouvelles affectations de la carte.
     * @return Carte La carte mise à jour.
     * @throws ServiceException Si une erreur se produit lors de la mise à jour de la carte.
     */
    public function mettreAJourCarte(?int $idCarte, ?int $idColonne, ?string $titreCarte, ?string $descriptifCarte, ?string $couleurCarte, ?string $loginUtilisateurConnecte, ?array $affectations): Carte
    {
        $this->verifierIdCarteCorrect($idCarte);
        $this->verifierIdColonneCorrect($idColonne);
        $this->verifierTitreCarteCorrect($titreCarte);
        $this->verifierDescriptifCarteCorrect($descriptifCarte);
        $this->verifierCouleurCarteCorrect($couleurCarte);

        $carte = $this->getCarte($idCarte);
        $colonne = $this->colonneService->getColonne($idColonne);
        $originalColonne = $this->colonneService->getColonne($carte->getColonne()->getIdColonne());
        $tableau = $this->tableauService->getByIdTableau($colonne->getTableau()->getIdTableau());

        $this->verifierAffectationsCorrect($affectations, $tableau);

        // Si les colonnes ne sont pas dans le même tableau
        if ($colonne->getTableau()->getIdTableau() !== $originalColonne->getTableau()->getIdTableau()) {
            throw new ServiceException("La nouvelle colonne n'appartient pas au bon tableau", Response::HTTP_BAD_REQUEST);
        }

        if (!$tableau->estParticipantOuProprietaire($loginUtilisateurConnecte)) {
            throw new ServiceException("Vous n'avez pas les droits nécessaires", Response::HTTP_UNAUTHORIZED);
        }

        $carte->setTitreCarte($titreCarte);
        $carte->setDescriptifCarte($descriptifCarte);
        $carte->setCouleurCarte($couleurCarte);
        $carte->setColonne($colonne);
        $this->carteRepository->mettreAJour($carte);
        $idCarte = $carte->getIdCarte();
        $this->carteRepository->supprimerToutesAffectationsCarte($idCarte);
        foreach ($affectations as $login) {
            $this->carteRepository->ajouterAffectation($login, $idCarte);
        }

        return $carte;
    }
}