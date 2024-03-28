<?php
namespace App\Trellotrolle\Service;
use App\Trellotrolle\Lib\MotDePasseInterface;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\CarteRepositoryInterface;
use App\Trellotrolle\Modele\Repository\ColonneRepositoryInterface;
use App\Trellotrolle\Modele\Repository\TableauRepositoryInterface;
use App\Trellotrolle\Modele\Repository\UtilisateurRepositoryInterface;
use App\Trellotrolle\Service\Exception\ServiceException;
use Symfony\Component\HttpFoundation\Response;

class TableauService implements TableauServiceInterface
{
    public function __construct(private TableauRepositoryInterface $tableauRepository,
                                private UtilisateurRepositoryInterface $utilisateurRepository,
                                private CarteRepositoryInterface $carteRepository,
                                private ColonneRepositoryInterface $colonneRepository,
                                private MotDePasseInterface $motDePasse) {}

    /**
     * @throws ServiceException
     */
    private function verifierCodeTableauCorrect(?string $codeTableau): void{
        if(is_null($codeTableau) || strlen($codeTableau) == 0){
            throw new ServiceException( "Le tableau n'est pas renseigné", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @throws ServiceException
     */
    public function getByCodeTableau(?string $codeTableau): Tableau{
        $this->verifierCodeTableauCorrect($codeTableau);
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
    private function verifierIdTableauCorrect(?int $idTableau): void{
        if(is_null($idTableau)){
            throw new ServiceException( "L'idTableau n'est pas renseigné", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @throws ServiceException
     */
    public function getByIdTableau(?int $idTableau): Tableau{
        $this->verifierIdTableauCorrect($idTableau);
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
        if(is_null($nomTableau) || $nb == 0 || $nb > 64){
            throw new ServiceException( "Le nom du tableau ne peut pas être vide et ne doit pas faire plus de 64 caractères", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @throws ServiceException
     */
    private function verifierLoginCorrect(?string $login){
        $nb = strlen($login);
        if(is_null($login) || $nb < 4 || $nb > 32){
            throw new ServiceException( "Le login ne peut pas être vide, et doit daire entre 4 et 32 caractères", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @throws ServiceException
     * @throws Exception
     */
    public function creerTableau(?string $loginUtilisateurConnecte, ?string $nomTableau): void{
        $this->verifierNomTableauCorrect($nomTableau);

        $this->verifierLoginCorrect($loginUtilisateurConnecte);

        /**
         * @var Utilisateur $utilisateur
         */
        $utilisateur = $this->utilisateurRepository->recupererParClePrimaire($loginUtilisateurConnecte);

        if(is_null($utilisateur)){
            throw new ServiceException( "L'utilisateur n'existe pas!", Response::HTTP_NOT_FOUND);
        }

        $codeTableauHache = $this->motDePasse->genererChaineAleatoire(64);

        $tableau = new Tableau($codeTableauHache,$nomTableau,$utilisateur); // TODO: A revoir Ici pour l'user et voir si la requête au dessus est vrm nécessaire (idUtilisateur plutôt que Utilisateur)
        $this->tableauRepository->ajouter($tableau);
    }

    /**
     * @throws ServiceException
     */
    public function metterAJourTableau(?int $idTableau, ?string $loginUtilisateurConnecte, ?string $nomtableau): void{
        $this->verifierLoginCorrect($loginUtilisateurConnecte);
        $this->verifierIdTableauCorrect($idTableau);
        $this->verifierNomTableauCorrect($nomtableau);

        /**
         * @var Tableau $tableau
         */
        $tableau = $this->tableauRepository->recupererParClePrimaire($idTableau);

        if(is_null($tableau)){
            throw new ServiceException( "Le tableau n'existe pas", Response::HTTP_NOT_FOUND);
        }
        if($tableau->estProprietaire($loginUtilisateurConnecte)){
            throw new ServiceException( "Seul le propriétaire du tableau peut mettre à jour le tableau", Response::HTTP_UNAUTHORIZED);
        }
        $tableau->setTitreTableau($nomtableau);
        $this->tableauRepository->mettreAJour($tableau);
    }

    /**
     * @throws ServiceException
     */
    public function ajouterMembre(?int $idTableau, ?string $loginUtilisateurConnecte, ?string $loginUtilisateurNouveau){
        $this->verifierLoginCorrect($loginUtilisateurConnecte);
        $this->verifierLoginCorrect($loginUtilisateurNouveau);
        $this->verifierIdTableauCorrect($idTableau);
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
        if($this->tableauRepository->estParticipantOuProprietaire($loginUtilisateurNouveau, $idTableau)){ // TODO: ajouter cette méthode dans les repository
            throw new ServiceException( "L'utilisateur est proprio ou participe déjà à ce talbeau", Response::HTTP_CONFLICT);
        }

        $this->tableauRepository->ajouterMembre($loginUtilisateurNouveau, $idTableau); // TODO : ajouter cette méthode dans les repository
    }

    /**
     * @throws ServiceException
     */
    public function supprimerMembre(?int $idTableau, ?string $loginUtilisateurConnecte, ?string $loginUtilisateurDelete){ // Fonction à vrm tester
        if(is_null($idTableau) ||is_null($loginUtilisateurConnecte) || strlen($loginUtilisateurConnecte) == 0 || is_null($loginUtilisateurDelete) || strlen($loginUtilisateurDelete) == 0){
            throw new ServiceException( "L'idTableau ou le login de l'user connecté ou le login a ajouté ne peut pas être vide", Response::HTTP_BAD_REQUEST);
        }

        /**
         * @var Tableau $tableau
         */
        $tableau = $this->tableauRepository->recupererParClePrimaire($idTableau);

        if(is_null($tableau)){
            throw new ServiceException( "Le tableau n'existe pas", Response::HTTP_NOT_FOUND);
        }
        // Si l'utilisateur connecté veut supprimer qq d'autre (seul le proprio peut supprimer dans ce cas)
        if($loginUtilisateurConnecte != $loginUtilisateurDelete){
            if(! $tableau->estProprietaire($loginUtilisateurConnecte)){
                throw new ServiceException( "Seul le propriétaire du tableau peut mettre supprimer des membres", Response::HTTP_UNAUTHORIZED);
            }
        }
        else{ // Ca signifie que l'utilisateur connecté est le même que celui à supprimer (il a le droite de quitter le tableau)
            if($tableau->estProprietaire($loginUtilisateurDelete)){
                throw new ServiceException( "Vous ne pouvez pas vous supprimer du tableau si vous êtes propriétaire", Response::HTTP_BAD_REQUEST);
            }
        }

        /**
         * @var Utilisateur $utilisateurNouveau
         */
        $utilisateurNouveau = $this->utilisateurRepository->recupererParClePrimaire($loginUtilisateurDelete);

        if(is_null($utilisateurNouveau)){
            throw new ServiceException( "L'utilisateur à supprimer n'existe pas", Response::HTTP_NOT_FOUND);
        }
        if(! $this->tableauRepository->estParticipantOuProprietaire($loginUtilisateurDelete, $idTableau)){ // TODO: ajouter cette méthode dans les repository. A voir si la fonction peut être simplifer (je pense que oui)
            throw new ServiceException( "L'utilisateur ne participe pas à ce talbeau", Response::HTTP_CONFLICT);
        }

        $this->carteRepository->supprimerAffectation($idTableau, $loginUtilisateurDelete); // TODO: ajotuer cette méthode dans les repository ou tout du moins faire en sorte que supprimerMembre supprime les affectations aux Cartes
        $this->tableauRepository->supprimerMembre($loginUtilisateurDelete, $idTableau); // TODO : ajouter cette méthode dans les repository
    }

    /**
     * @throws ServiceException
     */
    public function supprimer(?string $loginUtilisateurConnecte, ?int $idTableau): void
    {
        $this->verifierLoginCorrect($loginUtilisateurConnecte);
        $this->verifierIdTableauCorrect($idTableau);

        /**
         * @var Tableau $tableau
         */
        $tableau = $this->tableauRepository->recupererParClePrimaire($idTableau);

        if(is_null($tableau)){
            throw new ServiceException( "Le tableau n'existe pas", Response::HTTP_NOT_FOUND);
        }
        if(! $tableau->estProprietaire($loginUtilisateurConnecte)){
            throw new ServiceException( "Vous ne pouvez pas supprimer le tableau où vous n'êtes pas propriétaire", Response::HTTP_NOT_FOUND);
        }

        $this->carteRepository->supprimerCartesTableau($idTableau); // TODO: ajouter cette méthode dans les repository ou alors faire ceci dans supprimer de tableauRepository ?
        $this->colonneRepository->supprimerColonneTableau($idTableau); // TODO: ajouter cette méthode dans les repository ou alors faire ceci dans supprimer de tableauRepository ?
        $this->tableauRepository->supprimerUtilisateurParticipant($idTableau); // TODO: ajouter cette méthode dans les repository ou alors faire ceci dans supprimer de tableauRepository ?
        $this->tableauRepository->supprimer($idTableau);
    }
}