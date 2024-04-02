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

/**
 * Classe TableauService
 *
 * Cette classe est responsable de la gestion des tableaux dans l'application Trellotrolle.
 * Elle fournit des méthodes pour créer, mettre à jour, supprimer et récupérer des tableaux.
 * Elle gère également l'ajout et la suppression de membres dans un tableau, ainsi que la vérification des autorisations.
 * Elle permet également de récupérer les colonnes et les cartes d'un tableau, ainsi que les informations sur les affectations des cartes.
 * Enfin, elle fournit des méthodes pour quitter un tableau.
 */
class TableauService implements TableauServiceInterface
{
    public function __construct(private readonly TableauRepositoryInterface     $tableauRepository,
                                private readonly UtilisateurRepositoryInterface $utilisateurRepository,
                                private readonly CarteRepositoryInterface       $carteRepository,
                                private readonly ColonneRepositoryInterface     $colonneRepository,
                                private readonly MotDePasseInterface            $motDePasse)
    {
    }

    /**
     * Vérifie si le code du tableau est correct.
     *
     * @param string|null $codeTableau Le code du tableau à vérifier.
     * @return void
     * @throws ServiceException Si le tableau n'est pas renseigné.
     */
    private function verifierCodeTableauCorrect(?string $codeTableau): void
    {
        if (is_null($codeTableau) || strlen($codeTableau) == 0) {
            throw new ServiceException("Le tableau n'est pas renseigné", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Récupère un tableau en fonction de son code.
     *
     * @param string|null $codeTableau Le code du tableau à récupérer.
     * @return Tableau Le tableau correspondant au code.
     * @throws ServiceException Si le tableau n'existe pas.
     */
    public function getByCodeTableau(?string $codeTableau): Tableau
    {
        $this->verifierCodeTableauCorrect($codeTableau);
        /**
         * @var Tableau $tableau
         */
        $tableau = $this->tableauRepository->recupererParCodeTableau($codeTableau);

        if (is_null($tableau)) {
            throw new ServiceException("Le tableau n'existe pas", Response::HTTP_NOT_FOUND);
        }
        return $tableau;
    }

    /**
     * Vérifie si l'id du tableau est correct.
     *
     * @param int|null $idTableau L'id du tableau.
     * @throws ServiceException Si l'id du tableau n'est pas renseigné.
     * @return void
     */
    private function verifierIdTableauCorrect(?int $idTableau): void
    {
        if (is_null($idTableau)) {
            throw new ServiceException("L'idTableau n'est pas renseigné", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Récupère un tableau par son identifiant.
     *
     * @param int|null $idTableau L'identifiant du tableau.
     * @return Tableau Le tableau correspondant à l'identifiant.
     * @throws ServiceException Si le tableau n'existe pas.
     */
    public function getByIdTableau(?int $idTableau): Tableau
    {
        $this->verifierIdTableauCorrect($idTableau);
        /**
         * @var Tableau $tableau
         */
        $tableau = $this->tableauRepository->recupererParClePrimaire($idTableau);

        if (is_null($tableau)) {
            throw new ServiceException("Le tableau n'existe pas", Response::HTTP_NOT_FOUND);
        }
        return $tableau;
    }

    /**
     * Vérifie si le nom du tableau est correct.
     *
     * @param string|null $nomTableau Le nom du tableau à vérifier.
     * @return void
     * @throws ServiceException Si le nom du tableau est vide ou dépasse 64 caractères.
     */
    private function verifierNomTableauCorrect(?string $nomTableau): void
    {
        $nb = strlen($nomTableau);
        if (is_null($nomTableau) || $nb == 0 || $nb > 64) {
            throw new ServiceException("Le nom du tableau ne peut pas être vide et ne doit pas faire plus de 64 caractères", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Vérifie si le login est correct.
     *
     * @param string|null $login Le login à vérifier.
     * @return void
     * @throws ServiceException Si le login est vide ou a une longueur inférieure à 4 caractères ou supérieure à 32 caractères.
     */
    private function verifierLoginCorrect(?string $login): void
    {
        $nb = strlen($login);
        if (is_null($login) || $nb < 4 || $nb > 32) {
            throw new ServiceException("Le login ne peut pas être vide, et doit faire entre 4 et 32 caractères", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Crée un nouveau tableau avec un nom donné et un utilisateur connecté.
     *
     * @param string|null $loginUtilisateurConnecte Le login de l'utilisateur connecté.
     * @param string|null $nomTableau Le nom du tableau à créer.
     * @return Tableau Le tableau créé.
     * @throws ServiceException Si l'utilisateur n'existe pas.
     * @throws Exception Si une exception est levée.
     */
    public function creerTableau(?string $loginUtilisateurConnecte, ?string $nomTableau): Tableau
    {
        $this->verifierNomTableauCorrect($nomTableau);

        $this->verifierLoginCorrect($loginUtilisateurConnecte);

        /**
         * @var Utilisateur $utilisateur
         */
        $utilisateur = $this->utilisateurRepository->recupererParClePrimaire($loginUtilisateurConnecte);

        if (is_null($utilisateur)) {
            throw new ServiceException("L'utilisateur n'existe pas!", Response::HTTP_NOT_FOUND);
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
     * Met à jour un tableau.
     *
     * @param int|null $idTableau L'identifiant du tableau à mettre à jour.
     * @param string|null $loginUtilisateurConnecte Le login de l'utilisateur connecté.
     * @param string|null $nomtableau Le nouveau nom du tableau.
     * @return Tableau Le tableau mis à jour.
     * @throws ServiceException Si le tableau n'existe pas ou si l'utilisateur n'est pas le propriétaire du tableau.
     */
    public function mettreAJourTableau(?int $idTableau, ?string $loginUtilisateurConnecte, ?string $nomtableau): Tableau
    {
        $this->verifierLoginCorrect($loginUtilisateurConnecte);
        $this->verifierIdTableauCorrect($idTableau);
        $this->verifierNomTableauCorrect($nomtableau);

        /**
         * @var Tableau $tableau
         */
        $tableau = $this->tableauRepository->recupererParClePrimaire($idTableau);

        if (is_null($tableau)) {
            throw new ServiceException("Le tableau n'existe pas", Response::HTTP_NOT_FOUND);
        }
        if (!$tableau->estProprietaire($loginUtilisateurConnecte)) {
            throw new ServiceException("Seul le propriétaire du tableau peut mettre à jour le tableau!", Response::HTTP_UNAUTHORIZED);
        }
        $tableau->setTitreTableau($nomtableau);
        $this->tableauRepository->mettreAJour($tableau);

        return $tableau;
    }

    /**
     * Ajoute un membre à un tableau.
     *
     * @param int|null $idTableau L'identifiant du tableau.
     * @param string|null $loginUtilisateurConnecte Le login de l'utilisateur connecté.
     * @param string|null $loginUtilisateurNouveau Le login du nouvel utilisateur à ajouter.
     * @return Tableau Le tableau mis à jour.
     * @throws ServiceException Si une erreur se produit lors de l'ajout du membre.
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

        if (is_null($tableau)) {
            throw new ServiceException("Le tableau n'existe pas", Response::HTTP_NOT_FOUND);
        }
        if (!$tableau->estProprietaire($loginUtilisateurConnecte)) {
            throw new ServiceException("Seul le propriétaire du tableau peut ajouter des membres", Response::HTTP_UNAUTHORIZED);
        }

        /**
         * @var Utilisateur $utilisateurNouveau
         */
        $utilisateurNouveau = $this->utilisateurRepository->recupererParClePrimaire($loginUtilisateurNouveau);

        if (is_null($utilisateurNouveau)) {
            throw new ServiceException("L'utilisateur à ajouter n'existe pas", Response::HTTP_NOT_FOUND);
        }
        if ($tableau->estParticipantOuProprietaire($loginUtilisateurNouveau)) {
            throw new ServiceException("L'utilisateur est le propriétaire ou participe déjà à ce tableau", Response::HTTP_CONFLICT);
        }

        $this->tableauRepository->ajouterParticipant($loginUtilisateurNouveau, $idTableau);

        return $tableau;
    }

    /**
     * Supprime un membre d'un tableau.
     *
     * @param int|null $idTableau L'identifiant du tableau.
     * @param string|null $loginUtilisateurConnecte Le login de l'utilisateur connecté.
     * @param string|null $loginUtilisateurDelete Le login de l'utilisateur à supprimer.
     * @return Tableau Le tableau modifié.
     * @throws ServiceException Si l'idTableau, le login de l'utilisateur connecté ou le login à supprimer est vide.
     * @throws ServiceException Si le tableau n'existe pas.
     * @throws ServiceException Si seul le propriétaire du tableau peut supprimer des membres.
     * @throws ServiceException Si vous ne pouvez pas vous supprimer du tableau si vous êtes propriétaire.
     * @throws ServiceException Si l'utilisateur à supprimer n'existe pas.
     * @throws ServiceException Si l'utilisateur ne participe pas à ce tableau.
     */
    public function supprimerMembre(?int $idTableau, ?string $loginUtilisateurConnecte, ?string $loginUtilisateurDelete): Tableau
    {
        if (is_null($idTableau) || is_null($loginUtilisateurConnecte) || strlen($loginUtilisateurConnecte) == 0 || is_null($loginUtilisateurDelete) || strlen($loginUtilisateurDelete) == 0) {
            throw new ServiceException("L'idTableau ou le login de l'user connecté ou le login a ajouté ne peut pas être vide", Response::HTTP_BAD_REQUEST);
        }

        /**
         * @var Tableau $tableau
         */
        $tableau = $this->tableauRepository->recupererParClePrimaire($idTableau);
        if (is_null($tableau)) {
            throw new ServiceException("Le tableau n'existe pas", Response::HTTP_NOT_FOUND);
        }
        // Si l'utilisateur connecté veut supprimer qq d'autre (seul le proprio peut supprimer dans ce cas)
        if ($loginUtilisateurConnecte != $loginUtilisateurDelete) {
            if (!$tableau->estProprietaire($loginUtilisateurConnecte)) {
                throw new ServiceException("Seul le propriétaire du tableau peut supprimer des membres", Response::HTTP_UNAUTHORIZED);
            }
        } else { // Ca signifie que l'utilisateur connecté est le même que celui à supprimer (il a le droit de quitter le tableau)
            if ($tableau->estProprietaire($loginUtilisateurDelete)) {
                throw new ServiceException("Vous ne pouvez pas vous supprimer du tableau si vous êtes propriétaire", Response::HTTP_BAD_REQUEST);
            }
        }

        /**
         * @var Utilisateur $utilisateurNouveau
         */
        $utilisateurNouveau = $this->utilisateurRepository->recupererParClePrimaire($loginUtilisateurDelete);
        if (is_null($utilisateurNouveau)) {
            throw new ServiceException("L'utilisateur à supprimer n'existe pas", Response::HTTP_NOT_FOUND);
        }
        if (!$tableau->estParticipantOuProprietaire($loginUtilisateurDelete)) {
            throw new ServiceException("L'utilisateur ne participe pas à ce tableau", Response::HTTP_CONFLICT);
        }

        $this->carteRepository->supprimerAffectation($loginUtilisateurDelete, $idTableau);
        $this->tableauRepository->supprimerParticipant($loginUtilisateurDelete, $idTableau);

        return $tableau;
    }

    /**
     * Supprime un tableau.
     *
     * @param string|null $loginUtilisateurConnecte Le login de l'utilisateur connecté.
     * @param int|null $idTableau L'ID du tableau à supprimer.
     * @throws ServiceException Si le tableau n'existe pas ou si l'utilisateur n'est pas propriétaire du tableau.
     */
    public function supprimer(?string $loginUtilisateurConnecte, ?int $idTableau): void
    {
        $this->verifierLoginCorrect($loginUtilisateurConnecte);
        $this->verifierIdTableauCorrect($idTableau);

        /**
         * @var Tableau $tableau
         */
        $tableau = $this->tableauRepository->recupererParClePrimaire($idTableau);

        if (is_null($tableau)) {
            throw new ServiceException("Le tableau n'existe pas", Response::HTTP_NOT_FOUND);
        }
        if (!$tableau->estProprietaire($loginUtilisateurConnecte)) {
            throw new ServiceException("Vous ne pouvez pas supprimer le tableau où vous n'êtes pas propriétaire", Response::HTTP_NOT_FOUND);
        }

        $this->tableauRepository->supprimer($idTableau);
    }

    /**
     * Vérifie si l'utilisateur connecté est un participant ou le propriétaire du tableau.
     *
     * @param string|null $loginUtilisateurConnecte Le login de l'utilisateur connecté.
     * @param int|null $idTableau L'ID du tableau.
     * @throws ServiceException Si l'utilisateur n'est pas un participant du tableau.
     */
    public function verifierParticipant(?string $loginUtilisateurConnecte, ?int $idTableau): void
    {
        $this->verifierLoginCorrect($loginUtilisateurConnecte);
        $tableau = $this->getByIdTableau($idTableau);
        if (!$tableau->estParticipantOuProprietaire($loginUtilisateurConnecte)) {
            throw new ServiceException('Vous n\'êtes pas un participant de ce tableau.');
        }
    }

    /**
     * Vérifie si l'utilisateur connecté est le propriétaire du tableau.
     *
     * @param string|null $loginUtilisateurConnecte Le login de l'utilisateur connecté.
     * @param int|null $idTableau L'ID du tableau.
     * @return Tableau Le tableau correspondant.
     * @throws ServiceException Si l'utilisateur n'est pas le propriétaire du tableau.
     */
    public function verifierProprietaire(?string $loginUtilisateurConnecte, ?int $idTableau): Tableau
    {
        $this->verifierLoginCorrect($loginUtilisateurConnecte);
        $tableau = $this->getByIdTableau($idTableau);
        if (!$tableau->estProprietaire($loginUtilisateurConnecte)) {
            throw new ServiceException('Vous n\'êtes pas le propriétaire de ce tableau.');
        }
        return $tableau;
    }

    /**
     * Récupère les colonnes et les cartes d'un tableau.
     *
     * @param string $idTableau L'identifiant du tableau.
     * @return array Les colonnes et les cartes associées.
     */
    public function recupererColonnesEtCartesDuTableau(string $idTableau): array
    {
        $colonnes = $this->colonneRepository->recupererColonnesTableau($idTableau);
        $associationColonneCarte = array("colonnes" => $colonnes,
            "associations" => []);
        foreach ($colonnes as $colonne) {
            $associationColonneCarte["associations"][$colonne->getIdColonne()] = $this->carteRepository->recupererCartesColonne($colonne->getIdColonne());
        }
        return $associationColonneCarte;
    }

    /**
     * Récupère les informations sur les affectations des cartes d'un tableau.
     *
     * @param string $idTableau L'identifiant du tableau.
     * @return array Les informations sur les affectations des cartes, sous la forme d'un tableau associatif.
     *               Chaque clé du tableau correspond à un utilisateur, et chaque valeur est un tableau contenant
     *               les informations de l'utilisateur et le nombre d'affectations dans chaque colonne.
     */
    public function informationsAffectationsCartes(string $idTableau): array
    {
        /**
         * @var Carte[] $cartes
         */
        $infoAffectations = [];
        $cartes = $this->carteRepository->recupererCartesTableau($idTableau);
        foreach ($cartes as $carte) {
            foreach ($carte->getAffectationsCarte() as $utilisateur) {
                if (!isset($infoAffectations[$utilisateur->getLogin()])) {
                    $infoAffectations[$utilisateur->getLogin()] = ["infos" => $utilisateur, "colonnes" => []];
                }
                if (!isset($infoAffectations[$utilisateur->getLogin()]["colonnes"][$carte->getColonne()->getIdColonne()])) {
                    $infoAffectations[$utilisateur->getLogin()]["colonnes"][$carte->getColonne()->getIdColonne()] = 0;
                }
                $infoAffectations[$utilisateur->getLogin()]["colonnes"][$carte->getColonne()->getIdColonne()]++;
            }
        }
        return $infoAffectations;
    }

    /**
     * Quitte un tableau.
     *
     * @param string|null $loginUtilisateurConnecte Le login de l'utilisateur connecté.
     * @param int|null $idTableau L'ID du tableau.
     * @throws ServiceException Si l'utilisateur est propriétaire du tableau ou s'il n'appartient pas au tableau.
     */
    public function quitterTableau(?string $loginUtilisateurConnecte, ?int $idTableau): void
    {
        $tableau = $this->getByIdTableau($idTableau);

        if ($tableau->estProprietaire($loginUtilisateurConnecte))
            throw new ServiceException("Vous ne pouvez pas quitter un tableau qui vous appartient", Response::HTTP_FORBIDDEN);

        if (!$tableau->estParticipant($loginUtilisateurConnecte))
            throw new ServiceException("Vous n'appartenez pas à ce tableau", Response::HTTP_BAD_REQUEST);

        $this->carteRepository->supprimerAffectation($loginUtilisateurConnecte, $idTableau);
        $this->tableauRepository->supprimerParticipant($loginUtilisateurConnecte, $idTableau);
    }
}