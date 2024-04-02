<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;

/**
 * Interface TableauRepositoryInterface
 *
 * Cette interface définit les méthodes pour interagir avec la base de données pour la table "tableau".
 */
interface TableauRepositoryInterface
{

    /**
     * Méthode pour récupérer les tableaux d'un utilisateur.
     *
     * @param string $login Le login de l'utilisateur.
     * @return array Les tableaux de l'utilisateur.
     */
    public function recupererTableauxUtilisateur(string $login): array;

    /**
     * Méthode récupérerParCodeTableau()
     *
     * Cette méthode permet de récupérer un objet de données à partir d'un code tableau.
     *
     * @param string $codeTableau Le code tableau utilisé pour récupérer l'objet de données
     * @return AbstractDataObject|null L'objet de données récupéré, ou null s'il n'existe pas
     */
    public function recupererParCodeTableau(string $codeTableau): ?AbstractDataObject;

    /**
     * Méthode pour récupérer les tableaux auxquels un utilisateur participe.
     *
     * @param string $login Le login de l'utilisateur.
     * @return array Les tableaux auxquels l'utilisateur participe.
     */
    public function recupererTableauxParticipeUtilisateur(string $login): array;

    /**
     * Méthode pour récupérer les participants d'un tableau.
     *
     * @param string $idTableau L'identifiant du tableau.
     * @return array Les participants du tableau.
     */
    public function recupererParticipantsTableau(string $idTableau): array;

    /**
     * Méthode pour obtenir le nombre total de tableaux d'un utilisateur.
     *
     * @param string $login Le login de l'utilisateur.
     * @return int Le nombre total de tableaux de l'utilisateur.
     */
    public function getNombreTableauxTotalUtilisateur(string $login): int;

    /**
     * Méthode pour ajouter un participant à un tableau.
     *
     * @param string $login Le login du participant.
     * @param int $idTableau L'identifiant du tableau.
     * @return bool Retourne true si le participant a été ajouté avec succès, sinon false.
     */
    public function ajouterParticipant(string $login, int $idTableau): bool;

    /**
     * Méthode pour supprimer un participant d'un tableau.
     *
     * @param string $login Le login du participant à supprimer.
     * @param int $idTableau L'identifiant du tableau.
     * @return bool Retourne true si le participant a été supprimé avec succès, sinon false.
     */
    public function supprimerParticipant(string $login, int $idTableau): bool;

    /**
     * Méthode pour supprimer une affectation d'un utilisateur à un tableau.
     *
     * @param string $login Le login de l'utilisateur.
     * @param int $idTableau L'identifiant du tableau.
     * @return bool Retourne true si l'affectation a été supprimée avec succès, sinon false.
     */
    public function supprimerAffectation(string $login, int $idTableau): bool;

    /**
     * Méthode pour supprimer une entrée de la base de données en utilisant une valeur de clé primaire.
     *
     * @param string $valeurClePrimaire La valeur de la clé primaire utilisée pour supprimer l'entrée.
     * @return bool Retourne true si l'entrée a été supprimée avec succès, sinon false.
     */
    public function supprimer(string $valeurClePrimaire): bool;
}