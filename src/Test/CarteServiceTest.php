<?php

namespace App\Trellotrolle\Test;

use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\CarteRepositoryInterface;
use App\Trellotrolle\Service\CarteService;
use App\Trellotrolle\Service\ColonneService;
use App\Trellotrolle\Service\ColonneServiceInterface;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\TableauService;
use App\Trellotrolle\Service\TableauServiceInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Classe de test pour le service CarteService.
 *
 * Convention de nommage des tests : HappyPath/TriggerException_method/class/create_nom_Explication
 * Cette classe contient des méthodes de test pour les différentes fonctionnalités du service CarteService.
 * Elle utilise des mocks pour simuler les dépendances et effectuer les assertions nécessaires.
 */
class CarteServiceTest extends TestCase
{
    protected CarteRepositoryInterface $carteRepositoryMock;
    protected ColonneServiceInterface $colonneServiceMock;
    protected TableauServiceInterface $tableauServiceMock;
    protected CarteService $carteService;


    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Initialisation des mocks
        $this->carteRepositoryMock = $this->createMock(CarteRepository::class);
        $this->colonneServiceMock = $this->createMock(ColonneService::class);
        $this->tableauServiceMock = $this->createMock(TableauService::class);

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
    public function testHappyPath_method_getCarte_Success()
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

    public function testTriggerException_method_getCarte_CarteIsNull(){
        $idCarte = 1;

        $this->carteRepositoryMock->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($idCarte)
            ->willReturn(null);

        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("La carte n'existe pas");
        $this->expectExceptionCode(Response::HTTP_NOT_FOUND);

        $this->carteService->getCarte($idCarte);
    }

    public function testTriggerException_method_getCarte_IdCarteNull(){
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("La carte n'est pas renseigné");
        $this->expectExceptionCode(Response::HTTP_BAD_REQUEST);

        $this->carteService->getCarte(null);
    }

    /**
     * @throws ServiceException
     */
    public function testHappyPath_method_supprimerCarte_Success(): void
    {
        // Créer un tableau avec un utilisateur ayant les droits
        $user = Utilisateur::create("proprietaire", "Doe", "Jane", "jane@example.com", "hashedPassword");
        $tableau = Tableau::create(1, "code", "titre", $user, []);

        // Créer une colonne avec une carte
        $colonne = Colonne::create(1, "colonne", $tableau);
        $carte = Carte::create(1, "titreCarte", "descriptifCarte", "couleurCarte", $colonne, []);

        // Configurer les mocks pour retourner les objets nécessaires
        $this->colonneServiceMock->method('getColonne')->willReturn($colonne);
        $this->tableauServiceMock->method('getByIdTableau')->willReturn($tableau);
        $this->carteRepositoryMock->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($carte->getIdCarte())
            ->willReturn($carte);

        // Appel de la méthode à tester
        $this->carteRepositoryMock->expects($this->once())->method('supprimer');
        $resultat = $this->carteService->supprimerCarte($carte->getIdCarte(), 'proprietaire');

        // Vérification que la méthode retourne le tableau
        $this->assertSame($tableau, $resultat);
    }

    public function testTriggerException_method_supprimerCarte_InsuffisantRight(): void
    {
        // Créer un tableau avec un utilisateur n'ayant pas les droits
        $user = Utilisateur::create("Proprietaire", "Doe", "Jane", "jane@example.com", "hashedPassword");
        $tableau = Tableau::create(1, "code", "titre", $user, []);

        // Créer une colonne avec une carte
        $colonne = Colonne::create(1, "colonne", $tableau);
        $carte = Carte::create(1, "titreCarte", "descriptifCarte", "couleurCarte", $colonne, []);

        // Configurer les mocks pour retourner les objets nécessaires
        $this->carteRepositoryMock->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($carte->getIdCarte())
            ->willReturn($carte);
        $this->colonneServiceMock->method('getColonne')->willReturn($colonne);
        $this->tableauServiceMock->method('getByIdTableau')->willReturn($tableau);

        // Appel de la méthode à tester et s'attendre à ce qu'une exception ServiceException soit levée
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Vous n'avez pas les droits nécessaires!");
        $this->expectExceptionCode(Response::HTTP_UNAUTHORIZED);
        $this->carteService->supprimerCarte($carte->getIdCarte(), 'autreProprietaire');
    }

    /**
     * @throws ServiceException
     */
    public function testHappyPath_method_creerCarte_Success(): void
    {
        // Créer un tableau avec un utilisateur ayant les droits
        $user = Utilisateur::create("proprietaire", "Doe", "Jane", "jane@example.com", "hashedPassword");
        $tableau = Tableau::create(1, "code", "titre", $user, []);

        // Créer une colonne pour le test
        $colonne = Colonne::create(1, "colonne", $tableau);

        // Configurer les mocks pour retourner les objets nécessaires
        $this->colonneServiceMock->method('getColonne')->willReturn($colonne);
        $this->tableauServiceMock->method('getByIdTableau')->willReturn($tableau);
        $this->carteRepositoryMock->expects($this->once())->method('ajouter');

        // Appel de la méthode à tester
        $idCarte = $this->carteService->creerCarte(1, 'Titre carte', 'Description carte', 'bleu', 'proprietaire', [$user->getLogin()]);

        // Vérification que l'ID de la carte est retourné
        $this->assertIsInt($idCarte);
    }

    public function testTriggerException_method_creerCarte_InsuffisantRight(): void
    {
        // Créer un tableau avec un utilisateur n'ayant pas les droits
        $user = Utilisateur::create("Proprietaire", "Doe", "Jane", "jane@example.com", "hashedPassword");
        $tableau = Tableau::create(1, "code", "titre", $user, []);

        // Créer une colonne pour le test
        $colonne = Colonne::create(1, "colonne", $tableau);

        // Configurer les mocks pour retourner les objets nécessaires
        $this->colonneServiceMock->method('getColonne')->willReturn($colonne);
        $this->tableauServiceMock->method('getByIdTableau')->willReturn($tableau);

        // Appel de la méthode à tester et s'attendre à ce qu'une exception ServiceException soit levée
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Vous n'avez pas les droits nécessaires");
        $this->expectExceptionCode(Response::HTTP_UNAUTHORIZED);
        $this->carteService->creerCarte(1, 'Titre carte', 'Description carte', 'bleu', 'autreProprietaire', [$user->getLogin()]);
    }

    public function testTriggerException_method_creerCarte_InvalidTitle(){
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Le nom de la carte ne peut pas faire plus de 64 caractères et doit être renseigné");
        $this->expectExceptionCode(Response::HTTP_BAD_REQUEST);
        $this->carteService->creerCarte(1, null, 'Description carte', 'bleu', 'autreProprietaire', []);
    }

    public function testTriggerException_method_creerCarte_InvalidDescription(){
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("La description de la carte doit être renseigné");
        $this->expectExceptionCode(Response::HTTP_BAD_REQUEST);
        $this->carteService->creerCarte(1, 'Titre carte', null, 'bleu', 'autreProprietaire', []);
    }

    public function testTriggerException_method_creerCarte_InvalidColor(){
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("La couleur de la carte ne peut pas faire plus de 7 caractères et doit être renseigné");
        $this->expectExceptionCode(Response::HTTP_BAD_REQUEST);
        $this->carteService->creerCarte(1, 'Titre Carte', 'Description carte', null, 'autreProprietaire', []);
    }

    public function testTriggerException_method_creerCarte_InvalidIdColonne(){
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("La colonne n'est pas renseigné");
        $this->expectExceptionCode(Response::HTTP_BAD_REQUEST);
        $this->carteService->creerCarte(null, 'Titre Carte', 'Description carte', 'couleur', 'autreProprietaire', []);
    }

    public function testTriggerException_method_creerCarte_InvalidAffectionMember(): void
    {
        // Crée un tableau avec un utilisateur propriétaire et un participant
        $proprietaire = Utilisateur::create("proprietaire", "Doe", "Jane", "jane@example.com", "hashedPassword");
        $participant = Utilisateur::create("participant", "Doe", "John", "john@example.com", "hashedPassword");
        $tableau = Tableau::create(1, "code", "titre", $proprietaire, [$participant]);

        // Crée une colonne appartenant au tableau
        $colonne = Colonne::create(1, "colonne", $tableau);

        // Configure le mock de ColonneService pour retourner la colonne existante
        $this->colonneServiceMock->method('getColonne')
            ->with($colonne->getIdColonne())
            ->willReturn($colonne);

        // Configure le mock de TableauService pour retourner le tableau existant
        $this->tableauServiceMock->method('getByIdTableau')
            ->with($tableau->getIdTableau())
            ->willReturn($tableau);

        // Appelle la méthode creerCarte avec un affectation pour un utilisateur non participant ni propriétaire
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("L'un des membres n'est pas affecté au tableau ou n'existe pas");
        $this->expectExceptionCode(Response::HTTP_BAD_REQUEST);
        $this->carteService->creerCarte($colonne->getIdColonne(), "titreCarte", "descriptifCarte", "couleur", "utilisateurNonAffecte", ["utilisateurNonAffecte"]);
    }

    /**
     * @throws ServiceException
     */
    public function testHappyPath_method_mettreAJourCarte_Success(): void
    {
        // Créer un tableau avec un utilisateur ayant les droits
        $user = Utilisateur::create("proprietaire", "Doe", "Jane", "jane@example.com", "hashedPassword");
        $tableau = Tableau::create(1, "code", "titre", $user, []);

        // Créer une colonne avec une carte
        $colonne = Colonne::create(1, "colonne", $tableau);
        $carte = Carte::create(1, "titreCarte", "descriptifCarte", "couleurCarte", $colonne, []);

        // Créer une nouvelle colonne pour le test
        $nouvelleColonne = Colonne::create(2, "nouvelle colonne", $tableau);

        // Configurer les mocks pour retourner les objets nécessaires
        $this->colonneServiceMock->method('getColonne')->willReturn($nouvelleColonne);
        $this->colonneServiceMock->method('getColonne')->willReturn($colonne);
        $this->tableauServiceMock->method('getByIdTableau')->willReturn($tableau);
        $this->carteRepositoryMock->expects($this->once())->method('mettreAJour');
        $this->carteRepositoryMock->expects($this->once())->method('supprimerToutesAffectationsCarte');
        $affectations = [$user->getLogin()];
        $this->carteRepositoryMock->expects($this->exactly(count($affectations)))->method('ajouterAffectation');
        $this->carteRepositoryMock->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($carte->getIdCarte())
            ->willReturn($carte);

        // Appel de la méthode à tester
        $resultat = $this->carteService->mettreAJourCarte($carte->getIdCarte(), $nouvelleColonne->getIdColonne(), 'Nouveau titre', 'Nouveau descriptif', 'bleu', 'proprietaire', $affectations);

        // Vérification que la méthode retourne la carte mise à jour
        $this->assertInstanceOf(Carte::class, $resultat);
    }

    public function testTriggerException_method_mettreAJourCarte_InsuffisantRight(): void
    {
        // Créer un tableau avec un utilisateur n'ayant pas les droits
        $user = Utilisateur::create("autreProprietaire", "Doe", "Jane", "jane@example.com", "hashedPassword");
        $tableau = Tableau::create(1, "code", "titre", $user, []);

        // Créer une colonne avec une carte
        $colonne = Colonne::create(1, "colonne", $tableau);
        $carte = Carte::create(1, "titreCarte", "descriptifCarte", "rouge", $colonne, []);

        // Configurer les mocks pour retourner les objets nécessaires
        $this->colonneServiceMock->method('getColonne')->willReturn($colonne);
        $this->tableauServiceMock->method('getByIdTableau')->willReturn($tableau);
        $this->carteRepositoryMock->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($carte->getIdCarte())
            ->willReturn($carte);

        // Appel de la méthode à tester et s'attendre à ce qu'une exception ServiceException soit levée
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Vous n'avez pas les droits nécessaires");
        $this->expectExceptionCode(Response::HTTP_UNAUTHORIZED);
        $this->carteService->mettreAJourCarte($carte->getIdCarte(), 1, 'Nouveau titre', 'Nouveau descriptif', 'bleu', 'Proprietaire', []);
    }

    public function testTriggerException_method_mettreAJourCarte_NewColonneNotInTheTableau(): void
    {
        // Créez un tableau avec un utilisateur ayant les droits
        $user = Utilisateur::create("proprietaire", "Doe", "Jane", "jane@example.com", "hashedPassword");
        $tableau = Tableau::create(1, "code", "titre", $user, []);

        // Créez une colonne avec une carte
        $colonne = Colonne::create(1, "colonne", $tableau);
        $carte = Carte::create(1, "titreCarte", "descriptifCarte", "couleurCarte", $colonne, []);

        // Créez une nouvelle colonne pour le test, appartenant à un autre tableau
        $autreTableau = Tableau::create(2, "autre_code", "autre_titre", $user, []);
        $nouvelleColonne = Colonne::create(2, "nouvelle colonne", $autreTableau);

        // Configurer les mocks pour retourner les objets nécessaires
        $this->colonneServiceMock->expects($this->exactly(2))
            ->method('getColonne')
            ->willReturnCallback(function ($idColonne) use ($colonne, $nouvelleColonne) {
                if ($idColonne === $colonne->getIdColonne()) {
                    return $colonne;
                } elseif ($idColonne === $nouvelleColonne->getIdColonne()) {
                    return $nouvelleColonne;
                }
                return null;
            });

        $this->tableauServiceMock->method('getByIdTableau')
            ->willReturn($tableau);

        $this->carteRepositoryMock->expects($this->once())
            ->method('recupererParClePrimaire')
            ->willReturn($carte);

        // Appel de la méthode à tester et s'attendre à ce qu'une exception ServiceException soit levée
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("La nouvelle colonne n'appartient pas au bon tableau");
        $this->expectExceptionCode(Response::HTTP_BAD_REQUEST);

        // Appel de la méthode avec une nouvelle colonne qui n'appartient pas au bon tableau
        $this->carteService->mettreAJourCarte($carte->getIdCarte(), $nouvelleColonne->getIdColonne(), 'Nouveau titre', 'Nouveau descriptif', 'couleur', 'proprietaire', []);
    }
}