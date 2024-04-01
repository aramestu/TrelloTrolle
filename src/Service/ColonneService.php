<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\Repository\ColonneRepositoryInterface;
use App\Trellotrolle\Service\Exception\ServiceException;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use App\Trellotrolle\Service\TableauServiceInterface;

class ColonneService implements ColonneServiceInterface
{
    public function __construct(private ColonneRepositoryInterface  $colonneRepository,
                                private TableauServiceInterface $tableauService) {}

    /**
     * @throws ServiceException
     */
    public function getColonne(?int $idColonne): Colonne{
        $this->verifierIdColonneCorrect($idColonne);
        /**
         * @var Colonne $colonne
         */
        $colonne = $this->colonneRepository->recupererParClePrimaire($idColonne);

        if(is_null($colonne)){
            throw new ServiceException( "La colonne n'existe pas", Response::HTTP_NOT_FOUND);
        }
        return $colonne;
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
    private function verifierIdTableau(?int $idTableau): void{
        if(is_null($idTableau)){
            throw new ServiceException( "Le tableau n'est pas renseigné", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @throws ServiceException
     */
    public function supprimerColonne(?int $idColonne, ?string $loginUtilisateurConnecte): void
    {
        $this->verifierIdColonneCorrect($idColonne);

        $colonne = $this->getColonne($idColonne);

        $tableau = $this->tableauService->getByIdTableau($colonne->getTableau()->getIdTableau());

        if(! $tableau->estParticipantOuProprietaire($loginUtilisateurConnecte)){
            throw new ServiceException( "Vous n'avez pas les droits nécessaires", Response::HTTP_UNAUTHORIZED);
        }

        $this->colonneRepository->supprimer($idColonne);
    }

    /**
     * @throws ServiceException
     */
    private function verifierNomColonneCorrect(?string $nomColonne): void
    {
        $nb = strlen($nomColonne);
        if(is_null($nomColonne) || $nb == 0 || $nb > 64){
            throw new ServiceException( "Le nom de la colonne ne peut pas faire plus de 64 caractères et doit être renseigné", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @throws ServiceException
     */
    private function verifierLoginCorrect(?string $login): void{
        if (strlen($login) < 4 || strlen($login) > 32) {
            throw new ServiceException( "Le login doit être compris entre 4 et 32 caractères!", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @throws ServiceException
     * @throws Exception
     */
    public function creerColonne(?int $idTableau, ?string $nomColonne, ?string $loginUtilisateurConnecte): void{
        $this->verifierNomColonneCorrect($nomColonne);
        $this->verifierIdTableau($idTableau);

        $tableau = $this->tableauService->getByIdTableau($idTableau);

        if(! $tableau->estParticipantOuProprietaire($loginUtilisateurConnecte)){
            throw new ServiceException( "Vous devez être participant au tableau pour pouvoir créer une Colonne!", Response::HTTP_UNAUTHORIZED);
        }

        $colonne = Colonne::create(1, $nomColonne, $tableau);
        $this->colonneRepository->ajouter($colonne);
    }

    /**
     * @throws ServiceException
     */
    public function mettreAJour(?int $idColonne, ?string $nomColonne, ?string $loginUtilisateurConnecte): void{
        $this->verifierNomColonneCorrect($nomColonne);
        $this->verifierIdColonneCorrect($idColonne);

        $colonne = $this->getColonne($idColonne);

        $tableau = $this->tableauService->getByIdTableau($colonne->getTableau()->getIdTableau());

        if(! $tableau->estParticipantOuProprietaire($loginUtilisateurConnecte)){
            throw new ServiceException( "Vous n'avez pas les droits nécessaires", Response::HTTP_UNAUTHORIZED);
        }

        $colonne->setTitreColonne($nomColonne);
        $this->colonneRepository->mettreAJour($colonne);
    }

    /**
     * @throws ServiceException
     */
    public function recupererColonnesTableau(int|null $idTableau): array
    {
        $this->verifierIdTableau($idTableau);
        return $this->colonneRepository->recupererColonnesTableau($idTableau);
    }
}