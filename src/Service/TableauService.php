<?php

use App\Trellotrolle\Lib\MotDePasseInterface;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\TableauRepositoryInterface;
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
    public function getByCodeTableau(?string $codeTableau): ?Tableau{
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
    public function getByIdTableau(?int $idTableau): ?Tableau{
        if(is_null($idTableau)){
            throw new ServiceException( "L'idTableau n'est pas renseigné", Response::HTTP_BAD_REQUEST);
        }
        /**
         * @var Tableau $tableau
         */
        $tableau = $this->tableauRepository->recupererParClePrimaire($idTableau);

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

        if(is_null($loginUtilisateurConnecte) || strlen($loginUtilisateurConnecte) == 0){
            throw new ServiceException( "Le login ne peut pas être vide", Response::HTTP_BAD_REQUEST);
        }

        /**
         * @var Utilisateur $utilisateur
         */
        $utilisateur = $this->utilisateurRepository->recupererParClePrimaire($loginUtilisateurConnecte);

        if(is_null($utilisateur)){
            throw new ServiceException( "L'utilisateur n'existe pas!", Response::HTTP_NOT_FOUND);
        }

        $codeTableauHache = $this->motDePasse->genererChaineAleatoire(64);

        $tableau = new Tableau($codeTableauHache,$nomTableau,$utilisateur); // A revoir Ici pour l'user et voir si la requête au dessus est vrm nécessaire
        $this->tableauRepository->ajouter($tableau);
    }

    /**
     * @throws ServiceException
     */
    public function metterAJourTableau(?int $idTableau, ?string $loginUtilisateurConnecte, ?string $nomtableau): void{
        if(is_null($loginUtilisateurConnecte) || strlen($loginUtilisateurConnecte) == 0 || is_null($idTableau) || is_null($nomtableau) || strlen($nomtableau) == 0){
            throw new ServiceException( "Le login ou l'idTableau ou le nom du tableau ne peut pas être vide", Response::HTTP_BAD_REQUEST);
        }
        if(strlen($nomtableau) > 64){
            throw new ServiceException( "Le nom du tableau ne peut pas faire plus de 64 caractères", Response::HTTP_BAD_REQUEST);
        }

        /**
         * @var Tableau $tableau
         */
        $tableau = $this->tableauRepository->recupererParClePrimaire($idTableau);

        if(is_null($tableau)){
            throw new ServiceException( "Le tableau n'existe pas", Response::HTTP_NOT_FOUND);
        }
        if($loginUtilisateurConnecte != $tableau->getUtilisateur()){
            throw new ServiceException( "Seul le propriétaire du tableau peut mettre à jour le tableau", Response::HTTP_UNAUTHORIZED);
        }
        $tableau->setTitreTableau($nomtableau);
        $this->tableauRepository->mettreAJour($tableau);
    }

    /**
     * @throws ServiceException
     */
    public function ajouterMembre(?int $idTableau, ?string $loginUtilisateurConnecte, ?string $loginUtilisateurNouveau){
        if(is_null($idTableau) ||is_null($loginUtilisateurConnecte) || strlen($loginUtilisateurConnecte) == 0 || is_null($loginUtilisateurNouveau) || strlen($loginUtilisateurNouveau) == 0){
            throw new ServiceException( "L'idTableau ou le login de l'user connecté ou le login a ajouté ne peut pas être vide", Response::HTTP_BAD_REQUEST);
        }

        /**
         * @var Tableau $tableau
         */
        $tableau = $this->tableauRepository->recupererParClePrimaire($idTableau);

        if(is_null($tableau)){
            throw new ServiceException( "Le tableau n'existe pas", Response::HTTP_NOT_FOUND);
        }
        if(! $tableau->estProprietaire($loginUtilisateurConnecte)){
            throw new ServiceException( "Seul le propriétaire du tableau peut mettre ajouter des membres", Response::HTTP_UNAUTHORIZED);
        }

        /**
         * @var Utilisateur $utilisateurNouveau
         */
        $utilisateurNouveau = $this->utilisateurRepository->recupererParClePrimaire($loginUtilisateurNouveau);

        if(is_null($utilisateurNouveau)){
            throw new ServiceException( "L'utilisateur à ajouter n'existe pas", Response::HTTP_NOT_FOUND);
        }
        if($tableau->estParticipantOuProprietaire($loginUtilisateurNouveau)){
            throw new ServiceException( "L'utilisateur est proprio ou participe déjà à ce talbeau", Response::HTTP_CONFLICT);
        }

        $tab = $tableau->getParticipants(); // TODO: à revoir le système pour ajouter des membres, c'est plus comme ça dans la NEW BD
        $tab[] = $loginUtilisateurNouveau;
        $tableau->setParticipants($tab); // TODO : ajouter une fonciton ajouter membre dans DataObject et refactor ces 3 lignes içi

        $this->tableauRepository->mettreAJour($tableau);
    }
}