<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\Repository\ColonneRepositoryInterface;
use App\Trellotrolle\Service\Exception\ServiceException;
use Symfony\Component\HttpFoundation\Response;

class ColonneService
{
    public function __construct(private ColonneRepositoryInterface  $colonneRepository) {}

    /**
     * @throws ServiceException
     */
    public function getColonne(?int $idColonne): ?Colonne{
        if(is_null($idColonne)){
            throw new ServiceException( "La colonne n'est pas renseigné", Response::HTTP_NOT_FOUND);
        }
        /**
         * @var Colonne $colonne
         */
        $colonne = $this->colonneRepository->recupererParClePrimaire($idColonne);

        if(is_null($colonne)){
            throw new ServiceException( "La colonne n'existe pas", Response::HTTP_NOT_FOUND);
        }
        return $colonne;
    }


}