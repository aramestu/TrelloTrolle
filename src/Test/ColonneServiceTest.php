<?php

namespace App\Trellotrolle\Test;

use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\ColonneRepository;
use App\Trellotrolle\Modele\Repository\ColonneRepositoryInterface;
use App\Trellotrolle\Service\ColonneService;
use App\Trellotrolle\Service\ColonneServiceInterface;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\TableauServiceInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Classe de test pour la classe ColonneService.
 *
 * Convention de nommage des tests : HappyPath/TriggerException_method/class/create_nom_Explication
 * Cette classe contient des méthodes de test pour les différentes fonctionnalités de la classe ColonneService.
 * Chaque méthode de test est annotée avec des commentaires décrivant le scénario de test, les données de test utilisées et les vérifications effectuées.
 * Les méthodes de test couvrent les cas de succès ainsi que les cas où des exceptions sont déclenchées.
 */
class ColonneServiceTest extends TestCase
{

    private ColonneRepositoryInterface $colonneRepository;
    private TableauServiceInterface $tableauService;
    private ColonneServiceInterface $colonneService;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->colonneRepository = $this->createMock(ColonneRepository::class);
        $this->tableauService = $this->createMock(TableauServiceInterface::class);

        $this->colonneService = new ColonneService($this->colonneRepository, $this->tableauService);
    }

    public function tearDown(): void
    {
        unset($this->colonneRepository);
        unset($this->tableauService);
        unset($this->colonneService);

        parent::tearDown();
    }

    /**
     * @throws ServiceException
     */
    public function testHappyPath_method_getColonne_Success(): void
    {
        // Données de test
        $idColonne = 1;
        $colonneSimulee = new Colonne();

        // Mock de la méthode recupererParClePrimaire du ColonneRepository pour retourner une colonne existante
        $this->colonneRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($idColonne)
            ->willReturn($colonneSimulee);

        // Appel de la méthode à tester
        $resultat = $this->colonneService->getColonne($idColonne);

        // Vérification que la méthode retourne bien la colonne simulée
        $this->assertSame($colonneSimulee, $resultat);
    }

    public function testTriggerException_method_getColonne_ColonneIsNull(): void
    {
        // Configurer le mock pour retourner null, simulant le comportement lorsque la colonne n'existe pas
        $this->colonneRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->willReturn(null);

        // S'attend à ce qu'une ServiceException soit lancée avec le message approprié
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("La colonne n'existe pas");
        $this->expectExceptionCode(Response::HTTP_NOT_FOUND);

        // Appeler la méthode à tester
        $this->colonneService->getColonne(1);
    }

    public function testTriggerException_method_getColonne_IdColonneNull(): void
    {
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("La colonne n'est pas renseigné");
        $this->expectExceptionCode(Response::HTTP_BAD_REQUEST);

        $this->colonneService->getColonne(null);
    }

    /**
     * @throws ServiceException
     */
    public function testHappyPath_method_supprimerColonne_Success(): void
    {
        // Créer un tableau avec un utilisateur
        $user = Utilisateur::create("autreProprietaire", "Doe", "Jane", "jane@example.com", "hashedPassword");
        $tableau = Tableau::create(1, "code", "titre", $user, []);

        // Créer une colonne pour le test
        $colonne = Colonne::create(1, "colonne", $tableau);

        // Stub pour getByIdTableau pour renvoyer un tableau avec un propriétaire
        $this->tableauService->expects($this->once())
            ->method('getByIdTableau')
            ->willReturn($tableau);

        // Stub pour recupererParClePrimaire pour renvoyer la colonne
        $this->colonneRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->willReturn($colonne);

        // Appel de la méthode à tester
        $this->colonneRepository->expects($this->once())
            ->method('supprimer')
            ->with(1);

        $this->colonneService->supprimerColonne(1, $user->getLogin());
    }

    public function testHappyPath_method_supprimerColonne_InsuffisantRight(): void
    {
        // Créer un tableau avec un utilisateur
        $user = Utilisateur::create("autreProprietaire", "Doe", "Jane", "jane@example.com", "hashedPassword");
        $tableau = Tableau::create(1, "codeTableau", "titre", $user, []);

        // Créer une colonne pour le test
        $colonne = Colonne::create(1, "colonne", $tableau);

        // Stub pour getByIdTableau pour renvoyer un tableau sans propriétaire ni participant
        $this->tableauService->expects($this->once())
            ->method('getByIdTableau')
            ->willReturn($tableau);

        // Stub pour recupererParClePrimaire pour renvoyer la colonne
        $this->colonneRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->willReturn($colonne);

        // Appel de la méthode à tester avec un utilisateur sans droits
        $this->expectException(ServiceException::class);
        $this->colonneService->supprimerColonne(1, 'utilisateur_sans_droits');
    }

    /**
     * @throws ServiceException
     */
    public function testHappyPath_method_creerColonne_Success(): void
    {
        // Créer un tableau avec un utilisateur
        $user = Utilisateur::create("proprietaire", "Doe", "Jane", "jane@example.com", "hashedPassword");
        $tableau = Tableau::create(1, "code", "titre", $user, []);

        // Stub pour getByIdTableau pour renvoyer un tableau avec un propriétaire
        $this->tableauService->expects($this->once())
            ->method('getByIdTableau')
            ->willReturn($tableau);

        // Appel de la méthode à tester
        $this->colonneRepository->expects($this->once())
            ->method('ajouter');

        $this->colonneService->creerColonne(1, 'Nom colonne', 'proprietaire');
    }

    public function testTriggerException_method_creerColonne_InsuffisantRight(): void
    {
        // Créer un tableau avec un utilisateur
        $user = Utilisateur::create("proprietaire", "Doe", "Jane", "jane@example.com", "hashedPassword");
        $tableau = Tableau::create(1, "code", "titre", $user, []);

        // Stub pour getByIdTableau pour renvoyer un tableau sans propriétaire ni participant
        $this->tableauService->expects($this->once())
            ->method('getByIdTableau')
            ->willReturn($tableau);

        // Appel de la méthode à tester avec un utilisateur sans droits
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Vous devez être participant au tableau pour pouvoir créer une Colonne!");
        $this->expectExceptionCode(Response::HTTP_UNAUTHORIZED);
        $this->colonneService->creerColonne(1, 'Nom colonne', 'utilisateur_sans_droits');
    }

    public function testTriggerException_method_creerColonne_InvalidColonneName()
    {
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Le nom de la colonne ne peut pas faire plus de 64 caractères et doit être renseigné");
        $this->expectExceptionCode(Response::HTTP_BAD_REQUEST);

        $this->colonneService->creerColonne(1, null, "user");
    }

    /**
     * @throws ServiceException
     */
    public function testHappyPath_method_mettreAJour_Success(): void
    {
        // Créer un tableau avec un utilisateur
        $user = Utilisateur::create("proprietaire", "Doe", "Jane", "jane@example.com", "hashedPassword");
        $tableau = Tableau::create(1, "code", "titre", $user, []);

        // Créer une colonne pour le test
        $colonne = Colonne::create(1, "colonne", $tableau);

        // Stub pour getByIdTableau pour renvoyer un tableau avec un propriétaire
        $this->tableauService->expects($this->once())
            ->method('getByIdTableau')
            ->willReturn($tableau);

        // Stub pour recupererParClePrimaire pour renvoyer la colonne
        $this->colonneRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->willReturn($colonne);

        // Appel de la méthode à tester
        $this->colonneRepository->expects($this->once())
            ->method('mettreAJour')
            ->with($colonne);

        $this->colonneService->mettreAJour(1, 'Nouveau nom colonne', 'proprietaire');
    }

    public function testTriggerException_method_mettreAJour_InsuffisantRight(): void
    {
        // Créer un tableau avec un utilisateur
        $user = Utilisateur::create("propriétaire", "Doe", "Jane", "jane@example.com", "hashedPassword");
        $tableau = Tableau::create(1, "code", "titre", $user, []);

        // Créer une colonne pour le test
        $colonne = Colonne::create(1, "colonne", $tableau);

        // Stub pour getByIdTableau pour renvoyer un tableau sans propriétaire ni participant
        $this->tableauService->expects($this->once())
            ->method('getByIdTableau')
            ->willReturn($tableau);

        // Stub pour recupererParClePrimaire pour renvoyer la colonne
        $this->colonneRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->willReturn($colonne);

        // Appel de la méthode à tester avec un utilisateur sans droits
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode(Response::HTTP_UNAUTHORIZED); // Assurez-vous que le code d'erreur est correct
        $this->colonneService->mettreAJour(1, 'Nouveau nom colonne', 'utilisateur_sans_droits');
    }

    /**
     * @throws ServiceException
     */
    public function testHappyPath_method_recupererColonnesTableau_Success(): void
    {
        // Créer un tableau avec un ID
        $idTableau = 1;

        // Stub pour recupererColonnesTableau pour retourner un tableau vide
        $this->colonneRepository->expects($this->once())
            ->method('recupererColonnesTableau')
            ->with($idTableau)
            ->willReturn([]);

        // Appel de la méthode à tester
        $resultat = $this->colonneService->recupererColonnesTableau($idTableau);

        // Vérification que la méthode retourne bien un tableau vide
        $this->assertSame([], $resultat);
    }

    public function testTriggerException_method_RecupererColonnesTableau_IdTableauNull(): void
    {
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode(Response::HTTP_BAD_REQUEST);
        $this->expectExceptionMessage("Le tableau n'est pas renseigné");

        $this->colonneService->recupererColonnesTableau(null);
    }
}
