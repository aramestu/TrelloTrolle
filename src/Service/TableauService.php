<?php

use App\Trellotrolle\Lib\MotDePasseInterface;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\UtilisateurRepositoryInterface;
use App\Trellotrolle\Service\Exception\ServiceException;
use Symfony\Component\HttpFoundation\Response;

class TableauService
{
    public function __construct(private TableauRepositoryInterface $tableauRepository,
                                private UtilisateurRepositoryInterface $utilisateurRepository,
                                private MotDePasseInterface $motDePasse) {}

    /**
     * @throws ServiceException
     */
    public function getTableau(?string $codeTableau): ?Tableau{
        if(is_null($codeTableau) || strlen($codeTableau) == 0){
            throw new ServiceException( "Le tableau n'est pas renseigné", Response::HTTP_BAD_REQUEST);
        }
        /**
         * @var Tableau $tableau
         */
        $tableau = $this->tableauRepository->recupererParCodeTableau($codeTableau);

        if(is_null($tableau)){
            throw new ServiceException( "Le tableau n'existe pas", Response::HTTP_NOT_FOUND);
        }
        return $tableau;
    }

    /**
     * @throws ServiceException
     */
    private function verifierNomTableauCorrect(?string $nomTableau): void{
        $nb = strlen($nomTableau);
        if(is_null($nomTableau) || $nb == 0){
            throw new ServiceException( "Le nom du tableau ne peut pas être vide", Response::HTTP_BAD_REQUEST);
        }
        if($nb > 64){
            throw new ServiceException( "Le nom du tableau ne doit pas faire plus de 64 caractères", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @throws ServiceException
     * @throws Exception
     */
    public function creerTableau(?string $loginUtilisateurConnecte, ?string $nomTableau): void{
        $this->verifierNomTableauCorrect($nomTableau);

        if(is_null($loginUtilisateurConnecte) || strlen($loginUtilisateurConnecte)){
            throw new ServiceException( "Le login ne peut pas être vide", Response::HTTP_BAD_REQUEST);
        }

        /**
         * @var Utilisateur $utilisateur
         */
        $utilisateur = $this->utilisateurRepository->recupererParClePrimaire($loginUtilisateurConnecte);

        if(is_null($utilisateur)){
            throw new ServiceException( "Le login n'existe pas", Response::HTTP_NOT_FOUND);
        }

        $codeTableauHache = $this->motDePasse->genererChaineAleatoire(64);

        $tableau = new Tableau($codeTableauHache,$nomTableau,$utilisateur); // A revoir Ici pour l'user et voir si la requête au dessus est vrm nécessaire
        $this->tableauRepository->ajouter($tableau);
    }
}