<?php

namespace App\Trellotrolle\Test\ServiceTest;

use App\Trellotrolle\Lib\MotDePasse;
use App\Trellotrolle\Lib\MotDePasseInterface;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\TableauRepository;
use App\Trellotrolle\Modele\Repository\TableauRepositoryInterface;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;
use App\Trellotrolle\Modele\Repository\UtilisateurRepositoryInterface;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\UtilisateurService;
use App\Trellotrolle\Service\UtilisateurServiceInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;


/**
 * Classe de test pour le service UtilisateurService.
 *
 * Convention de nommage des tests : HappyPath/TriggerException_method/class/create_nom_Explication
 * Cette classe contient des méthodes de test pour les différentes fonctionnalités du service UtilisateurService.
 * Les méthodes de test vérifient le comportement attendu du service UtilisateurService en utilisant des mocks pour les dépendances.
 * Les méthodes de test couvrent les cas de succès (Happy Path) ainsi que les cas d'exception (Trigger Exception) pour assurer le bon fonctionnement du service.
 * Les méthodes de test vérifient également les paramètres d'entrée et lèvent des exceptions appropriées en cas de paramètres invalides.
 */
class UtilisateurServiceTest extends TestCase
{

    private UtilisateurRepositoryInterface $utilisateurRepository;
    private TableauRepositoryInterface $tableauRepository;
    private MotDePasseInterface $motDePasse;

    private UtilisateurServiceInterface $utilisateurService;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Initialisation des mocks
        $this->utilisateurRepository = $this->createMock(UtilisateurRepository::class);
        $this->tableauRepository = $this->createMock(TableauRepository::class);
        $this->motDePasse = new MotDePasse();

        // Initialisation de l'instance de CarteService avec les mocks
        $this->utilisateurService = new UtilisateurService($this->utilisateurRepository, $this->tableauRepository, $this->motDePasse);
    }

    protected function tearDown(): void
    {
        // Nettoyage des mocks si nécessaire
        unset($this->utilisateurRepository);
        unset($this->tableauRepository);
        unset($this->motDePasse);
        unset($this->utilisateurService);

        parent::tearDown();
    }

    /**
     * @throws ServiceException
     */
    public function testHappyPath_method_getUtilisateur_WithValidLogin()
    {
        $login = "lemoinem";
        $user = new Utilisateur();
        $user->setLogin($login);

        $this->utilisateurRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($login)
            ->willReturn($user);

        $result = $this->utilisateurService->getUtilisateur($login);

        self::assertSame($user, $result);
    }

    public function testTriggerException_method_getUtilisateur_WithInvalidLogin(): void
    {
        $login = null;

        // Configurez le mock de repository pour ne rien retourner
        $this->utilisateurRepository->expects($this->never())
            ->method('recupererParClePrimaire');

        // Assurez-vous qu'une exception est levée
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Le login n'est pas renseigné");
        $this->expectExceptionCode(Response::HTTP_BAD_REQUEST);

        // Appelez la méthode à tester
        $this->utilisateurService->getUtilisateur($login);
    }

    public function testTriggerException_method_getUtilisateur_UserNotFound(): void
    {
        $login = 'john_doe';

        // Configurez le mock de repository pour retourner null
        $this->utilisateurRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($login)
            ->willReturn(null);

        // Assurez-vous qu'une exception est levée
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("L'utilisateur n'existe pas");
        $this->expectExceptionCode(Response::HTTP_NOT_FOUND);

        // Appelez la méthode à tester
        $this->utilisateurService->getUtilisateur($login);
    }

    /**
     * @throws ServiceException
     */
    public function testHappyPath_method_RecupererTableauxOuUtilisateurEstMembre_WithValidLogin(): void
    {
        $login = 'john_doe';
        $expectedResult = ['tableau1', 'tableau2']; // Simulez les tableaux retournés par le repository

        // Configurez le mock de repository pour retourner les tableaux simulés
        $this->utilisateurRepository->expects($this->once())
            ->method('recupererTableauxOuUtilisateurEstMembre')
            ->with($login)
            ->willReturn($expectedResult);

        // Appelez la méthode à tester
        $result = $this->utilisateurService->recupererTableauxOuUtilisateurEstMembre($login);

        // Assurez-vous que les tableaux retournés sont ceux attendus
        $this->assertSame($expectedResult, $result);
    }

    public function testTriggerException_method_RecupererTableauxOuUtilisateurEstMembre_WithInValidLogin(): void
    {
        $login = null;

        // Configurez le mock de repository pour ne pas être appelé
        $this->utilisateurRepository->expects($this->never())
            ->method('recupererTableauxOuUtilisateurEstMembre');

        // Assurez-vous qu'une exception est levée
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Le login doit être compris entre 4 et 32 caractères!");
        $this->expectExceptionCode(Response::HTTP_BAD_REQUEST);

        // Appelez la méthode à tester
        $this->utilisateurService->recupererTableauxOuUtilisateurEstMembre($login);
    }

    /**
     * @throws ServiceException
     */
    public function testHappyPath_create_Utilisateur_ValidParameters(): void
    {
        $login = 'john_doe';
        $nom = 'Doe';
        $prenom = 'John';
        $email = 'john@example.com';
        $mdp = 'Password1';
        $mdp2 = 'Password1';

        // Configurez le mock de repository pour simuler un nouvel utilisateur
        $this->utilisateurRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($login)
            ->willReturn(null); // Simulez qu'aucun utilisateur n'existe avec ce login

        $this->utilisateurRepository->expects($this->once())
            ->method('recupererUtilisateursParEmail')
            ->with($email)
            ->willReturn([]); // Simulez qu'aucun utilisateur n'existe avec cet email

        // Assurez-vous que la méthode ajouter de votre repository est appelée avec le bon utilisateur
        $this->utilisateurRepository->expects($this->once())
            ->method('ajouter')
            ->with($this->callback(function ($user) use ($login, $nom, $prenom, $email) {
                return $user->getLogin() === $login &&
                    $user->getNom() === $nom &&
                    $user->getPrenom() === $prenom &&
                    $user->getEmail() === $email;
            }));

        // Appelez la méthode à tester
        $this->utilisateurService->creerUtilisateur($login, $nom, $prenom, $email, $mdp, $mdp2);
    }

    public function testTriggerException_create_Utilisateur_MissingParameters(): void
    {
        $login = null;
        $nom = null;
        $prenom = null;
        $email = null;
        $mdp = null;
        $mdp2 = null;

        // Configurez le mock de repository pour ne pas être appelé
        $this->utilisateurRepository->expects($this->never())
            ->method('recupererParClePrimaire');

        $this->utilisateurRepository->expects($this->never())
            ->method('recupererUtilisateursParEmail');

        // Assurez-vous qu'une exception est levée
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("le login ou le mdp ou l'email ou le nom ou le prenom n'a pas été renseigné");
        $this->expectExceptionCode(Response::HTTP_BAD_REQUEST);

        // Appelez la méthode à tester
        $this->utilisateurService->creerUtilisateur($login, $nom, $prenom, $email, $mdp, $mdp2);
    }

    public function testTriggerException_create_Utilisateur_LoginAlreadyExist(): void
    {
        // Mock du repository pour simuler qu'un utilisateur avec le même login existe déjà
        $this->utilisateurRepository->method('recupererParClePrimaire')->willReturn(new Utilisateur());

        // Teste si une exception est lancée lorsque le login est déjà pris
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode(Response::HTTP_CONFLICT);

        $this->utilisateurService->creerUtilisateur('johnDoe', 'Doe', 'John', 'john@example.com', 'MotDePasse123', 'MotDePasse123');
    }

    public function testTriggerException_create_Utilisateur_EmailAlreadyExist(): void
    {
        // Mock du repository pour simuler qu'un utilisateur avec le même email existe déjà
        $this->utilisateurRepository->method('recupererUtilisateursParEmail')->willReturn([new Utilisateur()]);

        // Teste si une exception est lancée lorsque l'email est déjà pris
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode(Response::HTTP_CONFLICT);

        $this->utilisateurService->creerUtilisateur('johnDoe', 'Doe', 'John', 'john@example.com', 'MotDePasse123', 'MotDePasse123');
    }

    public function testTriggerException_create_Utilisateur_InvalidLogin(): void
    {
        // Teste si une exception est lancée lorsque le login est invalide
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode(Response::HTTP_BAD_REQUEST);

        $this->utilisateurService->creerUtilisateur('j', 'Doe', 'John', 'john@example.com', 'MotDePasse123', 'MotDePasse123');
    }

    public function testTriggerException_create_Utilisateur_InvalidEmail(): void
    {
        // Teste si une exception est lancée lorsque l'email est invalide
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode(Response::HTTP_BAD_REQUEST);

        $this->utilisateurService->creerUtilisateur('johnDoe', 'Doe', 'John', 'johnexample.com', 'MotDePasse123', 'MotDePasse123');
    }

    public function testTriggerException_create_Utilisateur_InvalidPassword(): void
    {
        // Teste si une exception est lancée lorsque le mot de passe est invalide
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode(Response::HTTP_BAD_REQUEST);

        $this->utilisateurService->creerUtilisateur('johnDoe', 'Doe', 'John', 'john@example.com', 'password', 'password');
    }

    public function testTriggerException_create_Utilisateur_DistinctPassword(): void
    {
        // Teste si une exception est lancée lorsque les mots de passe ne correspondent pas
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode(Response::HTTP_BAD_REQUEST);

        $this->utilisateurService->creerUtilisateur('johnDoe', 'Doe', 'John', 'john@example.com', 'MotDePasse123', 'MotDePasse456');
    }

    public function testTriggerException_create_Utilisateur_AmpleEmail(): void
    {
        // Teste si une exception est lancée lorsque l'email est trop long
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode(Response::HTTP_BAD_REQUEST);

        // Adresse e-mail avec plus de 64 caractères
        $email = 'john@example.com'; // exemple de 15 caractères

        // Ajout de caractères supplémentaires pour dépasser 64 caractères
        $longueurExcedante = 65 - strlen($email);
        $email .= str_repeat('a', $longueurExcedante);

        $this->utilisateurService->creerUtilisateur('johnDoe', 'Doe', 'John', $email, 'MotDePasse123', 'MotDePasse123');
    }

    public function testTriggerException_create_Utilisateur_AmpleName(): void
    {
        // Préparez les données pour le test
        $login = 'johnDoe';
        $nom = 'UnNomTresLongQuiDepasseLaLimiteDe32Caracteres';
        $prenom = 'UnPrenomTresLongQuiDepasseLaLimiteDe32Caracteres';
        $email = 'john.doe@example.com';
        $mdp = 'MotDePasse123';
        $mdp2 = 'MotDePasse123';

        // Configurez le comportement attendu du repository ou d'autres dépendances si nécessaire

        // Exécutez la méthode à tester et vérifiez qu'elle lance une exception
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Le nom et le prénom ne doivent pas faire plus de 32 caractères");
        $this->expectExceptionCode(Response::HTTP_BAD_REQUEST);

        $this->utilisateurService->creerUtilisateur($login, $nom, $prenom, $email, $mdp, $mdp2);
    }

    /**
     * @throws ServiceException
     */
    public function testHappyPath_method_modifierUtilisateur_ValidParameters(): void
    {
        // Définissez les valeurs d'entrée pour le test
        $login = 'johnDoe';
        $nom = 'Doe';
        $prenom = 'John';
        $newMdp = 'NewPassword1';
        $newMdp2 = 'NewPassword1';

        // Créez un objet Utilisateur avec les valeurs d'entrée
        $utilisateur = new Utilisateur();
        $utilisateur->setLogin($login);
        $utilisateur->setNom('OldName');
        $utilisateur->setPrenom('OldName');
        $utilisateur->setMdpHache('OldPasswordHash'); // Simulez un mot de passe déjà haché

        // Configurez le mock de repository pour retourner l'utilisateur existant
        $this->utilisateurRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($login)
            ->willReturn($utilisateur);

        // Assurez-vous que la méthode mettreAJour de votre repository est appelée avec le bon utilisateur
        $this->utilisateurRepository->expects($this->once())
            ->method('mettreAJour')
            ->with($utilisateur);

        // Appelez la méthode à tester
        $this->utilisateurService->modifierUtilisateur($login, $nom, $prenom, $newMdp, $newMdp2);
    }


    public function testTriggerException_method_modifierUtilisateur_MissingParameters(): void
    {
        // Configurez le mock de repository pour ne pas être appelé
        $this->utilisateurRepository->expects($this->never())
            ->method('recupererParClePrimaire');

        // Assurez-vous qu'une exception est levée
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("le login ou l'email ou le nom ou le prenom n'a pas été renseigné");
        $this->expectExceptionCode(Response::HTTP_NOT_FOUND);

        // Appelez la méthode à tester
        $this->utilisateurService->modifierUtilisateur(null, null, null, null, null);
    }

    public function testTriggerException_method_modifierUtilisateur_InvalidLogin(): void
    {
        // Préparez les données pour le test
        $loginUtilisateurConnecte = 'johnDoe'; // Utilisateur connecté avec ce login
        $nom = 'Doe';
        $prenom = 'John';
        $mdp = 'NouveauMotDePasse123';
        $mdp2 = 'NouveauMotDePasse123';

        // Configurez le comportement attendu du repository pour retourner null, ce qui simule un login inexistant
        $this->utilisateurRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($loginUtilisateurConnecte)
            ->willReturn(null);

        // Exécutez la méthode à tester et vérifiez qu'elle lance une exception
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Ce login n'existe pas!");
        $this->expectExceptionCode(Response::HTTP_NOT_FOUND);

        $this->utilisateurService->modifierUtilisateur($loginUtilisateurConnecte, $nom, $prenom, $mdp, $mdp2);
    }

    public function testTriggerException_method_modifierUtilisateur_OldPasswordIcorrect(): void
    {
        // Créer un utilisateur avec un ancien mot de passe correct
        $utilisateur = Utilisateur::create("login", "Nom", "Prénom", "email@example.com", "AncienMdp");

        $this->utilisateurRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($utilisateur->getLogin())
            ->willReturn($utilisateur);

        // S'attendre à ce qu'une exception soit levée avec le message approprié
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Impossible de changer le mot de passe, l'ancien mot de passe est erroné");

        // Appeler la méthode à tester avec le mot de passe incorrect
        $this->utilisateurService->modifierUtilisateur($utilisateur->getLogin(), "NouveauNom", "NouveauPrénom", "nouveauemail@example.com", "AncienMdpIncorrect1", "NouveauMdp1", "NouveauMdp1");
    }

    /**
     * @throws ServiceException
     */
    public function testHappyPath_method_modifierUtilisateur_OldPasswordCorrect(): void
    {
        // Créer un utilisateur avec un ancien mot de passe correct
        $mdpClair = "AncienMdp1";
        $mdpHache = $this->motDePasse->hacher($mdpClair); // Générer le hachage du mot de passe

        $utilisateur = Utilisateur::create("login", "Nom", "Prénom", "email@example.com", $mdpHache);

        $this->utilisateurRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($utilisateur->getLogin())
            ->willReturn($utilisateur);

        // Appeler la méthode à tester avec le mot de passe correct
        $this->utilisateurService->modifierUtilisateur($utilisateur->getLogin(), "NouveauNom", "NouveauPrénom", "nouveauemail@example.com", $mdpClair, "NouveauMdp1", "NouveauMdp1");
    }

    public function testTriggerException_method_modifierUtilisateur_InvalidEmail()
    {
        $utilisateur = Utilisateur::create("login", "Nom", "Prénom", "email@example.com", "mdpHacheTKT");
        $user = Utilisateur::create("autreLogin", "Nom", "Prénom", "t@t.com", "mdpHacheTKT");

        $this->utilisateurRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($utilisateur->getLogin())
            ->willReturn($utilisateur);

        $this->utilisateurRepository->expects($this->once())
            ->method('recupererUtilisateursParEmail')
            ->with($utilisateur->getEmail())
            ->willReturn([$user]);

        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Vous ne pouvez pas entrer un email déjà utilisé par un autre utilisateur");
        $this->expectExceptionCode(Response::HTTP_FORBIDDEN);

        $this->utilisateurService->modifierUtilisateur($utilisateur->getLogin(), $utilisateur->getNom(), $utilisateur->getPrenom(), $utilisateur->getEmail(), $utilisateur->getMdpHache());

    }


    /**
     * @throws ServiceException
     */
    public function testHappyPath_method_verifierIdentifiantUtilisateur_ValidParameters(): void
    {
        // Création d'un utilisateur avec un mot de passe haché connu
        $login = 'johnDoe';
        $mdp = 'MotDePasse123';
        $mdpHache = $this->motDePasse->hacher($mdp); // Hachage du mot de passe

        $utilisateur = new Utilisateur();
        $utilisateur->setLogin($login);
        $utilisateur->setMdpHache($mdpHache);

        // Configuration du mock du repository pour retourner l'utilisateur
        $this->utilisateurRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($login)
            ->willReturn($utilisateur);

        // Appel de la méthode à tester
        $this->utilisateurService->verifierIdentifiantUtilisateur($login, $mdp);
    }


    public function testTriggerException_method_verifierIdentifiantUtilisateur_InvalidParameters(): void
    {
        // Création d'un utilisateur avec un mot de passe haché connu
        $login = 'johnDoe';
        $mdp = 'MotDePasse123';
        $mdpHache = $this->motDePasse->hacher("MotDePasseIncorrect"); // Hachage d'un mot de passe incorrect

        $utilisateur = new Utilisateur();
        $utilisateur->setLogin($login);
        $utilisateur->setMdpHache($mdpHache);

        // Configuration du mock du repository pour retourner l'utilisateur
        $this->utilisateurRepository->expects($this->once())
            ->method('recupererParClePrimaire')
            ->with($login)
            ->willReturn($utilisateur);

        // Appel de la méthode à tester
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Mot de passe incorrect.");

        $this->utilisateurService->verifierIdentifiantUtilisateur($login, $mdp);
    }


    public function testTriggerException_method_verifierIdentifiantUtilisateur_MissingParameters(): void
    {
        // Définissez les valeurs d'entrée pour le test
        $loginManquant = null;
        $mdpManquant = 'motDePasse';

        // Assurez-vous que l'appel à la méthode lance une exception si l'identifiant est manquant
        try {
            $this->utilisateurService->verifierIdentifiantUtilisateur($loginManquant, $mdpManquant);
        } catch (ServiceException $exception) {
            $this->assertEquals(Response::HTTP_BAD_REQUEST, $exception->getCode());
        }

        // Assurez-vous que l'appel à la méthode lance une exception si le mot de passe est manquant
        try {
            $this->utilisateurService->verifierIdentifiantUtilisateur($loginManquant, $mdpManquant);
        } catch (ServiceException $exception) {
            $this->assertEquals(Response::HTTP_BAD_REQUEST, $exception->getCode());
        }
    }

    /**
     * @throws ServiceException
     */
    public function testhappyPath_method_supprimer_ValidParameter(): void
    {
        // Préparez les données pour le test
        $loginUtilisateurConnecte = 'johnDoe'; // Utilisateur connecté avec ce login

        // Configurez le comportement attendu du repository
        $this->utilisateurRepository->expects($this->once())
            ->method('supprimer')
            ->with($loginUtilisateurConnecte);

        // Exécutez la méthode à tester sans qu'elle ne lève d'exception
        $this->utilisateurService->supprimer($loginUtilisateurConnecte);

        // Aucune exception ne devrait être levée
    }

    public function testTriggerException_method_supprimer_InvalidLogin(): void
    {
        // Exécutez la méthode à tester et vérifiez qu'elle lance une exception lorsque $loginUtilisateurConnecte est null
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("le login n'a pas été renseigné");
        $this->expectExceptionCode(Response::HTTP_BAD_REQUEST);

        $this->utilisateurService->supprimer(null);
    }

    /**
     * @throws ServiceException
     */
    public function testHappyPath_method_verifierLoginConnecteEstLoginRenseigne_Success(): void
    {
        // Pas d'exception attendue
        $this->utilisateurService->verifierLoginConnecteEstLoginRenseigne("login", "login");
        $this->assertTrue(true);
    }

    public function testTriggerException_method_verifierLoginConnecteEstLoginRenseigne_InvalidLogin(): void
    {
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Vous n'avez pas accès à cet utilisateur");
        $this->expectExceptionCode(Response::HTTP_UNAUTHORIZED);

        $this->utilisateurService->verifierLoginConnecteEstLoginRenseigne("login1", "login2");
    }
}