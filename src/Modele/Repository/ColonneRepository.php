<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Classe ColonneRepository
 *
 * Cette classe est une implémentation de l'interface ColonneRepositoryInterface.
 * Elle gère la récupération et la manipulation des colonnes d'un tableau dans la base de données.
 *
 * @package App\Trellotrolle\Modele\Repository
 */
class ColonneRepository extends AbstractRepository implements ColonneRepositoryInterface
{

    /**
     * Constructeur de la classe.
     *
     * @param ContainerInterface $container L'interface du conteneur.
     * @param ConnexionBaseDeDonneesInterface $connexionBaseDeDonnees L'interface de la connexion à la base de données.
     */
    public function __construct(private ContainerInterface $container, private ConnexionBaseDeDonneesInterface $connexionBaseDeDonnees)
    {
        parent::__construct($connexionBaseDeDonnees);
    }

    /**
     * Méthode getNomTable
     *
     * Cette méthode retourne le nom de la table "Colonnes".
     *
     * @return string Le nom de la table "Colonnes".
     */
    protected function getNomTable(): string
    {
        return "Colonnes";
    }

    /**
     * Méthode getNomCle
     *
     * Cette méthode retourne le nom de la clé primaire "idColonne".
     *
     * @return string Le nom de la clé primaire "idColonne".
     */
    protected function getNomCle(): string
    {
        return "idColonne";
    }

    /**
     * Méthode getNomsColonnes
     *
     * Cette méthode retourne un tableau contenant les noms des colonnes.
     *
     * @return array Le tableau des noms des colonnes.
     */
    protected function getNomsColonnes(): array
    {
        return [
            "idColonne", "titreColonne", "idTableau"
        ];
    }

    /**
     * Méthode estAutoIncremente
     *
     * Cette méthode retourne un booléen indiquant si l'attribut est auto-incrémenté.
     *
     * @return bool Un booléen indiquant si l'attribut est auto-incrémenté.
     */
    protected function estAutoIncremente(): bool
    {
        return true;
    }

    /**
     * Méthode protégée pour construire un objet AbstractDataObject à partir d'un tableau de données.
     *
     * @param array $objetFormatTableau Le tableau de données contenant les informations nécessaires pour construire l'objet.
     * @return AbstractDataObject L'objet construit à partir du tableau de données.
     */
    protected function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject
    {
        $tableau = new Tableau();
        $tableau->setIdTableau($objetFormatTableau["idtableau"]);
        $objetFormatTableau["tableau"] = $tableau;
        return Colonne::construireDepuisTableau($objetFormatTableau);
    }

    /**
     * Récupère les colonnes d'un tableau en fonction de l'ID du tableau.
     *
     * @param int $idTableau L'ID du tableau.
     * @return array Les colonnes du tableau.
     */
    public function recupererColonnesTableau(int $idTableau): array
    {
        return $this->recupererPlusieursParOrdonne("idtableau", $idTableau, ["idcolonne"]);
    }

    /**
     * Méthode getNombreColonnesTotalTableau
     *
     * Cette méthode retourne le nombre total de colonnes d'un tableau en fonction de l'ID du tableau.
     *
     * @param int $idTableau L'ID du tableau.
     * @return int Le nombre total de colonnes du tableau.
     */
    public function getNombreColonnesTotalTableau(int $idTableau): int
    {
        $query = "SELECT COUNT(DISTINCT idColonne) FROM Colonnes WHERE idTableau=:idTableau";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
        $pdoStatement->execute(["idTableau" => $idTableau]);
        $obj = $pdoStatement->fetch();
        return $obj[0];
    }

    /**
     * Supprime un élément en utilisant la valeur de la clé primaire.
     *
     * @param string $valeurClePrimaire La valeur de la clé primaire.
     * @return bool Retourne true si la suppression a réussi, sinon false.
     */
    public function supprimer(string $valeurClePrimaire): bool
    {
        $carteRepository = $this->container->get("carte_repository");
        $cartes = $carteRepository->recupererPlusieursParOrdonne("idColonne", $valeurClePrimaire, ["idColonne"]);
        foreach ($cartes as $carte) {
            $carteRepository->supprimer($carte->getIdCarte());
        }
        return parent::supprimer($valeurClePrimaire);
    }
}