<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\MessageFlash;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ControleurGenerique {

    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
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

    protected function afficherVuePHP(string $cheminVue, array $parametres = []): Response {
        extract($parametres);
        $messagesFlash = MessageFlash::lireTousMessages();
        ob_start();
        require $this->container->getParameter('project_root'). "/src/vue/$cheminVue";
        $corpsReponse = ob_get_clean();
        return new Response($corpsReponse);
    }

    public function afficherVue(string $cheminVue, array $parametres = []): Response
    {
        /** @var Environment $twig */
        $twig = $this->container->get("twig");
        $corpsReponse = $twig->render($cheminVue, $parametres);
        return new Response($corpsReponse);
    }

    protected function rediriger(string $routeName, array $parameters = []) : RedirectResponse
    {
        $generateurUrl = $this->container->get("url_generator");
        $url = $generateurUrl->generate($routeName, $parameters);
        return new RedirectResponse($url);
    }

    // https://stackoverflow.com/questions/768431/how-do-i-make-a-redirect-in-php
    protected static function redirection(string $controleur = "", string $action = "", array $query = []) : void
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

    public function afficherErreur($messageErreur = "", $controleur = ""): Response
    {
        return ControleurGenerique::afficherVue('erreur.html.twig', [
            "messageErreur" => $messageErreur
        ]);
    }

    public static function issetAndNotNull(array $requestParams) : bool {
        foreach ($requestParams as $param) {
            if(!(isset($_REQUEST[$param]) && $_REQUEST[$param] != null)) {
                return false;
            }
        }
        return true;
    }
}