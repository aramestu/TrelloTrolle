<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ColonneRepository extends AbstractRepository implements ColonneRepositoryInterface
{

    public function __construct(private ContainerInterface $container, private ConnexionBaseDeDonneesInterface $connexionBaseDeDonnees){
        parent::__construct($connexionBaseDeDonnees);
    }

    protected function getNomTable(): string
    {
        return "Colonnes";
    }

    protected function getNomCle(): string
    {
        return "idColonne";
    }

    protected function getNomsColonnes(): array
    {
        return [
            "idColonne", "titreColonne", "idTableau"
        ];
    }

    protected function estAutoIncremente():bool
    {
        return true;
    }

    protected function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject
    {
        $tableau = new Tableau();
        $tableau->setIdTableau($objetFormatTableau["idtableau"]);
        $objetFormatTableau["tableau"] = $tableau;
        return Colonne::construireDepuisTableau($objetFormatTableau);
    }

    public function lastInsertId(): false|string
    {
        return $this->connexionBaseDeDonnees->getPdo()->lastInsertId();
    }

    public function recupererColonnesTableau(int $idTableau): array {
        return $this->recupererPlusieursParOrdonne("idtableau", $idTableau, ["idcolonne"]);
    }

    public function getNombreColonnesTotalTableau(int $idTableau) : int {
        $query = "SELECT COUNT(DISTINCT idColonne) FROM Colonnes WHERE idTableau=:idTableau";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
        $pdoStatement->execute(["idTableau" => $idTableau]);
        $obj = $pdoStatement->fetch();
        return $obj[0];
    }

    public function supprimer(string $valeurClePrimaire): bool
    {
        $carteRepository = $this->container->get("carte_repository");
        $cartes = $carteRepository->recupererPlusieursParOrdonne("idColonne", $valeurClePrimaire, ["idColonne"]);
        foreach ($cartes as $carte){
            $carteRepository->supprimer($carte->getIdCarte());
        }
        return parent::supprimer($valeurClePrimaire);
    }
}