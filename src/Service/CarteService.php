<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\Repository\CarteRepositoryInterface;
use App\Trellotrolle\Modele\Repository\ColonneRepositoryInterface;
use App\Trellotrolle\Modele\Repository\TableauRepositoryInterface;
use App\Trellotrolle\Service\Exception\ServiceException;
use Symfony\Component\HttpFoundation\Response;

class CarteService
{
    public function __construct(private CarteRepositoryInterface  $carteRepository,
                                private ColonneRepositoryInterface $colonneRepository,
                                private TableauRepositoryInterface $tableauRepository) {}

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
    public function verifierNomCarteCorrect(?string $titreCarte) {
        $nb = strlen($titreCarte);
        if(is_null($titreCarte) || $nb == 0 || $nb > 64){
            throw new ServiceException( "Le nom de la carte ne peut pas faire plus de 64 caractères et doit être renseigné", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @throws ServiceException
     */
    public function verifierDescriptifCarteCorrect(?string $descriptifCarte) {
        $nb = strlen($descriptifCarte);
        if(is_null($descriptifCarte) || $nb == 0){
            throw new ServiceException( "La description de la carte doit être renseigné", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @throws ServiceException
     */
    public function verifierCouleurCarteCorrect(?string $couleurCarte) {
        $nb = strlen($couleurCarte);
        if(is_null($couleurCarte) || $nb == 0 || $nb > 7){
            throw new ServiceException( "La couleur de la carte ne peut pas faire plus de 7 caractères et doit être renseigné", Response::HTTP_BAD_REQUEST);
        }

}