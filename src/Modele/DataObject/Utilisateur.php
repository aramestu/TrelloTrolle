<?php

namespace App\Trellotrolle\Modele\DataObject;

use JsonSerializable;

/**
 * Classe Utilisateur
 *
 * Cette classe représente un utilisateur avec les propriétés suivantes :
 * - login : le login de l'utilisateur
 * - nom : le nom de l'utilisateur
 * - prenom : le prénom de l'utilisateur
 * - email : l'email de l'utilisateur
 * - mdpHache : le mot de passe haché de l'utilisateur
 *
 * Elle implémente l'interface \JsonSerializable.
 *
 * @package App\Trellotrolle\Modele\DataObject
 */
class Utilisateur extends AbstractDataObject implements JsonSerializable
{
    /**
     * Déclaration des propriétés privées de la classe.
     *
     * @var string $login Le login de l'utilisateur
     * @var string $nom Le nom de l'utilisateur
     * @var string $prenom Le prénom de l'utilisateur
     * @var string $email L'email de l'utilisateur
     * @var string $mdpHache Le mot de passe haché de l'utilisateur
     */
    private string $login;
    private string $nom;
    private string $prenom;
    private string $email;
    private string $mdpHache;

    public function __construct()
    {
    }

    /**
     * Fonction create
     *
     * Cette fonction statique crée et retourne une instance de la classe Utilisateur.
     *
     * @param string $login Le login de l'utilisateur
     * @param string $nom Le nom de l'utilisateur
     * @param string $prenom Le prénom de l'utilisateur
     * @param string $email L'email de l'utilisateur
     * @param string $mdpHache Le mot de passe haché de l'utilisateur
     * @return Utilisateur L'instance de la classe Utilisateur créée
     */
    public static function create(string $login, string $nom, string $prenom, string $email, string $mdpHache): Utilisateur
    {
        $u = new Utilisateur();
        $u->login = $login;
        $u->nom = $nom;
        $u->prenom = $prenom;
        $u->email = $email;
        $u->mdpHache = $mdpHache;
        return $u;
    }

    /**
     * Fonction construireDepuisTableau
     *
     * Cette fonction statique construit et retourne une instance de la classe Utilisateur à partir d'un tableau de données.
     *
     * @param array $objetFormatTableau Le tableau de données contenant les informations de l'utilisateur
     * @return Utilisateur L'instance de la classe Utilisateur créée
     */
    public static function construireDepuisTableau(array $objetFormatTableau): Utilisateur
    {
        return Utilisateur::create(
            $objetFormatTableau["login"],
            $objetFormatTableau["nomutilisateur"],
            $objetFormatTableau["prenomutilisateur"],
            $objetFormatTableau["emailutilisateur"],
            $objetFormatTableau["mdphache"],
        );
    }

    /**
     * Fonction construireUtilisateursDepuisJson
     *
     * Cette fonction statique construit et retourne un tableau d'utilisateurs à partir d'une liste JSON.
     *
     * @param string|null $jsonList La liste JSON contenant les informations des utilisateurs
     * @return array Le tableau d'utilisateurs construit à partir de la liste JSON
     */
    public static function construireUtilisateursDepuisJson(?string $jsonList): array
    {
        $users = [];
        if ($jsonList != null) {
            $aff = json_decode($jsonList, true);
            $utilisateurs = $aff["utilisateurs"] ?? [];
            foreach ($utilisateurs as $utilisateur) {
                $users[] = Utilisateur::construireDepuisTableau($utilisateur);
            }
        }
        return $users;
    }

    /**
     * Méthode getLogin
     *
     * Cette méthode retourne le login de l'utilisateur.
     *
     * @return string Le login de l'utilisateur
     */
    public function getLogin(): string
    {
        return $this->login;
    }

    /**
     * Méthode setLogin
     *
     * Cette méthode définit le login de l'utilisateur.
     *
     * @param string $login Le login de l'utilisateur
     * @return void
     */
    public function setLogin(string $login): void
    {
        $this->login = $login;
    }

    /**
     * Méthode getNom
     *
     * Cette méthode retourne le nom de l'objet.
     *
     * @return string Le nom de l'objet
     */
    public function getNom(): string
    {
        return $this->nom;
    }

    /**
     * Méthode setNom
     *
     * Cette méthode définit le nom de l'objet.
     *
     * @param string $nom Le nom de l'objet
     * @return void
     */
    public function setNom(string $nom): void
    {
        $this->nom = $nom;
    }

    /**
     * Méthode getPrenom
     *
     * Cette méthode retourne le prénom.
     *
     * @return string Le prénom
     */
    public function getPrenom(): string
    {
        return $this->prenom;
    }

    /**
     * Méthode setPrenom
     *
     * Cette méthode définit le prénom.
     *
     * @param string $prenom Le prénom
     * @return void
     */
    public function setPrenom(string $prenom): void
    {
        $this->prenom = $prenom;
    }

    /**
     * Méthode getMdpHache
     *
     * Cette méthode retourne le mot de passe haché.
     *
     * @return string Le mot de passe haché
     */
    public function getMdpHache(): string
    {
        return $this->mdpHache;
    }

    /**
     * Méthode setMdpHache
     *
     * Cette méthode définit le mot de passe haché.
     *
     * @param string $mdpHache Le mot de passe haché
     * @return void
     */
    public function setMdpHache(string $mdpHache): void
    {
        $this->mdpHache = $mdpHache;
    }

    /**
     * Méthode getEmail
     *
     * Cette méthode retourne l'email de l'utilisateur.
     *
     * @return string L'email de l'utilisateur
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Méthode setEmail
     *
     * Cette méthode définit l'email de l'utilisateur.
     *
     * @param string $email L'email de l'utilisateur
     * @return void
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * Fonction formatJsonListeUtilisateurs
     *
     * Cette fonction formate un tableau d'utilisateurs en une liste JSON.
     *
     * @param array $utilisateurs Le tableau d'utilisateurs à formater
     * @return string La liste JSON des utilisateurs formatée
     */
    public static function formatJsonListeUtilisateurs(array $utilisateurs): string
    {
        $utilisateursToJson = [];
        foreach ($utilisateurs as $utilisateur) {
            $utilisateursToJson[] = $utilisateur->formatTablleauUtilisateurPourJson();
        }
        return json_encode(["utilisateurs" => $utilisateursToJson]);
    }

    /**
     * Méthode formatTableau
     *
     * Cette méthode retourne un tableau formaté avec les propriétés de l'objet Utilisateur.
     *
     * @return array Le tableau formaté avec les propriétés de l'objet Utilisateur
     */
    public function formatTableau(): array
    {
        return array(
            "loginTag" => $this->login ?? null,
            "nomUtilisateurTag" => $this->nom ?? null,
            "prenomUtilisateurTag" => $this->prenom ?? null,
            "emailUtilisateurTag" => $this->email ?? null,
            "mdpHacheTag" => $this->mdpHache ?? null,
        );
    }

    /**
     * Méthode jsonSerialize
     *
     * Cette méthode retourne un tableau associatif contenant les propriétés "login", "nom", "prenom" et "email" de l'objet.
     *
     * @return array Le tableau associatif contenant les propriétés de l'objet
     */
    public function jsonSerialize(): array
    {
        return [
            "login" => $this->login ?? null,
            "nom" => $this->nom ?? null,
            "prenom" => $this->prenom ?? null,
            "email" => $this->email ?? null
        ];
    }
}