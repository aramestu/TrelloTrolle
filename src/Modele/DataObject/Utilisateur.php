<?php

namespace App\Trellotrolle\Modele\DataObject;

class Utilisateur extends AbstractDataObject implements \JsonSerializable
{
    private string $login;
    private string $nom;
    private string $prenom;
    private string $email;
    private string $mdpHache;
    public function __construct(){}

    public static function create(string $login, string $nom, string $prenom, string $email, string $mdpHache) : Utilisateur {
        $u = new Utilisateur();
        $u->login = $login;
        $u->nom = $nom;
        $u->prenom = $prenom;
        $u->email = $email;
        $u->mdpHache = $mdpHache;
        return $u;
    }

    public static function construireDepuisTableau(array $objetFormatTableau) : Utilisateur {
        return Utilisateur::create(
            $objetFormatTableau["login"],
            $objetFormatTableau["nomUtilisateur"],
            $objetFormatTableau["prenomUtilisateur"],
            $objetFormatTableau["emailUtilisateur"],
            $objetFormatTableau["mdpHache"],
        );
    }

    public static function construireUtilisateursDepuisJson(?string $jsonList) : array {
        $users = [];
        if($jsonList != null) {
            $aff = json_decode($jsonList, true);
            $utilisateurs = $aff["utilisateurs"] ?? [];
            foreach ($utilisateurs as $utilisateur) {
                $users[] = Utilisateur::construireDepuisTableau($utilisateur);
            }
        }
        return $users;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function setLogin(string $login): void
    {
        $this->login = $login;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): void
    {
        $this->nom = $nom;
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): void
    {
        $this->prenom = $prenom;
    }

    public function getMdpHache(): string
    {
        return $this->mdpHache;
    }

    public function setMdpHache(string $mdpHache): void
    {
        $this->mdpHache = $mdpHache;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public static function formatJsonListeUtilisateurs($utilisateurs) : string {
        $utilisateursToJson = [];
        foreach ($utilisateurs as $utilisateur) {
            $utilisateursToJson[] = $utilisateur->formatTablleauUtilisateurPourJson();
        };
        return json_encode(["utilisateurs" => $utilisateursToJson]);
    }

    public function formatTableau(): array
    {
        return array(
            "loginTag" => $this->login,
            "nomTag" => $this->nom,
            "prenomTag" => $this->prenom,
            "emailTag" => $this->email,
            "mdpHacheTag" => $this->mdpHache,
        );
    }

    public function jsonSerialize() : array
    {
        return [
            "login" => $this->login,
            "nom" => $this->nom,
            "prenom" => $this->prenom,
            "email" => $this->email,
            "mdpHache" => $this->mdpHache,
        ];
    }
}