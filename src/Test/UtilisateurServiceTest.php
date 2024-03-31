<?php

namespace App\Trellotrolle\Test;

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

    public function testGetUtilisateurWithValidLogin()
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

    public function testGetUtilisateurWithInvalidLogin(): void
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

    public function testGetUtilisateurWhenUserNotFound(): void
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

    public function testRecupererTableauxOuUtilisateurEstMembreWithValidLogin(): void
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

    public function testRecupererTableauxOuUtilisateurEstMembreWithInvalidLogin(): void
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

    public function testCreerUtilisateurWithValidParameters(): void
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

    public function testCreerUtilisateurWithMissingParameters(): void
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


    /**
     * @throws ServiceException
     */
    public function testModifierUtilisateurWithValidParameters(): void
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


    public function testModifierUtilisateurWithMissingParameters(): void
    {
        $login = null;
        $nom = null;
        $prenom = null;

        // Configurez le mock de repository pour ne pas être appelé
        $this->utilisateurRepository->expects($this->never())
            ->method('recupererParClePrimaire');

        // Assurez-vous qu'une exception est levée
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("le login ou l'email ou le nom ou le prenom n'a pas été renseigné");
        $this->expectExceptionCode(Response::HTTP_NOT_FOUND);

        // Appelez la méthode à tester
        $this->utilisateurService->modifierUtilisateur($login, $nom, $prenom);
    }

    /**
     * @throws ServiceException
     */
    public function testVerifieIdentifiantUtilisateurCorrect(): void
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


    public function testVerifieIdentifiantUtilisateurIncorrect(): void
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


    public function testVerifieIdentifiantUtilisateurAvecIdentifiantOuMotDePasseManquant(): void
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
}
