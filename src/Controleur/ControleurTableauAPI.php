<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\ConnexionUtilisateurInterface;
use App\Trellotrolle\Service\TableauServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ControleurTableauAPI extends ControleurGenerique
{
    public function __construct (
        ContainerInterface $container,
        private TableauServiceInterface $utilisateurService,
        private ConnexionUtilisateurInterface $connexionUtilisateurJWT
    )
    {
        parent::__construct($container);
    }
}