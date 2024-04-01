<?php

namespace App\Trellotrolle\Lib;

use App\Trellotrolle\Modele\HTTP\Session;

/**
 * Classe MessageFlash
 *
 * Cette classe gère les messages flash, qui sont des messages temporaires stockés en session.
 * Les messages flash peuvent être ajoutés, vérifiés, lus et supprimés.
 * Les messages flash sont stockés dans la session en utilisant la classe Session.
 * Les types de messages flash pris en charge sont : success, info, warning et danger.
 * Les messages flash peuvent être lus par type ou tous les messages peuvent être lus en une seule fois.
 */
class MessageFlash
{

    /**
     * Attribut statique de la classe MessageFlash.
     * Il représente la clé utilisée pour stocker les messages flash en session.
     */
    private static string $cleFlash = "_messagesFlash";

    /**
     * Méthode statique pour ajouter un message flash.
     *
     * Cette méthode permet d'ajouter un message flash de type spécifié avec le message spécifié.
     * Le message flash est stocké en session en utilisant la classe Session.
     * Si le type de message flash existe déjà en session, le message est ajouté à la liste existante.
     * Sinon, une nouvelle liste de messages flash est créée avec le type et le message spécifiés.
     *
     * @param string $type Le type de message flash (success, info, warning, danger).
     * @param string $message Le message flash à ajouter.
     * @return void
     */
    public static function ajouter(string $type, string $message): void
    {
        $session = Session::getInstance();

        $messagesFlash = [];
        if ($session->contient(MessageFlash::$cleFlash))
            $messagesFlash = $session->lire(MessageFlash::$cleFlash);

        $messagesFlash[$type][] = $message;
        $session->enregistrer(MessageFlash::$cleFlash, $messagesFlash);
    }

    /**
     * Méthode statique pour vérifier si un type de message flash existe dans la session.
     *
     * Cette méthode vérifie si le type de message flash spécifié existe dans la session.
     * Elle utilise la classe Session pour accéder à la session en cours.
     * La méthode retourne true si le type de message flash existe et contient des messages, sinon elle retourne false.
     *
     * @param string $type Le type de message flash à vérifier.
     * @return bool True si le type de message flash existe et contient des messages, sinon false.
     */
    public static function contientMessage(string $type): bool
    {
        $session = Session::getInstance();
        return $session->contient(MessageFlash::$cleFlash) &&
            array_key_exists($type, $session->lire(MessageFlash::$cleFlash)) &&
            !empty($session->lire(MessageFlash::$cleFlash)[$type]);
    }

    /**
     * Méthode statique pour lire les messages flash d'un certain type.
     *
     * Cette méthode permet de lire les messages flash d'un type spécifié.
     * Elle utilise la classe Session pour accéder à la session en cours.
     * Si le type de message flash n'existe pas dans la session ou s'il ne contient pas de messages, un tableau vide est retourné.
     * Sinon, les messages flash du type spécifié sont récupérés, supprimés de la session et retournés.
     *
     * @param string $type Le type de message flash à lire.
     * @return array Les messages flash du type spécifié.
     */
    public static function lireMessages(string $type): array
    {
        $session = Session::getInstance();
        if (!MessageFlash::contientMessage($type))
            return [];

        $messagesFlash = $session->lire(MessageFlash::$cleFlash);
        $messages = $messagesFlash[$type];
        unset($messagesFlash[$type]);
        $session->enregistrer(MessageFlash::$cleFlash, $messagesFlash);

        return $messages;
    }

    /**
     * Méthode statique pour lire tous les messages flash.
     *
     * Cette méthode permet de lire tous les messages flash de tous les types (success, info, warning, danger).
     * Elle utilise la méthode lireMessages() de la classe MessageFlash pour récupérer les messages de chaque type.
     * Les messages flash sont stockés dans un tableau associatif où chaque clé est un type de message et chaque valeur est un tableau de messages de ce type.
     * La méthode retourne le tableau associatif contenant tous les messages flash.
     *
     * @return array Le tableau associatif contenant tous les messages flash.
     */
    public static function lireTousMessages(): array
    {
        $tousMessages = [];
        foreach (["success", "info", "warning", "danger"] as $type) {
            $tousMessages[$type] = MessageFlash::lireMessages($type);
        }
        return $tousMessages;
    }

}