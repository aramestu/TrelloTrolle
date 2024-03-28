<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Lib\MotDePasse;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\TableauRepositoryInterface;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;
use App\Trellotrolle\Modele\Repository\UtilisateurRepositoryInterface;
use App\Trellotrolle\Lib\MotDePasseInterface;
use Symfony\Component\HttpFoundation\Response;
use App\Trellotrolle\Service\Exception\ServiceException;

class UtilisateurService implements UtilisateurServiceInterface
{
    public function __construct(private UtilisateurRepositoryInterface $utilisateurRepository,
                                private TableauRepositoryInterface $tableauRepository,
                                private MotDePasseInterface  $motDePasse) {}

    /**
     * @throws ServiceException
     */
    public function getUtilisateur(?int $idUtilisateurConnecte) :?Utilisateur{
        if(is_null($idUtilisateurConnecte)){
            throw new ServiceException( "L'identifiant n'est pas renseigné", Response::HTTP_BAD_REQUEST);
        }
        /**
         * @var Utilisateur $utilisateur
         */
        $utilisateur = (new UtilisateurRepository())->recupererParClePrimaire($idUtilisateurConnecte);

        if(is_null($utilisateur)){
            throw new ServiceException( "L'utilisateur n'existe pas", Response::HTTP_NOT_FOUND);
        }
        return $utilisateur;
    }

    /**
     * @throws ServiceException
     */
    private function verifierLoginCorrect($login) : void{
        if (strlen($login) < 4 || strlen($login) > 32) {
            throw new ServiceException( "Le login doit être compris entre 4 et 32 caractères!", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @throws ServiceException
     */
    private function verifierEmailValide($email) : void{
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ServiceException( "L'adresse mail est incorrecte!", Response::HTTP_BAD_REQUEST);
        }
        if(strlen($email) > 64){
            throw new ServiceException( "L'adresse mail ne doit pas faire plus de 64 caractères", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @throws ServiceException
     */
    private function verifierMotDePasseClair($mdp) : void{
        if (!preg_match("#^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,20}$#", $mdp)) {
            throw new ServiceException( "Le mot de passe doit avoir une minuscule, majuscule, un nombre et faire entre 8 et 20 caractères!", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @throws ServiceException
     */
    private function verifierNomEtPrenomCorrecte($nom, $prenom): void{
        $nbNom = strlen($nom);
        $nbPrenom = strlen($prenom);
        if($nbNom > 32 || $nbPrenom > 32 || $nbNom < 2 || $nbPrenom < 2){
            throw new ServiceException( "Le nom et le prénom ne doivent pas faire plus de 32 caractères", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @throws ServiceException
     */
    private function verifier2MdpIdentiques($mdp, $mdp2) : void{
        if($mdp != $mdp2){
            throw new ServiceException( "Les mots de passe sont différents!", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @throws ServiceException
     */
    private function verifierToutesInfosCorrectes($login, $nom, $prenom, $email, $mdp, $mdp2) : void{
        $this->verifierLoginCorrect($login);
        $this->verifierEmailValide($email);
        $this->verifierMotDePasseClair($mdp);
        $this->verifierNomEtPrenomCorrecte($nom, $prenom);
        $this->verifier2MdpIdentiques($mdp, $mdp2);
    }

    /**
     * @throws ServiceException
     * @throws Exception
     */
    public function creerUtilisateur($login, $nom, $prenom, $email, $mdp, $mdp2): void{
        if(is_null($login) || is_null($mdp) || is_null($email) || is_null($nom) || is_null($prenom) || is_null($mdp2)){
            throw new ServiceException("le login ou le mdp ou l'email ou le nom ou le prenom n'a pas été renseigné", Response::HTTP_BAD_REQUEST);
        }

        // Throw une erreur si une donnée n'est pas correcte
        $this->verifierToutesInfosCorrectes($login, $nom, $prenom, $email, $mdp, $mdp2);

        $utilisateur = $this->utilisateurRepository->recupererParClePrimaire($login);
        if ($utilisateur != null) {
            throw new ServiceException( "Ce login est déjà pris!", Response::HTTP_CONFLICT);
        }

        $utilisateur = $this->utilisateurRepository->recupererUtilisateursParEmail($email);
        if ($utilisateur != null) {
            throw new ServiceException("Un compte est déjà enregistré avec cette adresse mail!", Response::HTTP_CONFLICT);
        }

        $mdpHache = $this->motDePasse->hacher($mdp);

        $utilisateur = new Utilisateur($login, $nom, $prenom, $email, $mdpHache);
        $this->utilisateurRepository->ajouter($utilisateur);
    }

    /**
     * @throws ServiceException
     */
    public function modifierUtilisateur($loginUtilisateurConnecte, $nom, $prenom, $mdp = null, $mdp2 = null): void{
        if(is_null($loginUtilisateurConnecte) || is_null($nom) || is_null($prenom)){
            throw new ServiceException("le login ou l'email ou le nom ou le prenom n'a pas été renseigné", Response::HTTP_NOT_FOUND);
        }
        $this->verifierLoginCorrect($loginUtilisateurConnecte);
        $this->verifierNomEtPrenomCorrecte($nom, $prenom);


        $utilisateur = $this->utilisateurRepository->recupererParClePrimaire($loginUtilisateurConnecte);
        if (is_null($utilisateur)) {
            throw new ServiceException( "Ce login n'existe pas!", Response::HTTP_NOT_FOUND);
        }

        // Pour ne pas throw d'erreurs s'il n'y a pas de mdp renseignés, on garde l'ancien
        if(! is_null($mdp) && ! is_null($mdp2)) {
            $this->verifier2MdpIdentiques($mdp, $mdp2);
            $this->verifierMotDePasseClair($mdp);

            // Si l'utilisateur décide de changer de mdp
            if(! $this->motDePasse->verifier($mdp, $utilisateur->getMdpHache())){
                $mdpHache = $this->motDePasse->hacher($mdp);
                $utilisateur->setMdpHache($mdpHache);
            }
        }

        $utilisateur->setNom($nom);
        $utilisateur->setPrenom($nom);
        $this->utilisateurRepository->mettreAJour($utilisateur);
    }

    /**
     * @throws ServiceException
     */
    public function supprimer(?string $loginUtilisateurConnecte) : void{
        if(is_null($loginUtilisateurConnecte)){
            throw new ServiceException("le login n'a pas été renseigné", Response::HTTP_BAD_REQUEST);
        }
        $this->verifierLoginCorrect($loginUtilisateurConnecte);

        //TODO : Redéfinir la méthode supprimer dans UtilisateurRepository pour qu'elle appelle supprimer de TableauRep
        $this->utilisateurRepository->supprimer($loginUtilisateurConnecte);
    }

    //TODO : Rajouter un système pour récupérer le mdp via l'email (mot de passe perdu)


    /**
     * @throws ServiceException
     */
    public function getUtilisateursPasMembreOuPasProprioTableau(?int $idTableau): array{
        if(is_null($idTableau)){
            throw new ServiceException("L'idTableau n'a pas été renseigné", Response::HTTP_BAD_REQUEST);
        }

        return $this->utilisateurRepository->recupererUtilisateursPasMembreOuPasProprio($idTableau); // TODO : ajouter cette fonction dans le Repository
    }
}