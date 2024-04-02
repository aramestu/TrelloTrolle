<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\TableauRepositoryInterface;
use App\Trellotrolle\Modele\Repository\UtilisateurRepositoryInterface;
use App\Trellotrolle\Lib\MotDePasseInterface;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use App\Trellotrolle\Service\Exception\ServiceException;

/**
 * Classe UtilisateurService
 *
 * Cette classe implémente l'interface UtilisateurServiceInterface et fournit des méthodes pour gérer les utilisateurs.
 *
 * @package App\Trellotrolle\Service
 */
class UtilisateurService implements UtilisateurServiceInterface
{

    /**
     * Constructeur de la classe.
     *
     * @param UtilisateurRepositoryInterface $utilisateurRepository L'instance de l'interface UtilisateurRepositoryInterface.
     * @param TableauRepositoryInterface $tableauRepository L'instance de l'interface TableauRepositoryInterface.
     * @param MotDePasseInterface $motDePasse L'instance de l'interface MotDePasseInterface.
     */
    public function __construct(private readonly UtilisateurRepositoryInterface $utilisateurRepository,
                                private readonly TableauRepositoryInterface     $tableauRepository,
                                private readonly MotDePasseInterface            $motDePasse)
    {
    }

    /**
     * Récupère un utilisateur en fonction de son login.
     *
     * @param string|null $loginUtilisateurConnecte Le login de l'utilisateur connecté
     * @return Utilisateur|null L'utilisateur correspondant au login, ou null s'il n'existe pas
     * @throws ServiceException Si le login n'est pas renseigné ou si l'utilisateur n'existe pas
     */
    public function getUtilisateur(?string $loginUtilisateurConnecte): ?Utilisateur
    {
        if (is_null($loginUtilisateurConnecte)) {
            throw new ServiceException("Le login n'est pas renseigné", Response::HTTP_BAD_REQUEST);
        }
        /**
         * @var Utilisateur $utilisateur
         */
        $utilisateur = $this->utilisateurRepository->recupererParClePrimaire($loginUtilisateurConnecte);

        if (is_null($utilisateur)) {
            throw new ServiceException("L'utilisateur n'existe pas", Response::HTTP_NOT_FOUND);
        }
        return $utilisateur;
    }

    /**
     * Récupère les tableaux où l'utilisateur est membre ou propriétaire.
     *
     * @param string|null $loginUtilisateurConnecte Le login de l'utilisateur connecté
     * @return array Les tableaux où l'utilisateur est membre ou propriétaire
     * @throws ServiceException Si le login de l'utilisateur connecté n'est pas valide
     */
    public function recupererTableauxOuUtilisateurEstMembre(?string $loginUtilisateurConnecte): array
    {
        $this->verifierLoginCorrect($loginUtilisateurConnecte);

        return $this->utilisateurRepository->recupererTableauxOuUtilisateurEstMembre($loginUtilisateurConnecte);
    }

    /**
     * Vérifie si le login est correct.
     *
     * @param string $login Le login à vérifier.
     * @return void
     * @throws ServiceException Si le login n'a pas entre 4 et 32 caractères.
     */
    private function verifierLoginCorrect(string $login): void
    {
        if (strlen($login) < 4 || strlen($login) > 32) {
            throw new ServiceException("Le login doit être compris entre 4 et 32 caractères!", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Vérifie si l'adresse email est valide.
     *
     * @param string $email L'adresse email à vérifier.
     * @throws ServiceException Si l'adresse email est incorrecte ou dépasse 64 caractères.
     * @return void
     */
    private function verifierEmailValide(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ServiceException("L'adresse mail est incorrecte!", Response::HTTP_BAD_REQUEST);
        }
        if (strlen($email) > 64) {
            throw new ServiceException("L'adresse mail ne doit pas faire plus de 64 caractères", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Vérifie si le mot de passe en clair respecte les critères suivants:
     * - Au moins une minuscule
     * - Au moins une majuscule
     * - Au moins un chiffre
     * - Longueur entre 8 et 20 caractères
     *
     * @param string $mdp Le mot de passe à vérifier
     * @return void
     * @throws ServiceException Si le mot de passe ne respecte pas les critères
     */
    private function verifierMotDePasseClair(string $mdp): void
    {
        if (!preg_match("#^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,20}$#", $mdp)) {
            throw new ServiceException("Le mot de passe doit avoir une minuscule, majuscule, un nombre et faire entre 8 et 20 caractères!", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Vérifie si le nom et le prénom sont corrects.
     *
     * @param string $nom Le nom à vérifier.
     * @param string $prenom Le prénom à vérifier.
     * @return void
     * @throws ServiceException Si le nom et le prénom ne respectent pas les critères.
     */
    private function verifierNomEtPrenomCorrecte(string $nom, string $prenom): void
    {
        $nbNom = strlen($nom);
        $nbPrenom = strlen($prenom);
        if ($nbNom > 32 || $nbPrenom > 32 || $nbNom < 2 || $nbPrenom < 2) {
            throw new ServiceException("Le nom et le prénom ne doivent pas faire plus de 32 caractères", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Vérifie si les deux mots de passe sont identiques.
     *
     * @param mixed $mdp Le premier mot de passe à vérifier.
     * @param mixed $mdp2 Le deuxième mot de passe à vérifier.
     * @return void
     * @throws ServiceException Si les mots de passe sont différents.
     */
    private function verifier2MdpIdentiques(mixed $mdp, mixed $mdp2): void
    {
        if ($mdp != $mdp2) {
            throw new ServiceException("Les mots de passe sont différents!", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Vérifie si toutes les informations fournies sont correctes.
     *
     * @param mixed $login Le login de l'utilisateur.
     * @param string $nom Le nom de l'utilisateur.
     * @param string $prenom Le prénom de l'utilisateur.
     * @param string $email L'adresse email de l'utilisateur.
     * @param string $mdp Le mot de passe de l'utilisateur.
     * @param string $mdp2 La confirmation du mot de passe de l'utilisateur.
     * @throws ServiceException Si une des informations est incorrecte.
     */
    private function verifierToutesInfosCorrectes(mixed $login, string $nom, string $prenom, string $email, string $mdp, string $mdp2): void
    {
        $this->verifierLoginCorrect($login);
        $this->verifierEmailValide($email);
        $this->verifierMotDePasseClair($mdp);
        $this->verifierNomEtPrenomCorrecte($nom, $prenom);
        $this->verifier2MdpIdentiques($mdp, $mdp2);
    }

    /**
     * Crée un nouvel utilisateur avec les informations fournies.
     *
     * @param string|null $login Le login de l'utilisateur.
     * @param string|null $nom Le nom de l'utilisateur.
     * @param string|null $prenom Le prénom de l'utilisateur.
     * @param string|null $email L'email de l'utilisateur.
     * @param string|null $mdp Le mot de passe de l'utilisateur.
     * @param string|null $mdp2 La confirmation du mot de passe de l'utilisateur.
     * @throws ServiceException Si le login, le mot de passe, l'email, le nom, le prénom ou la confirmation du mot de passe n'est pas renseigné.
     * @throws Exception Si une erreur inattendue se produit.
     * @throws ServiceException Si une donnée n'est pas correcte.
     * @throws ServiceException Si le login est déjà pris.
     * @throws ServiceException Si un compte est déjà enregistré avec cette adresse mail.
     * @return void
     */
    public function creerUtilisateur(string|null $login, string|null $nom, string|null $prenom, string|null $email, string|null $mdp, string|null $mdp2): void
    {
        if (is_null($login) || is_null($mdp) || is_null($email) || is_null($nom) || is_null($prenom) || is_null($mdp2)) {
            throw new ServiceException("le login ou le mdp ou l'email ou le nom ou le prenom n'a pas été renseigné", Response::HTTP_BAD_REQUEST);
        }


        // Throw une erreur si une donnée n'est pas correcte
        $this->verifierToutesInfosCorrectes($login, $nom, $prenom, $email, $mdp, $mdp2);

        $utilisateur = $this->utilisateurRepository->recupererParClePrimaire($login);
        if (!is_null($utilisateur)) {
            throw new ServiceException("Ce login est déjà pris!", Response::HTTP_CONFLICT);
        }

        $tabUser = $this->utilisateurRepository->recupererUtilisateursParEmail($email);
        // S'il existe déjà des utilisateurs avec cette adresse mail
        if (count($tabUser) > 0) {
            throw new ServiceException("Un compte est déjà enregistré avec cette adresse mail!", Response::HTTP_CONFLICT);
        }

        $mdpHache = $this->motDePasse->hacher($mdp);

        $utilisateur = Utilisateur::create($login, $nom, $prenom, $email, $mdpHache);
        $this->utilisateurRepository->ajouter($utilisateur);
    }

    /**
     * Modifie un utilisateur.
     *
     * @param string|null $loginUtilisateurConnecte Le login de l'utilisateur connecté.
     * @param string|null $nom Le nom de l'utilisateur.
     * @param string|null $prenom Le prénom de l'utilisateur.
     * @param string|null $email L'email de l'utilisateur.
     * @param string|null $mdpAncien Le mot de passe ancien de l'utilisateur.
     * @param string|null $mdp Le nouveau mot de passe de l'utilisateur.
     * @param string|null $mdp2 La confirmation du nouveau mot de passe de l'utilisateur.
     * @return Utilisateur L'utilisateur modifié.
     * @throws ServiceException Si le login, l'email, le nom ou le prénom n'a pas été renseigné.
     * @throws ServiceException Si le login n'existe pas.
     * @throws ServiceException Si les deux nouveaux mots de passe ne sont pas identiques.
     * @throws ServiceException Si le mot de passe ancien est erroné.
     */
    public function modifierUtilisateur(string|null $loginUtilisateurConnecte, string|null $nom, string|null $prenom, string|null $email, string|null $mdpAncien, string $mdp = null, string $mdp2 = null): Utilisateur
    {
        if (is_null($loginUtilisateurConnecte) || is_null($nom) || is_null($prenom)) {
            throw new ServiceException("le login ou l'email ou le nom ou le prenom n'a pas été renseigné", Response::HTTP_NOT_FOUND);
        }
        $this->verifierLoginCorrect($loginUtilisateurConnecte);
        $this->verifierNomEtPrenomCorrecte($nom, $prenom);

        /**
         * @var Utilisateur $utilisateur
         */
        $utilisateur = $this->utilisateurRepository->recupererParClePrimaire($loginUtilisateurConnecte);
        if (is_null($utilisateur)) {
            throw new ServiceException("Ce login n'existe pas!", Response::HTTP_NOT_FOUND);
        }

        // Pour ne pas throw d'erreurs s'il n'y a pas de mdp renseignés, on garde l'ancien
        if ((!is_null($mdp) && strlen($mdp) > 0) || (!is_null($mdp2) && strlen($mdp2) > 0)) {
            $this->verifier2MdpIdentiques($mdp, $mdp2);
            $this->verifierMotDePasseClair($mdp);

            if (!$this->motDePasse->verifier($mdpAncien, $utilisateur->getMdpHache())) {
                throw new ServiceException("Impossible de changer le mot de passe, l'ancien mot de passe est erroné", Response::HTTP_FORBIDDEN);
            }
            $mdpHache = $this->motDePasse->hacher($mdp);
            $utilisateur->setMdpHache($mdpHache);
        }

        $utilisateur->setNom($nom);
        $utilisateur->setPrenom($prenom);
        $utilisateur->setEmail($email);
        $this->utilisateurRepository->mettreAJour($utilisateur);

        return $utilisateur;
    }

    /**
     * Vérifie l'identifiant de l'utilisateur en comparant le login et le mot de passe fournis.
     *
     * @param string|null $login Le login de l'utilisateur
     * @param string|null $mdp Le mot de passe de l'utilisateur
     * @return Utilisateur L'objet Utilisateur correspondant à l'identifiant fourni
     * @throws ServiceException Si le login ou le mot de passe est manquant, ou si le mot de passe est incorrect
     */
    public function verifierIdentifiantUtilisateur(string|null $login, string|null $mdp): Utilisateur
    {
        if (is_null($login) || is_null($mdp)) {
            throw new ServiceException("Login ou mot de passe manquant.", Response::HTTP_BAD_REQUEST);
        }

        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->getUtilisateur($login);

        if (!$this->motDePasse->verifier($mdp, $utilisateur->getMdpHache())) {
            throw new ServiceException("Mot de passe incorrect.", Response::HTTP_UNAUTHORIZED);
        }

        return $utilisateur;
    }

    /**
     * Supprime un utilisateur en fonction de son login.
     *
     * @param string|null $loginUtilisateurConnecte Le login de l'utilisateur à supprimer.
     * @throws ServiceException Si le login n'est pas renseigné.
     * @return void
     */
    public function supprimer(?string $loginUtilisateurConnecte): void
    {
        if (is_null($loginUtilisateurConnecte)) {
            throw new ServiceException("le login n'a pas été renseigné", Response::HTTP_BAD_REQUEST);
        }
        $this->verifierLoginCorrect($loginUtilisateurConnecte);

        $this->utilisateurRepository->supprimer($loginUtilisateurConnecte);
    }

    /**
     * Vérifie si le login connecté est le même que le login renseigné.
     *
     * @param string|null $loginConnecte Le login de l'utilisateur connecté.
     * @param string|null $loginRenseigne Le login renseigné.
     * @throws ServiceException Si le login connecté ou le login renseigné est incorrect.
     * @return void
     */
    public function verifierLoginConnecteEstLoginRenseigne(?string $loginConnecte, ?string $loginRenseigne): void
    {
        $this->verifierLoginCorrect($loginConnecte);
        $this->verifierLoginCorrect($loginRenseigne);

        if ($loginConnecte != $loginRenseigne) {
            throw new ServiceException("Vous n'avez pas accès à cet utilisateur", Response::HTTP_UNAUTHORIZED);
        }
    }
}