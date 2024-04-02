<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Classe UtilisateurRepository
 *
 * Cette classe est une implémentation de l'interface UtilisateurRepositoryInterface.
 * Elle gère les opérations de récupération et de manipulation des utilisateurs dans la base de données.
 *
 * @package App\Trellotrolle\Modele\Repository
 */
class UtilisateurRepository extends AbstractRepository implements UtilisateurRepositoryInterface
{

    /**
     * Constructeur de la classe.
     *
     * @param ContainerInterface $container L'instance du conteneur d'injection de dépendances.
     * @param ConnexionBaseDeDonneesInterface $connexionBaseDeDonnees L'instance de la connexion à la base de données.
     */
    public function __construct(private ContainerInterface $container, private ConnexionBaseDeDonneesInterface $connexionBaseDeDonnees)
    {
        parent::__construct($connexionBaseDeDonnees);
    }

    /**
     * Renvoie le nom de la table utilisée dans la base de données.
     *
     * @return string Le nom de la table "Utilisateurs".
     */
    protected function getNomTable(): string
    {
        return "Utilisateurs";
    }

    /**
     * Retourne le nom de la clé utilisée dans la table.
     *
     * @return string Le nom de la clé "login".
     */
    protected function getNomCle(): string
    {
        return "login";
    }

    /**
     * Retourne un tableau contenant les noms des colonnes utilisées dans la méthode.
     *
     * @return array Les noms des colonnes : "login", "nomUtilisateur", "prenomUtilisateur", "emailUtilisateur", "mdpHache".
     */
    protected function getNomsColonnes(): array
    {
        return ["login", "nomUtilisateur", "prenomUtilisateur", "emailUtilisateur", "mdpHache"];
    }

    /**
     * Méthode estAutoIncremente
     *
     * Cette méthode retourne un booléen indiquant si l'attribut auto-increment est activé pour la table.
     *
     * @return bool True si l'attribut auto-increment est activé, false sinon.
     */
    protected function estAutoIncremente(): bool
    {
        return false;
    }

    /**
     * Méthode protégée construireDepuisTableau
     *
     * Cette méthode protégée construit et retourne un objet de type AbstractDataObject à partir d'un tableau de données.
     *
     * @param array $objetFormatTableau Le tableau de données contenant les informations de l'objet
     * @return AbstractDataObject L'objet de type AbstractDataObject construit
     */
    protected function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject
    {
        return Utilisateur::construireDepuisTableau($objetFormatTableau);
    }

    /**
     * Récupère les utilisateurs par email.
     *
     * @param string $email L'email des utilisateurs à récupérer.
     * @return array Les utilisateurs correspondants à l'email donné.
     */
    public function recupererUtilisateursParEmail(string $email): array
    {
        return $this->recupererPlusieursPar("emailUtilisateur", $email);
    }

    /**
     * Récupère les utilisateurs triés par prénom puis par nom.
     *
     * @return array Les utilisateurs triés.
     */
    public function recupererUtilisateursOrderedPrenomNom(): array
    {
        return $this->recupererOrdonne(["prenomUtilisateur", "nomUtilisateur"]);
    }

    /**
     * Méthode recupererTableauxOuUtilisateurEstMembre
     *
     * Cette méthode récupère les tableaux où l'utilisateur est membre.
     *
     * @param string $login Le login de l'utilisateur.
     * @return array Les tableaux où l'utilisateur est membre.
     */
    public function recupererTableauxOuUtilisateurEstMembre(string $login): array
    {
        $tableauRepository = $this->container->get("tableau_repository");
        $nomColonnes = $tableauRepository->formatNomsColonnes();
        $sql = "SELECT DISTINCT  t.$nomColonnes FROM Tableaux t
                LEFT JOIN participer p ON t.idTableau = p.idtableau
                WHERE p.login = :loginTag
                OR t.proprietairetableau = :loginTag
                ";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $values = ["loginTag" => $login];
        $pdoStatement->execute($values);
        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $tableauRepository->construireDepuisTableau($objetFormatTableau);
        }

        return $objets;
    }

    /**
     * Supprime un utilisateur en fonction de sa valeur de clé primaire.
     *
     * Cette méthode supprime toutes les affectations et participations associées à l'utilisateur.
     * Ensuite, elle supprime tous les tableaux auxquels l'utilisateur est membre.
     * Enfin, elle appelle la méthode parente pour supprimer l'utilisateur de la base de données.
     *
     * @param string $valeurClePrimaire La valeur de la clé primaire de l'utilisateur à supprimer.
     * @return bool Retourne true si l'utilisateur a été supprimé avec succès, sinon false.
     */
    public function supprimer(string $valeurClePrimaire): bool
    {
        $this->supprimerToutesAffectations("login", $valeurClePrimaire);
        $this->supprimerToutesParticipation("login", $valeurClePrimaire);

        $tableaux = $this->recupererTableauxOuUtilisateurEstMembre($valeurClePrimaire);
        $tableauRepository = $this->container->get("tableau_repository");
        foreach ($tableaux as $tableau) {
            $tableauRepository->supprimer($tableau->getIdTableau());
        }
        return parent::supprimer($valeurClePrimaire);
    }
}