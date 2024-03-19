<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\MessageFlash;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class ControleurGenerique {

    public function __construct(private ContainerInterface $container)
    {}

    protected function afficherVue(string $cheminVue, array $parametres = []): Response {
        extract($parametres);
        $messagesFlash = MessageFlash::lireTousMessages();
        ob_start();
        require $this->container->getParameter('project_root'). "/src/vue/$cheminVue";
        $corpsReponse = ob_get_clean();
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

    public static function afficherErreur($messageErreur = "", $controleur = ""): void
    {
        $messageErreurVue = "Problème";
        if ($controleur !== "")
            $messageErreurVue .= " avec le contrôleur $controleur";
        if ($messageErreur !== "")
            $messageErreurVue .= " : $messageErreur";

        ControleurGenerique::afficherVue('vueGenerale.php', [
            "pagetitle" => "Problème",
            "cheminVueBody" => "erreur.php",
            "messageErreur" => $messageErreurVue
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