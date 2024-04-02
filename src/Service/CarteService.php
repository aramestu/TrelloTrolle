<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\Repository\CarteRepositoryInterface;
use App\Trellotrolle\Modele\Repository\ConnexionBaseDeDonnees;
use App\Trellotrolle\Service\Exception\ServiceException;
use Symfony\Component\HttpFoundation\Response;

class CarteService implements CarteServiceInterface
{
    public function __construct(private CarteRepositoryInterface  $carteRepository,
                                private ColonneServiceInterface $colonneService,
                                private TableauServiceInterface $tableauService) {}

    /**
     * @throws ServiceException
     */
    public function getCarte(?int $idCarte): ?Carte{
        $this->verifierIdCarteCorrect($idCarte);

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
    private function verifierIdCarteCorrect(?int $idCarte): void{
        if(is_null($idCarte)){
            throw new ServiceException( "La carte n'est pas renseigné", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @throws ServiceException
     */
    private function verifierTitreCarteCorrect(?string $titreCarte): void
    {
        $nb = strlen($titreCarte);
        if(is_null($titreCarte) || $nb == 0 || $nb > 64){
            throw new ServiceException( "Le nom de la carte ne peut pas faire plus de 64 caractères et doit être renseigné", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @throws ServiceException
     */
    private function verifierDescriptifCarteCorrect(?string $descriptifCarte): void
    {
        $nb = strlen($descriptifCarte);
        if(is_null($descriptifCarte) || $nb == 0){
            throw new ServiceException( "La description de la carte doit être renseigné", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @throws ServiceException
     */
    private function verifierCouleurCarteCorrect(?string $couleurCarte): void
    {
        $nb = strlen($couleurCarte);
        if(is_null($couleurCarte) || $nb == 0 || $nb > 7){
            throw new ServiceException( "La couleur de la carte ne peut pas faire plus de 7 caractères et doit être renseigné", Response::HTTP_BAD_REQUEST);
        }
    }

    //A vérifier potentielement source d'erreur
    /**
     * @throws ServiceException
     */
    private function verifierAffectationsCorrect(?array $affectations, Tableau $tableau): void
    {
        $loginsParticipantsTableau = [];
        foreach ($tableau->getParticipants() as $participant){
            $loginsParticipantsTableau[] = $participant->getLogin();
        }

        foreach ($affectations as $loginAffectation) {
            if ($loginAffectation != $tableau->getProprietaireTableau()->getLogin() && !in_array($loginAffectation, $loginsParticipantsTableau)) {
                throw new ServiceException( "L'un des membres n'est pas affecté au tableau ou n'existe pas", Response::HTTP_BAD_REQUEST);
            }
        }
    }

    /**
     * @throws ServiceException
     */
    private function verifierIdColonneCorrect(?int $idColonne): void{
        if(is_null($idColonne)){
            throw new ServiceException( "La colonne n'est pas renseigné", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @throws ServiceException
     */
    public function supprimerCarte(?int $idCarte, ?string $loginUtilisateurConnecte) :Tableau{
        $carte = $this->getCarte($idCarte);
        $colonne = $this->colonneService->getColonne($carte->getColonne()->getIdColonne());
        $tableau = $this->tableauService->getByIdTableau($colonne->getTableau()->getIdTableau());

        if(!$tableau->estParticipantOuProprietaire($loginUtilisateurConnecte)){
            throw new ServiceException( "Vous n'avez pas les droits nécessaires!", Response::HTTP_UNAUTHORIZED);
        }

        $this->carteRepository->supprimer($idCarte);

        return $tableau;
    }

    /**
     * @throws ServiceException
     */
    public function creerCarte(?int $idColonne, ?string $titreCarte, ?string $descriptifCarte, ?string $couleurCarte, ?string $loginUtilisateurConnecte, ?array $affectations) :int{
        $this->verifierTitreCarteCorrect($titreCarte);
        $this->verifierDescriptifCarteCorrect($descriptifCarte);
        $this->verifierCouleurCarteCorrect($couleurCarte);
        $this->verifierIdColonneCorrect($idColonne);

        $colonne = $this->colonneService->getColonne($idColonne);
        $tableau = $this->tableauService->getByIdTableau($colonne->getTableau()->getIdTableau());

        $this->verifierAffectationsCorrect($affectations, $tableau);

        if(!$tableau->estParticipantOuProprietaire($loginUtilisateurConnecte)){
            throw new ServiceException( "Vous n'avez pas les droits nécessaires", Response::HTTP_UNAUTHORIZED);
        }

        $carte = Carte::create(1, $titreCarte, $descriptifCarte, $couleurCarte, $colonne, $affectations ?? []);
        $this->carteRepository->ajouter($carte);
        $idCarte = $this->carteRepository->lastInsertId();
        foreach ($affectations as $login){
            $this->carteRepository->ajouterAffectation($login, $idCarte);
        }

        return $idCarte;
    }

    /**
     * @throws ServiceException
     */
    public function mettreAJourCarte(?int $idCarte, ?int $idColonne, ?string $titreCarte, ?string $descriptifCarte, ?string $couleurCarte, ?string $loginUtilisateurConnecte, ?array $affectations) :Carte{
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
            throw new ServiceException( "La nouvelle colonne n'appartient pas au bon tableau", Response::HTTP_BAD_REQUEST);
        }

        if(!$tableau->estParticipantOuProprietaire($loginUtilisateurConnecte)){
            throw new ServiceException( "Vous n'avez pas les droits nécessaires", Response::HTTP_UNAUTHORIZED);
        }

        $carte->setTitreCarte($titreCarte);
        $carte->setDescriptifCarte($descriptifCarte);
        $carte->setCouleurCarte($couleurCarte);
        $carte->setColonne($colonne);
        $this->carteRepository->mettreAJour($carte);
        $idCarte = $carte->getIdCarte();
        $this->carteRepository->supprimerToutesAffectationsCarte($idCarte);
        foreach ($affectations as $login){
            $this->carteRepository->ajouterAffectation($login, $idCarte);
        }

        return $carte;
    }
}