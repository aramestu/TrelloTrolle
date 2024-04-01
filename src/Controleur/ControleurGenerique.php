<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\MessageFlash;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Classe ControleurGenerique
 *
 * Cette classe est une classe générique pour les contrôleurs.
 * Elle contient des méthodes pour afficher des vues, effectuer des redirections et vérifier si des paramètres sont définis et non nuls.
 * Elle utilise le conteneur de dépendances pour accéder aux services nécessaires.
 */
class ControleurGenerique
{

    protected ContainerInterface $container;

    /**
     * Constructeur de la classe.
     *
     * @param ContainerInterface $container L'interface du conteneur de dépendances.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Méthode afficherTwig
     *
     * Cette méthode affiche une vue Twig en utilisant le service Twig du conteneur de dépendances.
     *
     * @param string $cheminVue Le chemin de la vue Twig à afficher.
     * @param array $parametres Les paramètres à passer à la vue Twig.
     * @return Response La réponse HTTP contenant le corps de la vue Twig rendue.
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    protected function afficherTwig(string $cheminVue, array $parametres = []): Response
    {
        /** @var Environment $twig */
        $twig = $this->container->get("twig");
        $corpsReponse = $twig->render($cheminVue, $parametres);
        return new Response($corpsReponse);
    }

    /**
     * Méthode afficherVuePHP
     *
     * Cette méthode affiche une vue PHP en utilisant le service Twig du conteneur de dépendances.
     *
     * @param string $cheminVue Le chemin de la vue PHP à afficher.
     * @param array $parametres Les paramètres à passer à la vue PHP.
     * @return Response La réponse HTTP contenant le corps de la vue PHP rendue.
     */
    protected function afficherVuePHP(string $cheminVue, array $parametres = []): Response
    {
        extract($parametres);
        $messagesFlash = MessageFlash::lireTousMessages();
        ob_start();
        require $this->container->getParameter('project_root') . "/src/vue/$cheminVue";
        $corpsReponse = ob_get_clean();
        return new Response($corpsReponse);
    }

    /**
     * Méthode afficherVue
     *
     * Cette méthode affiche une vue en utilisant le service Twig du conteneur de dépendances.
     *
     * @param string $cheminVue Le chemin de la vue à afficher.
     * @param array $parametres Les paramètres à passer à la vue.
     * @return Response La réponse HTTP contenant le corps de la vue rendue.
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function afficherVue(string $cheminVue, array $parametres = []): Response
    {
        /** @var Environment $twig */
        $twig = $this->container->get("twig");
        $corpsReponse = $twig->render($cheminVue, $parametres);
        return new Response($corpsReponse);
    }

    /**
     * Méthode rediriger
     *
     * Cette méthode redirige vers une autre route en utilisant le service "url_generator" du conteneur de dépendances.
     *
     * @param string $routeName Le nom de la route vers laquelle rediriger.
     * @param array $parameters Les paramètres à passer à la route.
     * @return RedirectResponse La réponse HTTP de redirection vers la nouvelle route.
     */
    protected function rediriger(string $routeName, array $parameters = []): RedirectResponse
    {
        $generateurUrl = $this->container->get("url_generator");
        $url = $generateurUrl->generate($routeName, $parameters);
        return new RedirectResponse($url);
    }

    // https://stackoverflow.com/questions/768431/how-do-i-make-a-redirect-in-php

    /**
     * Méthode redirection
     *
     * Cette méthode effectue une redirection vers une autre page en utilisant la fonction header() de PHP.
     *
     * @param string $controleur Le nom du contrôleur vers lequel rediriger.
     * @param string $action Le nom de l'action vers laquelle rediriger.
     * @param array $query Les paramètres de la requête à passer dans l'URL de redirection.
     * @return void
     */
    #[NoReturn] protected static function redirection(string $controleur = "", string $action = "", array $query = []): void
    {
        $queryString = [];
        if ($action != "") {
            $queryString[] = "action=$action";
        }
        if ($controleur != "") {
            $queryString[] = "controleur=$controleur";
        }
        foreach ($query as $name => $value) {
            $name = rawurlencode($name);
            $value = rawurlencode($value);
            $queryString[] = "$name=$value";
        }
        $url = "Location: ./controleurFrontal.php?" . join("&", $queryString);
        header($url);
        exit();
    }

    /**
     * /**
     * Méthode afficherErreur
     *
     * Cette méthode affiche une vue Twig d'erreur en utilisant la méthode afficherTwig de la classe ControleurGenerique.
     *
     * @param string $messageErreur Le message d'erreur à afficher dans la vue Twig.
     * @param string $controleur Le nom du contrôleur (optionnel).
     * @return Response La réponse HTTP contenant le corps de la vue Twig d'erreur rendue.
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */

    public function afficherErreur(string $messageErreur = "", string $controleur = ""): Response
    {
        return ControleurGenerique::afficherTwig('erreur.html.twig', [
            "messageErreur" => $messageErreur
        ]);
    }

    /**
     * Méthode issetAndNotNull
     *
     * Cette méthode vérifie si les paramètres de la requête sont définis et non nuls.
     *
     * @param array $requestParams Les paramètres de la requête à vérifier.
     * @return bool Retourne true si tous les paramètres sont définis et non nuls, sinon retourne false.
     */
    public static function issetAndNotNull(array $requestParams): bool
    {
        foreach ($requestParams as $param) {
            if (!(isset($_REQUEST[$param]) && $_REQUEST[$param] != null)) {
                return false;
            }
        }
        return true;
    }
}