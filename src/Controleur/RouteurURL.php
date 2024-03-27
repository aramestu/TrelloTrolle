<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\AttributeRouteControllerLoader;
use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Lib\MessageFlash;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ContainerControllerResolver;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Loader\AttributeDirectoryLoader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Twig\TwigFunction;

class RouteurURL {
    public static function traiterRequete(Request $requete): Response {
        $conteneur = new ContainerBuilder();
        $conteneur->set('container', $conteneur);
        $conteneur->setParameter('project_root', __DIR__.'/../..');

        //On indique au FileLocator de chercher à partir du dossier de configuration
        $loader = new YamlFileLoader($conteneur, new FileLocator(__DIR__."/../Configuration"));
        //On remplit le conteneur avec les données fournies dans le fichier de configuration
        $loader->load("conteneur.yml");

        $fileLocator = new FileLocator(__DIR__);
        $attrClassLoader = new AttributeRouteControllerLoader();
        $routes = (new AttributeDirectoryLoader($fileLocator, $attrClassLoader))->load(__DIR__);

        $contexteRequete = (new RequestContext())->fromRequest($requete);
        //Après l'instanciation de l'objet $contexteRequete
        $conteneur->set('request_context', $contexteRequete);
        //Après que les routes soient récupérées
        $conteneur->set('routes', $routes);
        $generateurUrl = $conteneur->get("url_generator");
        $assistantUrl = $conteneur->get('url_helper');

        $twig=$conteneur->get('twig');
        $twig->addFunction(new TwigFunction("route", [$generateurUrl, "generate"]));
        $twig->addFunction(new TwigFunction("asset", [$assistantUrl, "getAbsoluteUrl"]));
        $twig->addGlobal('loginUtilisateurConnecte', ConnexionUtilisateur::getLoginUtilisateurConnecte());
        $twig->addGlobal('messagesFlash', new MessageFlash());

        try{
            $associateurUrl = new UrlMatcher($routes, $contexteRequete);
            $donneesRoute = $associateurUrl->match($requete->getPathInfo()); //Peut sortir les exceptions NoConfigurationException
            //ResourceNotFoundException
            //MethodNotAllowedException
            $requete->attributes->add($donneesRoute);

            $resolveurDeControleur = new ContainerControllerResolver($conteneur);
            $controleur = $resolveurDeControleur->getController($requete);

            $resolveurDArguments = new ArgumentResolver();
            $arguments = $resolveurDArguments->getArguments($requete, $controleur);

            $reponse = call_user_func_array($controleur, $arguments);

        }catch (ResourceNotFoundException $exception) {
            // Remplacez xxx par le bon code d'erreur
            $reponse = $conteneur->get("controleur_generique")->afficherErreur($exception->getMessage(), 404);
        } catch (MethodNotAllowedException  $exception) {
            // Remplacez xxx par le bon code d'erreur
            $reponse = $conteneur->get("controleur_generique")->afficherErreur($exception->getMessage(), 405);
        } catch (\Exception $exception) {
            echo($exception->getMessage());
            $reponse = $conteneur->get("controleur_generique")->afficherErreur($exception->getMessage()) ;
        }

        return $reponse;
    }

}