<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UtilisateurRepository extends AbstractRepository implements UtilisateurRepositoryInterface
{

    public function __construct(private ContainerInterface $container, private ConnexionBaseDeDonneesInterface $connexionBaseDeDonnees){
        parent::__construct($this->connexionBaseDeDonnees);
    }

    protected function getNomTable(): string
    {
        return "Utilisateurs";
    }

    protected function getNomCle(): string
    {
        return "login";
    }

    protected function getNomsColonnes(): array
    {
        return ["login", "nomUtilisateur", "prenomUtilisateur", "emailUtilisateur", "mdpHache"];
    }

    protected function estAutoIncremente():bool
    {
        return false;
    }

    protected function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject
    {
        return Utilisateur::construireDepuisTableau($objetFormatTableau);
    }

    public function recupererUtilisateursParEmail(string $email): array {
        return $this->recupererPlusieursPar("emailUtilisateur", $email);
    }

    public function recupererUtilisateursOrderedPrenomNom() : array {
        return $this->recupererOrdonne(["prenomUtilisateur", "nomUtilisateur"]);
    }

    public function recupererTableauxOuUtilisateurEstMembre(string $login): array
    {
        $tableauRepository =  $this->container->get("tableau_repository");
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

    public function supprimer(string $valeurClePrimaire): bool
    {
        $this->supprimerToutesAffectations("login", $valeurClePrimaire);
        $this->supprimerToutesParticipation("login", $valeurClePrimaire);

        $tableaux = $this->recupererTableauxOuUtilisateurEstMembre($valeurClePrimaire);
        $tableauRepository =  $this->container->get("tableau_repository");
        foreach ($tableaux as $tableau){
            $tableauRepository->supprimer($tableau->getIdTableau());
        }
        return parent::supprimer($valeurClePrimaire);
    }
}