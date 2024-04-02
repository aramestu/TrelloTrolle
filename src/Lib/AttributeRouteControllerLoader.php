<?php

namespace App\Trellotrolle\Lib;

use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\Routing\Loader\AttributeClassLoader;
use Symfony\Component\Routing\Route;

/**
 * Classe AttributeRouteControllerLoader
 *
 * Cette classe est une extension de la classe AttributeClassLoader et est utilisée pour configurer le paramètre _controller par défaut d'une instance de Route donnée.
 * Elle utilise la méthode configureRoute pour définir la valeur du paramètre _controller en utilisant le nom de la classe et le nom de la méthode.
 * La méthode toSnakeCase est utilisée pour convertir le nom du contrôleur en notation snake_case.
 */
class AttributeRouteControllerLoader extends AttributeClassLoader
{
    /**
     * Méthode configureRoute
     *
     * Cette méthode configure le paramètre _controller par défaut d'une instance de Route donnée.
     * Elle utilise la méthode toSnakeCase pour convertir le nom du contrôleur en notation snake_case.
     *
     * @param Route $route - L'instance de Route à configurer
     * @param ReflectionClass $class - L'objet \ReflectionClass représentant la classe
     * @param ReflectionMethod $method - L'objet \ReflectionMethod représentant la méthode
     * @param object $annot - L'annotation associée à la méthode
     * @return void
     */
    protected function configureRoute(Route $route, ReflectionClass $class, ReflectionMethod $method, object $annot): void
    {
        $route->setDefault('_controller', $this->toSnakeCase($class->getShortName()).'::'.$method->getName());
    }

    /**
     * Fonction toSnakeCase
     *
     * Cette fonction prend en paramètre le nom d'un contrôleur et retourne le nom en notation snake_case.
     *
     * @param string $controllerName - Le nom du contrôleur
     * @return string - Le nom du contrôleur en notation snake_case
     */
    private function toSnakeCase($controllerName) : string {
        return ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $controllerName)), '_');
    }

}