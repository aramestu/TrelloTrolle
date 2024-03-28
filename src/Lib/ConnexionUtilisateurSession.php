<?php

namespace App\Trellotrolle\Lib;

use App\Trellotrolle\Lib\ConnexionUtilisateurInterface;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\HTTP\Session;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;

class ConnexionUtilisateurSession implements ConnexionUtilisateurInterface
{
    private string $cleConnexion = "_utilisateurConnecte";

    // Note : Classe trop couplée avec les sessions
    public function connecter(string $idUtilisateur): void
    {
        $session = Session::getInstance();
        $session->enregistrer($this->cleConnexion, $idUtilisateur);
    }

    public function estConnecte(): bool
    {
        $session = Session::getInstance();
        return $session->contient($this->cleConnexion);
    }

    public function deconnecter()
    {
        $session = Session::getInstance();
        $session->supprimer($this->cleConnexion);
    }

    public function getIdUtilisateurConnecte(): ?string
    {
        $session = Session::getInstance();
        if ($session->contient($this->cleConnexion)) {
            return $session->lire($this->cleConnexion);
        } else
            return null;
    }
}
