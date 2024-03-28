<?php

namespace App\Trellotrolle\Modele\DataObject;

abstract class AbstractDataObject
{
    public abstract function formatTableau(): array;
    public static abstract function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject;

}
