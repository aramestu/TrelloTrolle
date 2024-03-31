<?php

namespace App\Trellotrolle\Test;

use App\Trellotrolle\Lib\MotDePasse;
use App\Trellotrolle\Lib\MotDePasseInterface;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\CarteRepositoryInterface;
use App\Trellotrolle\Modele\Repository\ColonneRepository;
use App\Trellotrolle\Modele\Repository\ColonneRepositoryInterface;
use App\Trellotrolle\Modele\Repository\TableauRepository;
use App\Trellotrolle\Modele\Repository\TableauRepositoryInterface;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;
use App\Trellotrolle\Modele\Repository\UtilisateurRepositoryInterface;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\TableauService;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class TableauServiceTest extends TestCase
{

    private TableauRepositoryInterface $tableauRepository;
    private UtilisateurRepositoryInterface $utilisateurRepository;
    private CarteRepositoryInterface $carteRepository;
    private ColonneRepositoryInterface $colonneRepository;
    private MotDePasseInterface $motDePasse;
    private TableauService $tableauService;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->tableauRepository = $this->createMock(TableauRepository::class);
        $this->utilisateurRepository = $this->createMock(UtilisateurRepository::class);
        $this->carteRepository = $this->createMock(CarteRepository::class);
        $this->colonneRepository = $this->createMock(ColonneRepository::class);
        $this->motDePasse = new MotDePasse();

        $this->tableauService = new TableauService($this->tableauRepository, $this->utilisateurRepository, $this->carteRepository, $this->colonneRepository, $this->motDePasse);
    }

    public function tearDown(): void{
        unset($this->tableauRepository);
        unset($this->utilisateurRepository);
        unset($this->carteRepository);
        unset($this->colonneRepository);
        unset($this->motDePasse);

        parent::tearDown();
    }

    /**
     * @throws ServiceException
     */
    public function testGetByCodeTableauAvecCodeValide(): void
    {
        // Préparez les données pour le test
        $codeTableau = 'ABC123';

        // Configurez le comportement attendu du repository
        $tableauAttendu = new Tableau();
        $this->tableauRepository->expects($this->once())
            ->method('recupererParCodeTableau')
            ->with($codeTableau)
            ->willReturn($tableauAttendu);

        // Exécutez la méthode à tester
        $resultat = $this->tableauService->getByCodeTableau($codeTableau);

        // Vérifiez que le résultat est celui attendu
        $this->assertSame($tableauAttendu, $resultat);
    }

    public function testGetByCodeTableauAvecCodeInvalide(): void
    {
        // Préparez les données pour le test
        $codeTableauInvalide = 'XYZ987';

        // Configurez le comportement attendu du repository
        $this->tableauRepository->expects($this->once())
            ->method('recupererParCodeTableau')
            ->with($codeTableauInvalide)
            ->willReturn(null);

        // Assurez-vous que l'exception appropriée est levée
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode(Response::HTTP_NOT_FOUND);

        // Exécutez la méthode à tester
        $this->tableauService->getByCodeTableau($codeTableauInvalide);
    }

    public function testGetByCodeTableauAvecCodeNull(): void
    {
        // Assurez-vous que l'exception appropriée est levée si le code du tableau est null
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode(Response::HTTP_BAD_REQUEST);

        // Exécutez la méthode à tester avec un code de tableau null
        $this->tableauService->getByCodeTableau(null);
    }

    /**
    * @throws ServiceException
    */
    public function testGetByIdTableauHappyPath(): void
    {
        // Préparation de l'ID de tableau
        $idTableau = 1;

        // Configuration du mock du repository pour retourner le tableau
        $tableau = new Tableau();
        $this->tableauRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($idTableau)
            ->willReturn($tableau);

        // Appel de la méthode à tester
        $resultat = $this->tableauService->getByIdTableau($idTableau);

        // Vérification du résultat
        $this->assertSame($tableau, $resultat);
    }

    /**
     * Teste le cas où aucun tableau n'existe avec l'ID donné.
     * @throws ServiceException
     */
    public function testGetByIdTableauTriggerException(): void
    {
        // Préparation de l'ID de tableau
        $idTableau = 999; // Un ID qui ne correspond à aucun tableau existant

        // Configuration du mock du repository pour retourner null
        $this->tableauRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($idTableau)
            ->willReturn(null);

        // Vérification que la méthode lance une exception ServiceException
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Le tableau n'existe pas");

        // Appel de la méthode à tester
        $this->tableauService->getByIdTableau($idTableau);
    }

    /**
     * @throws ServiceException
     */
    public function testCreerTableauHappyPath(): void
    {
        // Préparation des données de test
        $loginUtilisateurConnecte = "johnDoe";
        $nomTableau = "Nouveau tableau";

        // Mock du repository utilisateur pour simuler un utilisateur existant
        $utilisateur = new Utilisateur();
        $this->utilisateurRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($loginUtilisateurConnecte)
            ->willReturn($utilisateur);

        // Attente de l'ajout du tableau
        $this->tableauRepository->expects($this->once())
            ->method('ajouter')
            ->willReturn(true);

        // Exécution de la méthode à tester
        $tableau = $this->tableauService->creerTableau($loginUtilisateurConnecte, $nomTableau);

        // Vérification que la méthode retourne un tableau
        $this->assertInstanceOf(Tableau::class, $tableau);
    }



    public function testCreerTableauUtilisateurInexistant(): void
    {
        // Préparation des données de test
        $loginUtilisateurConnecte = "utilisateurInexistant";
        $nomTableau = "Nouveau tableau";

        // Mock du repository utilisateur pour simuler un utilisateur inexistant
        $this->utilisateurRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($loginUtilisateurConnecte)
            ->willReturn(null);

        // Exécution de la méthode à tester et attente de l'exception
        $this->expectException(ServiceException::class);
        $this->tableauService->creerTableau($loginUtilisateurConnecte, $nomTableau);
    }

    public function testCreerTableauNomTableauVide(): void
    {
        // Préparation des données de test
        $loginUtilisateurConnecte = "johnDoe";
        $nomTableau = "";

        // Exécution de la méthode à tester et attente de l'exception
        $this->expectException(ServiceException::class);
        $this->tableauService->creerTableau($loginUtilisateurConnecte, $nomTableau);
    }

    public function testCreerTableauNomTableauTropLong(): void
    {
        // Préparation des données de test
        $loginUtilisateurConnecte = "johnDoe";
        // Générer une chaîne de caractères de plus de 64 caractères pour dépasser la limite
        $nomTableau = str_repeat("a", 65);

        // Exécution de la méthode à tester et attente de l'exception
        $this->expectException(ServiceException::class);
        $this->tableauService->creerTableau($loginUtilisateurConnecte, $nomTableau);
    }

    /**
     * @throws ServiceException
     */
    public function testMettreAJourTableau(): void
    {
        // Préparation des données de test
        $idTableau = 1;
        $loginUtilisateurConnecte = "johnDoe";
        $nomTableau = "Nouveau nom du tableau";

        // Créer un tableau existant
        $tableau = new Tableau();
        $tableau->setIdTableau($idTableau);
        $tableau->setProprietaireTableau(Utilisateur::create("johnDoe", "Doe", "John", "john@example.com", "hashedPassword"));
        $tableau->setTitreTableau("Ancien nom du tableau");

        // Mock du repository tableau pour retourner le tableau existant
        $this->tableauRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($idTableau)
            ->willReturn($tableau);

        // Exécution de la méthode à tester
        $result = $this->tableauService->mettreAJourTableau($idTableau, $loginUtilisateurConnecte, $nomTableau);

        // Vérifier que le titre du tableau a été mis à jour correctement
        $this->assertEquals($nomTableau, $result->getTitreTableau());
    }

    public function testMettreAJourTableauTableauInexistant(): void
    {
        // Préparation des données de test
        $idTableau = 1;
        $loginUtilisateurConnecte = "johnDoe";
        $nomTableau = "Nouveau nom du tableau";
        $this->tableauRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($idTableau)
            ->willReturn(null);

        // Exécution de la méthode à tester
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Le tableau n'existe pas");
        $this->tableauService->mettreAJourTableau($idTableau, $loginUtilisateurConnecte, $nomTableau);
    }

    public function testMettreAJourTableauNonProprietaire(): void
    {
        // Préparation des données de test
        $idTableau = 1;
        $loginUtilisateurConnecte = "johnDoe";
        $nomTableau = "Nouveau nom du tableau";

        // Créer un tableau existant avec un propriétaire différent
        $tableau = new Tableau();
        $tableau->setIdTableau($idTableau);
        $tableau->setProprietaireTableau(Utilisateur::create("autreProprietaire", "Doe", "Jane", "jane@example.com", "hashedPassword"));
        $tableau->setTitreTableau("Ancien nom du tableau");

        // Mock du repository tableau pour retourner le tableau existant
        $this->tableauRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($idTableau)
            ->willReturn($tableau);

        // Exécution de la méthode à tester
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Seul le propriétaire du tableau peut mettre à jour le tableau!");
        $this->tableauService->mettreAJourTableau($idTableau, $loginUtilisateurConnecte, $nomTableau);
    }

    /**
     * //TODO: a revoir setParticipant
     * @throws ServiceException
     */
    public function testAjouterMembreAvecSucces(): void
    {
        // Préparation des données de test
        $idTableau = 1;
        $loginUtilisateurConnecte = "proprietaire";
        $loginUtilisateurNouveau = "nouveauMembre";

        // Création d'un tableau avec le propriétaire spécifié
        $tableau = new Tableau();
        $tableau->setIdTableau($idTableau);
        $tableau->setProprietaireTableau(Utilisateur::create("proprietaire", "Doe", "John", "john@example.com", "hashedPassword"));
        $tableau->setParticipants([]); // Initialisation de la liste des participants

        // Mock du tableauRepository pour retourner le tableau existant
        $this->tableauRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($idTableau)
            ->willReturn($tableau);

        // Mock de l'utilisateurRepository pour retourner un utilisateur existant
        $this->utilisateurRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($loginUtilisateurNouveau)
            ->willReturn(Utilisateur::create("nouveauMembre", "Doe", "Jane", "jane@example.com", "hashedPassword"));

        // Attendre que le participant soit ajouté avec succès
        $this->tableauRepository->expects($this->once())
            ->method('ajouterParticipant')
            ->with($loginUtilisateurNouveau, $idTableau);

        // Exécution de la méthode à tester
        $result = $this->tableauService->ajouterMembre($idTableau, $loginUtilisateurConnecte, $loginUtilisateurNouveau);

        // Vérifier que le tableau est retourné
        $this->assertInstanceOf(Tableau::class, $result);
    }


    public function testAjouterMembreTableauInexistant(): void
    {
        // Préparation des données de test
        $idTableau = 1;
        $loginUtilisateurConnecte = "proprietaire";
        $loginUtilisateurNouveau = "nouveauMembre";

        // Mock du tableauRepository pour retourner null, simulant un tableau inexistant
        $this->tableauRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($idTableau)
            ->willReturn(null);

        // Attendre une levée d'exception ServiceException
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Le tableau n'existe pas");

        // Exécution de la méthode à tester
        $this->tableauService->ajouterMembre($idTableau, $loginUtilisateurConnecte, $loginUtilisateurNouveau);
    }

    public function testAjouterMembreUtilisateurNonProprietaire(): void
    {
        // Préparation des données de test
        $idTableau = 1;
        $loginUtilisateurConnecte = "autreUtilisateur";
        $loginUtilisateurNouveau = "nouveauMembre";
        $tableau = new Tableau();
        $tableau->setProprietaireTableau(Utilisateur::create("proprietaire", "Doe", "John", "john@example.com", "hashedPassword"));

        // Mock du tableauRepository pour retourner un tableau avec un propriétaire différent
        $this->tableauRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($idTableau)
            ->willReturn($tableau);

        // Attendre une levée d'exception ServiceException
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Seul le propriétaire du tableau peut ajouter des membres");

        // Exécution de la méthode à tester
        $this->tableauService->ajouterMembre($idTableau, $loginUtilisateurConnecte, $loginUtilisateurNouveau);
    }


    public function testAjouterMembreUtilisateurInexistant(): void
    {
        // Préparation des données de test
        $idTableau = 1;
        $loginUtilisateurConnecte = "proprietaire";
        $loginUtilisateurNouveau = "utilisateurInexistant";
        $tableau = new Tableau();
        $tableau->setProprietaireTableau(Utilisateur::create("proprietaire", "Doe", "John", "john@example.com", "hashedPassword"));

        // Mock du tableauRepository pour retourner un tableau existant
        $this->tableauRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($idTableau)
            ->willReturn($tableau);

        // Mock du utilisateurRepository pour retourner null, simulant un utilisateur inexistant
        $this->utilisateurRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($loginUtilisateurNouveau)
            ->willReturn(null);

        // Attendre une levée d'exception ServiceException
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("L'utilisateur à ajouter n'existe pas");

        // Exécution de la méthode à tester
        $this->tableauService->ajouterMembre($idTableau, $loginUtilisateurConnecte, $loginUtilisateurNouveau);
    }

    //TODO: a revoir setParticipant
    public function testAjouterMembreDejaParticipantOuProprietaire(): void
    {
        // Préparation des données de test
        $idTableau = 1;
        $loginUtilisateurConnecte = "proprietaire";
        $loginUtilisateurNouveau = "participantDejaPresent";
        $tableau = new Tableau();
        $tableau->setProprietaireTableau(Utilisateur::create("proprietaire", "Doe", "John", "john@example.com", "hashedPassword"));
        $tableau->setParticipants([Utilisateur::create("participantDejaPresent", "Doe", "Jane", "jane@example.com", "hashedPassword")]);

        // Mock du tableauRepository pour retourner un tableau existant
        $this->tableauRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($idTableau)
            ->willReturn($tableau);

        // Mock du utilisateurRepository pour retourner un utilisateur existant
        $this->utilisateurRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($loginUtilisateurNouveau)
            ->willReturn(Utilisateur::create("participantDejaPresent", "Doe", "Jane", "jane@example.com", "hashedPassword"));

        // Attendre une levée d'exception ServiceException
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("L'utilisateur est déjà propriétaire ou participe déjà à ce tableau");

        // Exécution de la méthode à tester
        $this->tableauService->ajouterMembre($idTableau, $loginUtilisateurConnecte, $loginUtilisateurNouveau);
    }

    public function testSupprimerMembreIdTableauVide(): void
    {
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("L'idTableau ou le login de l'user connecté ou le login a ajouté ne peut pas être vide");

        $this->tableauService->supprimerMembre(null, "userConnecte", "userDelete");
    }

    public function testSupprimerMembreTableauInexistant(): void
    {
        $this->tableauRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->willReturn(null);

        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Le tableau n'existe pas");

        $this->tableauService->supprimerMembre(1, "userConnecte", "userDelete");
    }

    public function testSupprimerMembreNonProprietaire(): void
    {
        $tableau = new Tableau();
        $tableau->setProprietaireTableau(Utilisateur::create("proprietaire", "Doe", "John", "john@example.com", "hashedPassword"));

        $this->tableauRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->willReturn($tableau);

        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Seul le propriétaire du tableau peut supprimer des membres");

        $this->tableauService->supprimerMembre(1, "utilisateurConnecte", "utilisateurDelete");
    }

    // Teste le cas où l'utilisateur à supprimer est le propriétaire du tableau
    public function testSupprimerMembreProprietaire(): void
    {
        $tableau = new Tableau();
        $tableau->setProprietaireTableau(Utilisateur::create("proprietaire", "Doe", "John", "john@example.com", "hashedPassword"));

        $this->tableauRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->willReturn($tableau);

        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Vous ne pouvez pas vous supprimer du tableau si vous êtes propriétaire");

        $this->tableauService->supprimerMembre(1, "proprietaire", "proprietaire");
    }

    // Teste le cas où l'utilisateur à supprimer ne participe pas au tableau
    public function testSupprimerMembreUtilisateurNonParticipant(): void
    {
        $tableau = new Tableau();
        $tableau->setProprietaireTableau(Utilisateur::create("proprietaire", "Doe", "John", "john@example.com", "hashedPassword"));

        $this->tableauRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->willReturn($tableau);

        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("L'utilisateur à supprimer n'existe pas");

        $this->tableauService->supprimerMembre(1, "proprietaire", "utilisateurInconnu");
    }

    // Teste le cas où tout se passe bien

    /**
     * TODO: setParticipant
     * @throws ServiceException
     */
    public function testVerifierParticipantSansParticipantOuProprietaire(): void
    {
        // Préparation des données de test
        $loginUtilisateurConnecte = "johnDoe";
        $idTableau = 1; // ID de tableau valide

        // Création d'un objet Tableau simulé avec un propriétaire différent et sans participation de l'utilisateur connecté
        $tableau = new Tableau();
        $tableau->setProprietaireTableau(Utilisateur::create("autreProprietaire", "Doe", "Jane", "jane@example.com", "hashedPassword"));
        $tableau->setParticipants([]); // Initialise les participants avec un tableau vide

        // Mock de la méthode recupererParClePrimaire pour retourner le tableau simulé
        $this->tableauRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($idTableau)
            ->willReturn($tableau);

        // Attendre une exception ServiceException indiquant que l'utilisateur n'est pas un participant du tableau
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Vous n'êtes pas un participant de ce tableau.");

        // Exécution de la méthode à tester
        $this->tableauService->verifierParticipant($loginUtilisateurConnecte, $idTableau);
    }

    public function testVerifierParticipantAvecParticipantOuProprietaire(): void
    {
        // Préparation des données de test
        $loginUtilisateurConnecte = "johnDoe";
        $idTableau = 1; // ID de tableau valide

        // Mock de la méthode recupererParClePrimaire pour simuler un tableau inexistant
        $this->tableauRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($idTableau)
            ->willReturn(null);

        // Attendre une exception ServiceException indiquant que le tableau n'existe pas
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Vous n'êtes pas un participant de ce tableau.");

        // Exécution de la méthode à tester
        $this->tableauService->verifierParticipant($loginUtilisateurConnecte, $idTableau);
    }

    public function testQuitterTableauProprietaire(): void
    {
        // Préparation des données de test
        $loginUtilisateurConnecte = "johnDoe";
        $idTableau = 1; // ID de tableau valide

        // Création d'un tableau simulé avec le propriétaire correspondant à l'utilisateur connecté
        $tableau = new Tableau();
        $tableau->setProprietaireTableau(Utilisateur::create($loginUtilisateurConnecte, "Doe", "John", "john@example.com", "hashedPassword"));
        $tableau->setIdTableau($idTableau);

        // Attendre une exception ServiceException avec un code HTTP 403 (FORBIDDEN)
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode(Response::HTTP_FORBIDDEN);

        // Exécution de la méthode à tester avec le tableau simulé
        $this->tableauService->quitterTableau($loginUtilisateurConnecte, $tableau->getIdTableau());
    }

    public function testQuitterTableauNonParticipant(): void
    {
        // Préparation des données de test
        $loginUtilisateurConnecte = "johnDoe";
        $idTableau = 1; // ID de tableau valide

        // Création d'un tableau simulé sans l'utilisateur connecté comme participant
        $tableau = new Tableau();
        $tableau->setIdTableau($idTableau);

        // Attendre une exception ServiceException avec un code HTTP 400 (BAD REQUEST)
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode(Response::HTTP_BAD_REQUEST);

        // Exécution de la méthode à tester avec le tableau simulé
        $this->tableauService->quitterTableau($loginUtilisateurConnecte, $tableau->getIdTableau());
    }

    /**
     * @throws ServiceException
     */
    public function testQuitterTableauParticipant(): void
    {
        // Préparation des données de test
        $loginUtilisateurConnecte = "johnDoe";

        // Création d'un tableau simulé avec l'utilisateur connecté comme participant
        $tableau = new Tableau();
        $tableau->setIdTableau(1);

        $this->tableauRepository->ajouterParticipant($loginUtilisateurConnecte, 1);

        // Mock des méthodes supprimerAffectation et supprimerParticipant
        $this->carteRepository->expects($this->once())
            ->method('supprimerAffectation');
        $this->tableauRepository->expects($this->once())
            ->method('supprimerParticipant');

        // Exécution de la méthode à tester avec le tableau simulé
        $this->tableauService->quitterTableau($loginUtilisateurConnecte, $tableau->getIdTableau());
    }
}
