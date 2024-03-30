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
        self::$connexionBaseDeDonnees->getPdo()->exec(file_get_contents(__DIR__."/BD_Tables_V1.sql"));
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

    public function test__construct()
    {

    }

    public function testMettreAJourCarte()
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

    }
}
