<?php

////////////////////
// Initialisation //
////////////////////
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../vendor/autoload.php';


/////////////
// Routage //
/////////////
$requete = Request::createFromGlobals();
\App\Trellotrolle\Controleur\RouteurURL::traiterRequete($requete)->send();