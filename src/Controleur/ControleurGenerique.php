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

    public function __construct(private ContainerInterface $container)
    {}

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

    protected function afficherVue(string $cheminVue, array $parametres = []): Response {
        extract($parametres);
        $messagesFlash = MessageFlash::lireTousMessages(); // TODO: injecter dépendance
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

    public function afficherErreur($messageErreur = "", $controleur = ""): Response
    {
        $messageErreurVue = "Problème";
        if ($controleur !== "")
            $messageErreurVue .= " avec le contrôleur $controleur";
        if ($messageErreur !== "")
            $messageErreurVue .= " : $messageErreur";

        return ControleurGenerique::afficherVue('vueGenerale.php', [
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