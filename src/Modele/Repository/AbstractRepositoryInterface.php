<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;

/**
 * Interface AbstractRepositoryInterface
 *
 * Cette interface définit les méthodes nécessaires pour un repository abstrait.
 * Les méthodes récupérer(), récupérerParClePrimaire(), supprimer(), mettreAJour(), ajouter(), supprimerToutesAffectations() et supprimerToutesParticipation() sont définies.
 *
 * @package App\Trellotrolle\Modele\Repository
 */
interface AbstractRepositoryInterface
{

    /**
     * Méthode récupérer()
     *
     * Cette méthode retourne un tableau d'objets de type AbstractDataObject.
     *
     * @return AbstractDataObject[] Le tableau d'objets de type AbstractDataObject
     */
    public function recuperer(): array;

    /**
     * Méthode récupérerParClePrimaire()
     *
     * Cette méthode permet de récupérer un objet de données en utilisant la clé primaire comme critère de recherche.
     *
     * @param string $valeurClePrimaire La valeur de la clé primaire à rechercher
     * @return AbstractDataObject|null L'objet de données correspondant à la clé primaire, ou null si aucun objet n'est trouvé
     */
    public function recupererParClePrimaire(string $valeurClePrimaire): ?AbstractDataObject;

    /**
     * Méthode supprimer()
     *
     * Cette méthode supprime un objet en utilisant la valeur de la clé primaire comme critère de suppression.
     *
     * @param string $valeurClePrimaire La valeur de la clé primaire de l'objet à supprimer
     * @return bool Retourne true si l'objet est supprimé avec succès, sinon false
     */
    public function supprimer(string $valeurClePrimaire): bool;

    /**
     * Méthode mettreAJour()
     *
     * Cette méthode permet de mettre à jour un objet de données.
     *
     * @param AbstractDataObject $object L'objet de données à mettre à jour
     * @return void
     */
    public function mettreAJour(AbstractDataObject $object): void;

    /**
     * Méthode ajouter()
     *
     * Cette méthode permet d'ajouter un objet de données de type AbstractDataObject.
     *
     * @param AbstractDataObject $object L'objet de données à ajouter
     * @return bool Retourne true si l'ajout a réussi, sinon false
     */
    public function ajouter(AbstractDataObject $object): bool;

    /**
     * Méthode supprimerToutesAffectations()
     *
     * Cette méthode supprime toutes les affectations d'objets en utilisant une colonne et une valeur de colonne spécifiées comme critères de suppression.
     *
     * @param string $nomColonne Le nom de la colonne à utiliser comme critère de suppression
     * @param string $valeurColonne La valeur de la colonne à utiliser comme critère de suppression
     * @return bool Retourne true si les affectations sont supprimées avec succès, sinon false
     */
    public function supprimerToutesAffectations(string $nomColonne, string $valeurColonne): bool;

    /**
     * Méthode supprimerToutesParticipation()
     *
     * Cette méthode supprime toutes les participations d'objets en utilisant une colonne et une valeur de colonne spécifiées comme critères de suppression.
     *
     * @param string $nomColonne Le nom de la colonne à utiliser comme critère de suppression
     * @param string $valeurColonne La valeur de la colonne à utiliser comme critère de suppression
     * @return bool Retourne true si les participations sont supprimées avec succès, sinon false
     */
    public function supprimerToutesParticipation(string $nomColonne, string $valeurColonne): bool;
}