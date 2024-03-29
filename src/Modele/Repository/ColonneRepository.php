<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use Exception;

class ColonneRepository extends AbstractRepository implements ColonneRepositoryInterface
{

    public function __construct(private CarteRepositoryInterface $carteRepository){

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

    public function recupererColonnesTableau(int $idTableau): array {
        return $this->recupererPlusieursParOrdonne("idtableau", $idTableau, ["idcolonne"]);
    }

    public function getNombreColonnesTotalTableau(int $idTableau) : int {
        $query = "SELECT COUNT(DISTINCT idColonne) FROM Colonnes WHERE idTableau=:idTableau";
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($query);
        $pdoStatement->execute(["idTableau" => $idTableau]);
        $obj = $pdoStatement->fetch();
        return $obj[0];
    }

    public function supprimer(string $valeurClePrimaire): bool
    {
        $carteRepository = $this->carteRepository;
        $cartes = $carteRepository->recupererPlusieursParOrdonne("idColonne", $valeurClePrimaire, ["idColonne"]);
        foreach ($cartes as $carte){
            $carteRepository->supprimer($carte->getIdCarte());
        }
        return parent::supprimer($valeurClePrimaire);
    }
}