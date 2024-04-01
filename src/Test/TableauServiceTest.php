<?php

namespace App\Trellotrolle\Test;

use App\Trellotrolle\Lib\MotDePasse;
use App\Trellotrolle\Lib\MotDePasseInterface;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
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

    public function testTriggerException_method_getByIdTableau_InvalidIdTableau(){
        $idTableau = null;

        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("L'idTableau n'est pas renseigné");
        $this->expectExceptionCode(Response::HTTP_BAD_REQUEST);

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

    public function testTriggerException_method_creerTableau_InvalidLogin()
    {
        $this->expectException(ServiceException::class);
        $this->tableauService->creerTableau("a", "tableau");
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
     * @throws ServiceException
     */
    public function testAjouterMembreAvecSucces(): void
    {

        $proprietaire = Utilisateur::create("proprietaire", "Doe", "John", "john@example.com", "hashedPassword");
        $user = Utilisateur::create("participantDejaPresent", "Doe", "Jane", "jane@example.com", "hashedPassword");
        $tableau = Tableau::create(1, "code", "titre", $proprietaire, []);

        // Mock du tableauRepository pour retourner le tableau existant
        $this->tableauRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($tableau->getIdTableau())
            ->willReturn($tableau);

        // Mock de l'utilisateurRepository pour retourner un utilisateur existant
        $this->utilisateurRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($user->getLogin())
            ->willReturn($user);

        // Attendre que le participant soit ajouté avec succès
        $this->tableauRepository->expects($this->once())
            ->method('ajouterParticipant')
            ->with($user->getLogin(), $tableau->getIdTableau());

        // Exécution de la méthode à tester
        $result = $this->tableauService->ajouterMembre($tableau->getIdTableau(), $proprietaire->getLogin(), $user->getLogin());

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

    public function testAjouterMembreDejaParticipantOuProprietaire(): void
    {
        $proprietaire = Utilisateur::create("proprietaire", "Doe", "John", "john@example.com", "hashedPassword");
        $user = Utilisateur::create("participantDejaPresent", "Doe", "Jane", "jane@example.com", "hashedPassword");
        $tableau = Tableau::create(1, "code", "titre", $proprietaire, [$user]);

        // Mock du tableauRepository pour retourner un tableau existant
        $this->tableauRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($tableau->getIdTableau())
            ->willReturn($tableau);

        // Mock du utilisateurRepository pour retourner un utilisateur existant
        $this->utilisateurRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($user->getLogin())
            ->willReturn($user);

        // Attendre une levée d'exception ServiceException
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("L'utilisateur est le propriétaire ou participe déjà à ce tableau");

        // Exécution de la méthode à tester
        $this->tableauService->ajouterMembre($tableau->getIdTableau(), $proprietaire->getLogin(), $user->getLogin());
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

    /**
     * @throws ServiceException
     */
    public function testSupprimerParticipantNonProprietaire(): void
    {
        // Préparation des données de test
        $proprietaire = Utilisateur::create("proprietaire", "Doe", "John", "john@example.com", "hashedPassword");
        $participant = Utilisateur::create("participant", "Doe", "Jane", "jane@example.com", "hashedPassword");
        $tableau = Tableau::create(1, "code", "titre", $proprietaire, [$participant]);

        // Mock du tableauRepository pour retourner le tableau simulé
        $this->tableauRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($tableau->getIdTableau())
            ->willReturn($tableau);

        // Mock du utilisateurRepository pour retourner l'utilisateur à supprimer
        $this->utilisateurRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($participant->getLogin())
            ->willReturn($participant);

        // Appel de la méthode à tester
        $resultat = $this->tableauService->supprimerMembre($tableau->getIdTableau(), $proprietaire->getLogin(), $participant->getLogin());

        // Vérification
        $this->assertNotNull($resultat);
        // Ajoutez d'autres assertions au besoin
    }

    public function testUtilisateurNonParticipantNiProprietaire(): void
    {
        // Préparation des données de test
        $proprietaire = Utilisateur::create("proprietaire", "Doe", "John", "john@example.com", "hashedPassword");
        $autreUtilisateur = Utilisateur::create("autreUtilisateur", "Doe", "Jane", "jane@example.com", "hashedPassword");
        $tableau = Tableau::create(1, "code", "titre", $proprietaire, []);

        // Mock du tableauRepository pour retourner le tableau simulé
        $this->tableauRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($tableau->getIdTableau())
            ->willReturn($tableau);

        // Mock du utilisateurRepository pour retourner l'utilisateur connecté
        $this->utilisateurRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($autreUtilisateur->getLogin())
            ->willReturn($autreUtilisateur);

        // Appel de la méthode à tester
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode(Response::HTTP_CONFLICT);
        $this->expectExceptionMessage("L'utilisateur ne participe pas à ce tableau");

        $this->tableauService->supprimerMembre($tableau->getIdTableau(), $proprietaire->getLogin(), $autreUtilisateur->getLogin());
    }


    /**
     * @throws ServiceException
     */
    public function testVerifierParticipantSansParticipantOuProprietaire(): void
    {
        // Préparation des données de test
        $loginUtilisateurConnecte = "johnDoe";
        $idTableau = 1; // ID de tableau valide

        // Création d'un objet Tableau simulé avec un propriétaire différent et sans participation de l'utilisateur connecté
        $user = Utilisateur::create("autreProprietaire", "Doe", "Jane", "jane@example.com", "hashedPassword");
        $tableau = Tableau::create(1, "code", "titre", $user, []);

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

        $user = Utilisateur::create("autreProprietaire", "Doe", "Jane", "jane@example.com", "hashedPassword");
        $tableau = Tableau::create(1, "code", "titre", $user, []);

        // Mock de la méthode recupererParClePrimaire pour retourner le tableau simulé
        $this->tableauRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($tableau->getIdTableau())
            ->willReturn($tableau);

        // Attendre une exception ServiceException indiquant que vous n'êtes pas un participant du tableau
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Vous n'êtes pas un participant de ce tableau.");

        // Exécution de la méthode à tester
        $this->tableauService->verifierParticipant($loginUtilisateurConnecte, $tableau->getIdTableau());
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

        $this->tableauRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($idTableau)
            ->willReturn($tableau);

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

        // Création d'un tableau simulé sans l'utilisateur connecté comme participant
        $user = Utilisateur::create("autreProprietaire", "Doe", "Jane", "jane@example.com", "hashedPassword");
        $tableau = Tableau::create(1, "code", "titre", $user, []);

        $this->tableauRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($tableau->getIdTableau())
            ->willReturn($tableau);

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
        $proprio = Utilisateur::create("autreProprietaire", "Doe", "Jane", "jane@example.com", "hashedPassword");
        $utilisateurConnecte = Utilisateur::create("johnDoe", "Doe", "Jane", "jane@example.com", "hashedPassword");

        // Création d'un tableau simulé avec l'utilisateur connecté comme participant
        $tableau = Tableau::create(1, "code", "titre", $proprio, [$utilisateurConnecte]);

        $this->tableauRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($tableau->getIdTableau())
            ->willReturn($tableau);

        // Mock des méthodes supprimerAffectation et supprimerParticipant
        $this->carteRepository->expects($this->once())
            ->method('supprimerAffectation');
        $this->tableauRepository->expects($this->once())
            ->method('supprimerParticipant');

        // Exécution de la méthode à tester avec le tableau simulé
        $this->tableauService->quitterTableau($utilisateurConnecte->getLogin(), $tableau->getIdTableau());
    }

    public function testSupprimerTableauProprietaireExistant(): void
    {
        // Préparation des données de test
        $loginUtilisateurConnecte = "johnDoe";
        $idTableau = 1;

        // Création d'un tableau simulé avec l'utilisateur connecté comme propriétaire
        $user = Utilisateur::create($loginUtilisateurConnecte, "Doe", "John", "john@example.com", "hashedPassword");
        $tableau = Tableau::create($idTableau, "code", "titre", $user, []);

        // Mock de la méthode recupererParClePrimaire pour retourner le tableau simulé
        $this->tableauRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($idTableau)
            ->willReturn($tableau);

        // Mock de la méthode supprimer dans le repository
        $this->tableauRepository->expects($this->once())
            ->method('supprimer')
            ->with($idTableau);

        // Exécution de la méthode à tester
        $this->tableauService->supprimer($loginUtilisateurConnecte, $idTableau);
    }

    public function testSupprimerTableauNonProprietaireExistant(): void
    {
        // Préparation des données de test
        $loginUtilisateurConnecte = "johnDoe";
        $idTableau = 1;

        // Création d'un tableau simulé avec un autre utilisateur comme propriétaire
        $user = Utilisateur::create("autreProprietaire", "Doe", "Jane", "jane@example.com", "hashedPassword");
        $tableau = Tableau::create($idTableau, "code", "titre", $user, []);

        // Mock de la méthode recupererParClePrimaire pour retourner le tableau simulé
        $this->tableauRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($idTableau)
            ->willReturn($tableau);

        // Attendre une exception ServiceException indiquant que l'utilisateur n'est pas propriétaire du tableau
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode(Response::HTTP_NOT_FOUND); // Attendre le code d'exception 403

        // Exécution de la méthode à tester
        $this->tableauService->supprimer($loginUtilisateurConnecte, $idTableau);
    }


    public function testSupprimerTableauInexistant(): void
    {
        // Préparation des données de test
        $loginUtilisateurConnecte = "johnDoe";
        $idTableau = 1;

        // Mock de la méthode recupererParClePrimaire pour retourner null
        $this->tableauRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($idTableau)
            ->willReturn(null);

        // Attendre une exception ServiceException indiquant que le tableau n'existe pas
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode(Response::HTTP_NOT_FOUND);

        // Exécution de la méthode à tester
        $this->tableauService->supprimer($loginUtilisateurConnecte, $idTableau);
    }

    public function testSupprimerTableauNonProprietaireInexistant(): void
    {
        // Préparation des données de test
        $loginUtilisateurConnecte = "johnDoe";
        $idTableau = 1;

        // Mock de la méthode recupererParClePrimaire pour retourner null
        $this->tableauRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($idTableau)
            ->willReturn(null);

        // Attendre une exception ServiceException indiquant que le tableau n'existe pas
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode(Response::HTTP_NOT_FOUND);

        // Exécution de la méthode à tester
        $this->tableauService->supprimer($loginUtilisateurConnecte, $idTableau);
    }

    /**
     * @throws ServiceException
     */
    public function testVerifierProprietaireProprietaireExistant(): void
    {

        // Création d'un tableau simulé avec l'utilisateur connecté comme propriétaire
        $user = Utilisateur::create("proprietaire", "Doe", "John", "john@example.com", "hashedPassword");
        $tableau = Tableau::create(1, "code", "titre", $user, []);

        $this->tableauRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($tableau->getIdTableau())
            ->willReturn($tableau);

        // Appel de la méthode à tester
        $resultat = $this->tableauService->verifierProprietaire($user->getLogin(), $tableau->getIdTableau());

        // Vérification que la méthode retourne le tableau
        $this->assertSame($tableau, $resultat);
    }

    public function testVerifierProprietaireNonProprietaireExistant(): void
    {
        // Préparation des données de test
        $loginUtilisateurConnecte = "johnDoe";

        // Création d'un tableau simulé avec un autre utilisateur comme propriétaire
        $user = Utilisateur::create("autreProprietaire", "Doe", "Jane", "jane@example.com", "hashedPassword");
        $tableau = Tableau::create(1, "code", "titre", $user, []);

        $this->tableauRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($tableau->getIdTableau())
            ->willReturn($tableau);

        // Attendre une exception ServiceException indiquant que l'utilisateur n'est pas propriétaire du tableau
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Vous n'êtes pas le propriétaire de ce tableau.");

        // Appel de la méthode à tester
        $this->tableauService->verifierProprietaire($loginUtilisateurConnecte, $tableau->getIdTableau());
    }

    public function testRecupererColonnesEtCartesDuTableau(): void
    {
        $user = Utilisateur::create("autreProprietaire", "Doe", "Jane", "jane@example.com", "hashedPassword");
        $tableau = Tableau::create(1, "code", "titre", $user, []);

        $colonne1 = Colonne::create(1, "colonne1", $tableau);
        $colonne2 = Colonne::create(2, "colonne2", $tableau);

        $carte1 = Carte::create(1, "carte1", "carte", "rouge", $colonne1, []);
        $carte2 = Carte::create(2, "carte2", "carte", "rouge", $colonne1, []);
        $carte3 = Carte::create(3, "carte3", "carte", "rouge", $colonne2, []);
        $carte4 = Carte::create(4, "carte4", "carte", "rouge", $colonne2, []);

        // Configurer le mock pour la colonneRepository
        $this->colonneRepository->expects($this->once())
            ->method('recupererColonnesTableau')
            ->with($tableau->getIdTableau())
            ->willReturn([$colonne1, $colonne2]);

        // Configurer le mock pour la carteRepository pour les deux colonnes
        $this->carteRepository->expects($this->exactly(2))
            ->method('recupererCartesColonne')
            ->willReturnOnConsecutiveCalls([$carte1, $carte2], [$carte3, $carte4]);


        // Appeler la méthode à tester
        $resultat = $this->tableauService->recupererColonnesEtCartesDuTableau($tableau->getIdTableau());

        // Vérifier le résultat
        $this->assertCount(2, $resultat["colonnes"]);
        $this->assertCount(2, $resultat["associations"]["1"]);
        $this->assertCount(2, $resultat["associations"]["2"]);
        // Ajouter d'autres assertions au besoin pour vérifier les données renvoyées
    }

    public function testInformationsAffectationsCartes(): void
    {
        // Préparation des données de test
        $user1 = Utilisateur::create("utilisateur1", "Doe", "John", "john@example.com", "hashedPassword");
        $user2 = Utilisateur::create("utilisateur2", "Doe", "Jane", "jane@example.com", "hashedPassword");

        $tableau = Tableau::create(1, "code", "titre", $user1, []);

        $colonne1 = Colonne::create(1, "colonne1", $tableau);
        $colonne2 = Colonne::create(2, "colonne2", $tableau);

        $carte1 = Carte::create(1, "carte1", "carte", "rouge", $colonne1, [$user1, $user2]);
        $carte2 = Carte::create(2, "carte2", "carte", "rouge", $colonne2, [$user1]);
        $carte3 = Carte::create(3, "carte3", "carte", "rouge", $colonne2, [$user2]);

        // Configurer le mock pour la carteRepository
        $this->carteRepository->expects($this->once())
            ->method('recupererCartesTableau')
            ->with($tableau->getIdTableau())
            ->willReturn([$carte1, $carte2, $carte3]);

        // Appeler la méthode à tester
        $resultat = $this->tableauService->informationsAffectationsCartes($tableau->getIdTableau());

        // Vérifier le résultat
        $this->assertCount(2, $resultat);

        $this->assertArrayHasKey('utilisateur1', $resultat);
        $this->assertCount(2, $resultat['utilisateur1']['colonnes']);
        $this->assertEquals(1, $resultat['utilisateur1']['colonnes'][1]);
        $this->assertEquals(1, $resultat['utilisateur1']['colonnes'][2]);

        $this->assertArrayHasKey('utilisateur2', $resultat);
        $this->assertCount(2, $resultat['utilisateur2']['colonnes']);
        $this->assertEquals(1, $resultat['utilisateur2']['colonnes'][1]);
        $this->assertEquals(1, $resultat['utilisateur2']['colonnes'][2]);
    }
}