<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Service\Exception\ServiceException;
use Exception;

/**
 * Interface UtilisateurServiceInterface
 *
 * Cette interface définit les méthodes pour le service d'utilisateur.
 */
interface UtilisateurServiceInterface
{

    /**
 * Méthode getUtilisateur
 *
 * Cette méthode retourne un objet Utilisateur en fonction du login de l'utilisateur connecté.
 *
 * @param string|null $loginUtilisateurConnecte Le login de l'utilisateur connecté
 * @return Utilisateur|null L'objet Utilisateur correspondant au login de l'utilisateur connecté, ou null s'il n'existe pas
 * @throws ServiceException Si une erreur se produit lors de la récupération de l'utilisateur
 */
    public function getUtilisateur(?string $loginUtilisateurConnecte): ?Utilisateur;

/**
 * Récupère les tableaux auxquels un utilisateur est membre.
 *
 * @param string|null $loginUtilisateurConnecte Le login de l'utilisateur connecté.
 * @return array Les tableaux auxquels l'utilisateur est membre.
 * @throws ServiceException En cas d'erreur lors de la récupération des tableaux.
 */
    public function recupererTableauxOuUtilisateurEstMembre(?string $loginUtilisateurConnecte): array;

/**
 * Méthode pour créer un utilisateur.
 *
 * @param string $login Le login de l'utilisateur.
 * @param string $nom Le nom de l'utilisateur.
 * @param string $prenom Le prénom de l'utilisateur.
 * @param string $email L'email de l'utilisateur.
 * @param string $mdp Le mot de passe de l'utilisateur.
 * @param string $mdp2 La confirmation du mot de passe de l'utilisateur.
 * @throws ServiceException Si une erreur liée au service se produit.
 * @throws Exception Si une erreur générale se produit.
 * @return void
 */
    public function creerUtilisateur(string $login, string $nom, string $prenom, string $email, string $mdp, string $mdp2): void;

/**
 * Modifie un utilisateur.
 *
 * @param string $loginUtilisateurConnecte Le login de l'utilisateur connecté
 * @param string $nom Le nouveau nom de l'utilisateur
 * @param string $prenom Le nouveau prénom de l'utilisateur
 * @param string $email Le nouvel email de l'utilisateur
 * @param string $mdpAncien Le mot de passe ancien de l'utilisateur
 * @param string|null $mdp Le nouveau mot de passe de l'utilisateur (optionnel)
 * @param string|null $mdp2 La confirmation du nouveau mot de passe de l'utilisateur (optionnel)
 * @return Utilisateur L'utilisateur modifié
 * @throws ServiceException Si une erreur se produit lors de la modification de l'utilisateur
 */
    public function modifierUtilisateur(string $loginUtilisateurConnecte, string $nom, string $prenom, string $email, string $mdpAncien, string $mdp = null, string $mdp2 = null): Utilisateur;

/**
 * Vérifie l'identifiant de l'utilisateur.
 *
 * @param string $login Le login de l'utilisateur
 * @param string $mdp Le mot de passe de l'utilisateur
 * @return Utilisateur L'objet Utilisateur correspondant à l'identifiant
 * @throws ServiceException Si une erreur se produit lors de la vérification de l'identifiant
 */
    public function verifierIdentifiantUtilisateur(string $login, string $mdp): Utilisateur;

/**
 * Supprime un utilisateur connecté.
 *
 * @param string|null $loginUtilisateurConnecte Le login de l'utilisateur connecté.
 * @throws ServiceException En cas d'erreur lors de la suppression.
 * @return void
 */
    public function supprimer(?string $loginUtilisateurConnecte): void;

    /**
 * Méthode verifierLoginConnecteEstLoginRenseigne
 *
 * Cette méthode vérifie si le login connecté est renseigné.
 *
 * @param string|null $loginConnecte Le login de l'utilisateur connecté
 * @param string|null $loginRenseigne Le login renseigné
 * @return void
 */
    public function verifierLoginConnecteEstLoginRenseigne(?string $loginConnecte, ?string $loginRenseigne): void;
}