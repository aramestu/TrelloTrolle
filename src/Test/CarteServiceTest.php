<?php

namespace App\Trellotrolle\Test;

use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\Repository\CarteRepositoryInterface;
use App\Trellotrolle\Modele\Repository\ConnexionBaseDeDonnees;
use App\Trellotrolle\Modele\Repository\ConnexionBaseDeDonneesInterface;
use App\Trellotrolle\Service\CarteService;
use App\Trellotrolle\Service\ColonneServiceInterface;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\TableauServiceInterface;
use Mockery;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use PDO;

//Base de donnees deja rempli d'import
class CarteServiceTest extends TestCase
{

    private static ConnexionBaseDeDonneesInterface $connexionBaseDeDonnees;

    protected CarteRepositoryInterface $carteRepositoryMock;
    protected ColonneServiceInterface $colonneServiceMock;
    protected TableauServiceInterface $tableauServiceMock;
    protected CarteService $carteService;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$connexionBaseDeDonnees = new ConnexionBaseDeDonnees(new ConfigurationBDDTest());
        //self::$connexionBaseDeDonnees->getPdo()->exec(file_get_contents(__DIR__."/BD_Tables_V1.sql")); A décommenter si les tables n'éxiste plus dans la BDD de test
    }

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Initialisation des mocks
        $this->carteRepositoryMock = $this->createMock(CarteRepositoryInterface::class);
        $this->colonneServiceMock = $this->createMock(ColonneServiceInterface::class);
        $this->tableauServiceMock = $this->createMock(TableauServiceInterface::class);

        // Initialisation de l'instance de CarteService avec les mocks
        $this->carteService = new CarteService($this->carteRepositoryMock, $this->colonneServiceMock, $this->tableauServiceMock);
    }

    protected function tearDown(): void
    {
        // Nettoyage des mocks si nécessaire
        unset($this->carteRepositoryMock);
        unset($this->colonneServiceMock);
        unset($this->tableauServiceMock);
        unset($this->carteService);

        parent::tearDown();
    }

    public function testScriptCreationTables(): void
    {
        // Assurez-vous que la connexion à la base de données est établie
        self::$connexionBaseDeDonnees->getPdo()->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Vérifiez si la table "cartes" existe dans la base de données
        $stmt = self::$connexionBaseDeDonnees->getPdo()->query("SELECT 1 FROM cartes LIMIT 1");

        // Assurez-vous que la requête s'est exécutée sans erreur
        $this->assertNotFalse($stmt, "La table 'cartes' n'a pas été créée.");

        // Facultatif : affichage d'un message de réussite
        $this->assertTrue(true, "Le script de création de tables s'est exécuté avec succès.");
    }

    /**
     * @throws Exception
     * @throws ServiceException
     */
    public function testGetCarte()
    {
        // Arrange
        $idCarte = 1;
        $carte = new Carte();

        $this->carteRepositoryMock->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($idCarte)
            ->willReturn($carte);

        // Act
        $result = $this->carteService->getCarte($idCarte);

        // Assert
        $this->assertEquals($carte, $result);
    }


    /**
     * @throws ServiceException
     */
    public function testMettreAJourCarte()
    {
        $statement = self::$connexionBaseDeDonnees->getPdo()->query("SELECT * FROM cartes where idCarte = 1");
        $carteObject = $statement->fetch(PDO::FETCH_ASSOC);

        $statement = self::$connexionBaseDeDonnees->getPdo()->query("SELECT * FROM colonnes where idcolonne ='" . $carteObject['idcolonne'] . "'");
        $colonneObject = $statement->fetch(PDO::FETCH_ASSOC);

        $statement = self::$connexionBaseDeDonnees->getPdo()->query("SELECT * from tableaux where idtableau='" . $colonneObject["idtableau"]. "'");
        $tableauObject = $statement->fetch(PDO::FETCH_ASSOC);

        $statement = self::$connexionBaseDeDonnees->getPdo()->query("SELECT * FROM affecter where login='" . $tableauObject['proprietaireTableau'] . "'");
        $affected = $statement->fetchAll(PDO::FETCH_ASSOC);


        var_dump($carteObject);
        var_dump($tableauObject);

        $carte = new Carte();
        $carte->setIdCarte($carteObject["idcarte"]);
        $carte->setAffectationsCarte($affected);
        $carte->setCouleurCarte($carteObject["couleurcarte"]);
        $carte->setDescriptifCarte($carteObject["descriptifcarte"]);
        $carte->setTitreCarte($carteObject["titrecarte"]);


        $colonne = new Colonne();
        $colonne->setIdColonne($carteObject["idcolonne"]);
        $colonne->setTitreColonne($colonneObject["titrecolonne"]);


        $carte->setColonne($colonne);

        $tableau = new Tableau();
        $tableau->setIdTableau($tableauObject['idtableau']);
        $tableau->setCodeTableau($tableauObject["codetableau"]);
        $tableau->setTitreTableau($tableauObject["titretableau"]);

        $colonne->setTableau($tableau);

        $this->colonneServiceMock->expects($this->once())
            ->method('getColonne')
            ->with($colonne->getIdColonne())
            ->willReturn($colonne);

        $this->tableauServiceMock->expects($this->once())
            ->method('getByIdTableau')
            ->with($tableau->getIdTableau())
            ->willReturn($tableau);

        $this->carteRepositoryMock->expects($this->once())
            ->method('mettreAJour')
            ->with($carte);

        $this->carteService->mettreAJourCarte($carte->getIdCarte(),$colonne->getIdColonne(), $carte->getTitreCarte(),$carte->getDescriptifCarte(), $carte->getCouleurCarte(),"utilisateur1", $affected);
    }

    /*public function testCreerCarte()
    {

    }

    public function testGetCartesParIdColonne()
    {

    }

    public function testSupprimerCarte()
    {

    }*/
}
