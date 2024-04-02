<?php

namespace App\Trellotrolle\Controleur;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Classe ControleurBase
 *
 * Cette classe est responsable de la gestion de la page d'accueil du site.
 *
 * @package App\Trellotrolle\Controleur
 */
class ControleurBase extends ControleurGenerique
{
    /**
     * Méthode accueil
     *
     * Cette méthode est responsable de l'affichage de la page d'accueil du site.
     *
     * @return Response La réponse HTTP contenant le rendu de la vue Twig "base/accueil.html.twig" avec le paramètre "pagetitle" défini comme "Accueil".
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    #[Route(path: '/', name: 'accueil', methods: ["GET", "POST"])]
    public function accueil(): Response
    {
        return $this->afficherTwig("base/accueil.html.twig", ["pagetitle" => "Accueil"]);
    }
}