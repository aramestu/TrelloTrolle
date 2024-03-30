<?php
namespace App\Trellotrolle\Service;
use App\Trellotrolle\Lib\MotDePasseInterface;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\CarteRepositoryInterface;
use App\Trellotrolle\Modele\Repository\ColonneRepositoryInterface;
use App\Trellotrolle\Modele\Repository\TableauRepositoryInterface;
use App\Trellotrolle\Modele\Repository\UtilisateurRepositoryInterface;
use App\Trellotrolle\Service\Exception\ServiceException;
use Exception;
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
    private function verifierLoginCorrect(?string $login): void
    {
        $nb = strlen($login);
        if(is_null($login) || $nb < 4 || $nb > 32){
            throw new ServiceException( "Le login ne peut pas être vide, et doit daire entre 4 et 32 caractères", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @throws ServiceException
     * @throws Exception
     */
    public function creerTableau(?string $loginUtilisateurConnecte, ?string $nomTableau): Tableau{
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

        $tableau = new Tableau();
        $tableau->setCodeTableau($codeTableauHache);
        $tableau->setTitreTableau($nomTableau);
        $tableau->setProprietaireTableau($utilisateur);
        $this->tableauRepository->ajouter($tableau);
        return $tableau;
    }

    /**
     * @throws ServiceException
     */
    public function mettreAJourTableau(?int $idTableau, ?string $loginUtilisateurConnecte, ?string $nomtableau): Tableau{
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
            throw new ServiceException( "Seul le propriétaire du tableau peut mettre à jour le tableau!", Response::HTTP_UNAUTHORIZED);
        }
        $tableau->setTitreTableau($nomtableau);
        $this->tableauRepository->mettreAJour($tableau);

        return $tableau;
    }

    /**
     * @throws ServiceException
     */
    public function ajouterMembre(?int $idTableau, ?string $loginUtilisateurConnecte, ?string $loginUtilisateurNouveau): Tableau
    {
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
        if($tableau->estParticipantOuProprietaire($loginUtilisateurNouveau)){
            throw new ServiceException( "L'utilisateur est proprio ou participe déjà à ce talbeau", Response::HTTP_CONFLICT);
        }

        $this->tableauRepository->ajouterParticipant($loginUtilisateurNouveau, $idTableau);

        return $tableau;
    }

    /**
     * @throws ServiceException
     */
    public function supprimerMembre(?int $idTableau, ?string $loginUtilisateurConnecte, ?string $loginUtilisateurDelete): Tableau
    {
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
        else{ // Ca signifie que l'utilisateur connecté est le même que celui à supprimer (il a le droit de quitter le tableau)
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
        if(! $tableau->estParticipantOuProprietaire($loginUtilisateurDelete)){
            throw new ServiceException( "L'utilisateur ne participe pas à ce talbeau", Response::HTTP_CONFLICT);
        }

        $this->carteRepository->supprimerAffectation($idTableau, $loginUtilisateurDelete);
        $this->tableauRepository->supprimerParticipant($loginUtilisateurDelete, $idTableau);

        return $tableau;
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

        $this->tableauRepository->supprimer($idTableau);
    }

    public function verifierParticipant(?string $loginUtilisateurConnecte, ?int $idTableau): void
    {
        $this->verifierLoginCorrect($loginUtilisateurConnecte);
        $tableau = $this->getByIdTableau($idTableau);
        if(!$tableau->estParticipantOuProprietaire($loginUtilisateurConnecte))
        {
            throw new ServiceException('Vous n\'êtes pas un participant de ce tableau.');
        }
    }

    /**
     * @throws ServiceException
     */
    public function verifierProprietaire(?string $loginUtilisateurConnecte, ?int $idTableau): Tableau
    {
        $this->verifierLoginCorrect($loginUtilisateurConnecte);
        $tableau = $this->getByIdTableau($idTableau);
        if(!$tableau->estProprietaire($loginUtilisateurConnecte))
        {
            throw new ServiceException('Vous n\'êtes pas le propriétaire de ce tableau.');
        }
        return $tableau;
    }

    public function recupererColonnesEtCartesDuTableau(string $idTableau): array
    {
        $colonnes = $this->colonneRepository->recupererColonnesTableau($idTableau);
        $associationColonneCarte = array("colonnes" => $colonnes,
                                        "associations" => []);
        foreach ($colonnes as $colonne){
            $associationColonneCarte["associations"][$colonne->getIdColonne()] = $this->carteRepository->recupererCartesColonne($colonne->getIdColonne());
        }
        return $associationColonneCarte;
    }

    public function informationsAffectationsCartes(string $idTableau): array
    {
        /**
         * @var Carte[] $cartes
         */
        $infoAffectations = [];
        $cartes = $this->carteRepository->recupererCartesTableau($idTableau);
        foreach ($cartes as $carte) {
            foreach ($carte->getAffectationsCarte() as $utilisateur) {
                if(!isset($infoAffectations[$utilisateur->getLogin()])) {
                    $infoAffectations[$utilisateur->getLogin()] = ["infos" => $utilisateur, "colonnes" => []];
                }
                if(!isset($infoAffectations[$utilisateur->getLogin()]["colonnes"][$carte->getColonne()->getIdColonne()])) {
                    $infoAffectations[$utilisateur->getLogin()]["colonnes"][$carte->getColonne()->getIdColonne()] = 0;
                }
                $infoAffectations[$utilisateur->getLogin()]["colonnes"][$carte->getColonne()->getIdColonne()]++;
            }
        }
        return $infoAffectations;
    }

    /**
     * @throws ServiceException
     */
    public function recupererUtilisateursPasMembreOuPasProprietaireTableau(Tableau $tableau){
        $utilisateurs = $this->utilisateurRepository->recupererUtilisateursOrderedPrenomNom();
        $filtredUtilisateurs = array_filter($utilisateurs, function ($u) use ($tableau) {return !$tableau->estParticipantOuProprietaire($u->getLogin());});

        if(empty($filtredUtilisateurs)) {
            throw new ServiceException( "Il n'est pas possible d'ajouter plus de membre à ce tableau !", Response::HTTP_BAD_REQUEST);
        }
        return $filtredUtilisateurs;
    }
}