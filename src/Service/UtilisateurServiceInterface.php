<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Service\Exception\ServiceException;
use Exception;

interface UtilisateurServiceInterface
{
    /**
     * @throws ServiceException
     */
    public function getUtilisateur(?string $loginUtilisateurConnecte): ?Utilisateur;

    /**
     * @throws ServiceException
     */
    public function recupererTableauxOuUtilisateurEstMembre(?string $loginUtilisateurConnecte): array;

    /**
     * @throws ServiceException
     * @throws Exception
     *
     */
    public function creerUtilisateur($login, $nom, $prenom, $email, $mdp, $mdp2): void;

    /**
     * @throws ServiceException
     */
    public function modifierUtilisateur($loginUtilisateurConnecte, $nom, $prenom, $email, $mdpAncien, $mdp = null, $mdp2 = null): Utilisateur;

    /**
     * @throws ServiceException
     */
    public function verifierIdentifiantUtilisateur($login, $mdp): Utilisateur;

    /**
     * @throws ServiceException
     */
    public function supprimer(?string $loginUtilisateurConnecte): void;

    public function verifierLoginConnecteEstLoginRenseigne(?string $loginConnecte, ?string $loginRenseigne): void;
}