<?php

namespace App\Trellotrolle\Modele\DataObject;

/**
 * Classe abstraite AbstractDataObject.
 *
 * Cette classe représente un objet de données abstrait.
 * Elle contient deux méthodes abstraites : formatTableau() et construireDepuisTableau().
 * La méthode formatTableau() retourne un tableau représentant l'objet de données.
 * La méthode construireDepuisTableau() construit un objet de données à partir d'un tableau.
 *
 * @package App\Trellotrolle\Modele\DataObject
 */
abstract class AbstractDataObject
{
    /**
     * Méthode abstraite formatTableau()
     *
     * Cette méthode retourne un tableau représentant l'objet de données.
     *
     * @return array
     */
    public abstract function formatTableau(): array;

    /**
     * Méthode abstraite construireDepuisTableau()
     *
     * Cette méthode construit un objet de données à partir d'un tableau.
     *
     * @param array $objetFormatTableau Le tableau représentant l'objet de données
     * @return AbstractDataObject L'objet de données construit
     */
    public static abstract function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject;
}