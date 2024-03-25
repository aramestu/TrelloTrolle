<?php

use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Service\Exception\ServiceException;

interface UtilisateurServiceInterface
{
    /**
     * @throws ServiceException
     */
    public function getUtilisateur(?int $idUtilisateurConnecte): ?Utilisateur;

    /**
     * @throws ServiceException
     * @throws Exception
     */
    public function creerUtilisateur($login, $nom, $prenom, $email, $mdp, $mdp2): void;

    /**
     * @throws ServiceException
     */
    public function modifierUtilisateur($loginUtilisateurConnecte, $nom, $prenom, $mdp = null, $mdp2 = null): void;

    /**
     * @throws ServiceException
     */
    public function supprimer($loginUtilisateurConnecte): void;
}