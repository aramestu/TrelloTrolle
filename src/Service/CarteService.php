<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\Repository\CarteRepositoryInterface;
use App\Trellotrolle\Modele\Repository\ColonneRepositoryInterface;
use App\Trellotrolle\Service\Exception\ServiceException;
use Symfony\Component\HttpFoundation\Response;

class CarteService
{
    public function __construct(private CarteRepositoryInterface  $carteRepository) {}

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
}