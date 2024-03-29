<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\Repository\CarteRepositoryInterface;
use App\Trellotrolle\Modele\Repository\ColonneRepositoryInterface;
use App\Trellotrolle\Modele\Repository\TableauRepositoryInterface;
use App\Trellotrolle\Modele\Repository\UtilisateurRepositoryInterface;
use App\Trellotrolle\Service\Exception\ServiceException;
use Symfony\Component\HttpFoundation\Response;

class CarteService
{
    public function __construct(private CarteRepositoryInterface  $carteRepository,
                                private ColonneRepositoryInterface $colonneRepository,
                                private TableauRepositoryInterface $tableauRepository,
                                private ColonneServiceInterface $colonneService,
                                private TableauServiceInterface $tableauService) {}

    /**
     * @throws ServiceException
     */
    public function getCarte(?int $idCarte): ?Carte{
        if(is_null($idCarte)){
            throw new ServiceException( "La carte n'est pas renseigné", Response::HTTP_NOT_FOUND);
        }

        /**
         * @var Carte $carte
         */
        $carte = $this->carteRepository->recupererParClePrimaire($idCarte);

        if(is_null($carte)){
            throw new ServiceException( "La carte n'existe pas", Response::HTTP_NOT_FOUND);
        }
        return $carte;
    }

    /**
     * @throws ServiceException
     */
    private function verifierIdCarteCorrect(?int $idTableau): void{
        if(is_null($idTableau)){
            throw new ServiceException( "La carte n'est pas renseigné", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @throws ServiceException
     */
    private function verifierTitreCarteCorrect(?string $titreCarte) {
        $nb = strlen($titreCarte);
        if(is_null($titreCarte) || $nb == 0 || $nb > 64){
            throw new ServiceException( "Le nom de la carte ne peut pas faire plus de 64 caractères et doit être renseigné", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @throws ServiceException
     */
    private function verifierDescriptifCarteCorrect(?string $descriptifCarte) {
        $nb = strlen($descriptifCarte);
        if(is_null($descriptifCarte) || $nb == 0){
            throw new ServiceException( "La description de la carte doit être renseigné", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @throws ServiceException
     */
    private function verifierCouleurCarteCorrect(?string $couleurCarte) {
        $nb = strlen($couleurCarte);
        if(is_null($couleurCarte) || $nb == 0 || $nb > 7){
            throw new ServiceException( "La couleur de la carte ne peut pas faire plus de 7 caractères et doit être renseigné", Response::HTTP_BAD_REQUEST);
        }
    }

    //A vérifier potentielement source d'erreur
    /**
     * @throws ServiceException
     */
    private function verifierAffectationsCorrect(?array $affectations, ?array $affectationsTableau) {
        if(is_null($affectations)) {
            throw new ServiceException( "Les affectations doit être renseigné", Response::HTTP_BAD_REQUEST);
        }
        foreach ($affectations as $affectation) {
            if (!in_array($affectation, $affectationsTableau)) {
                throw new ServiceException( "L'un des membres n'est pas affecté au tableau ou n'existe pas", Response::HTTP_BAD_REQUEST);
            }
        }
    }

    /**
     * @throws ServiceException
     */
    public function supprimerCarte(?int $idCarte, ?string $loginUtilisateurConnecte) {
        $this->verifierIdCarteCorrect($idCarte);

        $carte = $this->getCarte($idCarte);
        $colonne = $this->colonneService->getColonne($carte->getColonne());
        $tableau = $this->tableauService->getByIdTableau($colonne->getIdTableau());

        if(! $tableau->estParticipantOuProprietaire($loginUtilisateurConnecte)){
            throw new ServiceException( "Vous n'avez pas les droits nécessaires", Response::HTTP_UNAUTHORIZED);
        }

        $this->carteRepository->supprimer($idCarte);
    }

    public function creerCarte(?int $idCarte, ?string $titreCarte, ?string $descriptifCarte, ?string $couleurCarte, ?string $loginUtilisateurConnecte, ?array $affectations) {
        $this->verifierIdCarteCorrect($idCarte);
        $this->verifierTitreCarteCorrect($titreCarte);
        $this->verifierDescriptifCarteCorrect($descriptifCarte);
        $this->verifierCouleurCarteCorrect($couleurCarte);

        $carte = $this->getCarte($idCarte);
        $colonne = $this->colonneService->getColonne($carte->getColonne());
        $tableau = $this->tableauService->getByIdTableau($colonne->getIdTableau());

        if(! $tableau->estParticipantOuProprietaire($loginUtilisateurConnecte)){
            throw new ServiceException( "Vous n'avez pas les droits nécessaires", Response::HTTP_UNAUTHORIZED);
        }
    }
}