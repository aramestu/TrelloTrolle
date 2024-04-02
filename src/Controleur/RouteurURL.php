<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\AttributeRouteControllerLoader;
use App\Trellotrolle\Lib\ConnexionUtilisateurSession;
use App\Trellotrolle\Lib\MessageFlash;
use Exception;
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

/**
 * Classe RouteurURL
 *
 * Cette classe est responsable du traitement des requêtes et de la gestion des routes.
 * Elle utilise le composant Symfony pour charger les routes à partir d'un répertoire et les associer à une URL.
 * Elle utilise également le composant Symfony pour résoudre les contrôleurs et les arguments des requêtes.
 * Elle utilise le conteneur de dépendances pour gérer les services et les paramètres.
 * Elle utilise Twig pour générer des URL et gérer les variables globales dans les templates.
 * Enfin, elle gère les exceptions liées aux routes non trouvées ou aux méthodes non autorisées.
 */
class RouteurURL
{

    /**
     * Méthode statique pour traiter une requête.
     *
     * Cette méthode prend en paramètre un objet Request et retourne un objet Response.
     * Elle crée un conteneur, charge les données de configuration à partir d'un fichier YAML,
     * récupère les routes à partir d'un répertoire, configure le contexte de la requête,
     * initialise le générateur d'URL et l'assistant d'URL, configure le moteur de templates Twig,
     * vérifie si l'utilisateur est connecté et récupère les messages flash.
     * Ensuite, elle tente de faire correspondre la requête à une route, résout le contrôleur et les arguments,
     * appelle le contrôleur avec les arguments et retourne la réponse.
     * En cas d'erreur, elle gère les exceptions ResourceNotFoundException, MethodNotAllowedException
     * et les autres exceptions génériques.
     *
     * @param Request $requete L'objet Request représentant la requête à traiter.
     * @return Response L'objet Response représentant la réponse à la requête.
     */
    public static function traiterRequete(Request $requete): Response
    {
        try {
            $conteneur = new ContainerBuilder();
            $conteneur->set('container', $conteneur);
            $conteneur->setParameter('project_root', __DIR__ . '/../..');

            //On indique au FileLocator de chercher à partir du dossier de configuration
            $loader = new YamlFileLoader($conteneur, new FileLocator(__DIR__ . "/../Configuration"));
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

            $twig = $conteneur->get('twig');
            $twig->addFunction(new TwigFunction("route", [$generateurUrl, "generate"]));
            $twig->addFunction(new TwigFunction("asset", [$assistantUrl, "getAbsoluteUrl"]));
            $connexionSess = new ConnexionUtilisateurSession();
            $twig->addGlobal('estConnecte', $connexionSess->estConnecte());
            $twig->addGlobal('loginUtilisateurConnecte', $connexionSess->getIdUtilisateurConnecte());
            $twig->addGlobal('messagesFlash', new MessageFlash());

            try {
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

            } catch (ResourceNotFoundException $exception) {
                // Remplacez xxx par le bon code d'erreur
                $reponse = $conteneur->get("controleur_generique")->afficherErreur($exception->getMessage(), 404);
            } catch (MethodNotAllowedException  $exception) {
                // Remplacez xxx par le bon code d'erreur
                $reponse = $conteneur->get("controleur_generique")->afficherErreur($exception->getMessage(), 405);
            } catch (Exception $exception) {
                ob_start();
                echo($exception->getMessage());
                $reponseStr = ob_get_clean();
                $reponse = new Response($reponseStr);
                //$reponse = $conteneur->get("controleur_generique")->afficherErreur($exception->getMessage()) ;
            }

        } catch (Exception $e) {
            $reponse = new Response($e->getMessage());
        }
        return $reponse;
    }
}