<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Configuration\ConfigurationBaseDeDonneesInterface;
use PDO;

/**
 * Classe ConnexionBaseDeDonnees
 *
 * Cette classe implémente l'interface ConnexionBaseDeDonneesInterface et fournit une méthode getPdo() pour obtenir une instance de PDO connectée à la base de données.
 *
 * @package App\Trellotrolle\Modele\Repository
 */
class ConnexionBaseDeDonnees implements ConnexionBaseDeDonneesInterface
{

    /**
     * Attribut privé de type PDO.
     */
    private PDO $pdo;

    /**
     * Méthode getPdo()
     *
     * Cette méthode retourne une instance de la classe PDO.
     *
     * @return PDO L'instance de la classe PDO.
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * Constructeur de la classe.
     *
     * Ce constructeur crée une nouvelle instance de la classe PDO en utilisant les informations de configuration de la base de données fournies par l'objet $configurationBDD.
     * Les informations de configuration comprennent le DSN, le login, le mot de passe et les options.
     * L'attribut $pdo est initialisé avec l'instance de PDO créée.
     * L'attribut $pdo est configuré pour afficher les erreurs et exceptions.
     *
     * @param ConfigurationBaseDeDonneesInterface $configurationBDD L'objet contenant les informations de configuration de la base de données.
     * @return void
     */
    public function __construct(ConfigurationBaseDeDonneesInterface $configurationBDD)
    {

        $this->pdo = new PDO(
            $configurationBDD->getDSN(),
            $configurationBDD->getLogin(),
            $configurationBDD->getMotDePasse(),
            $configurationBDD->getOptions()
        );

        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
}