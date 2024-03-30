<?php

namespace App\Trellotrolle\Test;

use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\Repository\CarteRepositoryInterface;
use App\Trellotrolle\Modele\Repository\ConnexionBaseDeDonnees;
use App\Trellotrolle\Modele\Repository\ConnexionBaseDeDonneesInterface;
use App\Trellotrolle\Service\CarteService;
use App\Trellotrolle\Service\ColonneServiceInterface;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\TableauServiceInterface;
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




    /*public function testMettreAJourCarte()
    {

    }

    public function testCreerCarte()
    {

    }

    public function testGetCartesParIdColonne()
    {

    }

    public function testSupprimerCarte()
    {

    }*/
}
