<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use RuntimeException;

/*
 * Enrobage léger autour de PDO.
 * Connexion paresseuse (établie au premier appel réel à la base) et partagée.
 */
class Database
{
    private static ?PDO $pdo = null;
    private static array $config = [];

    /** Mémorise la config DB sans encore se connecter. */
    public static function configure(array $dbConfig): void
    {
        self::$config = $dbConfig;
    }

    /** Renvoie la connexion PDO, en l'établissant au besoin. */
    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            if (empty(self::$config)) {
                throw new RuntimeException('Database non configurée : appelle Database::configure($config["db"]).');
            }

            $c   = self::$config;
            $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $c['host'], $c['name']);

            self::$pdo = new PDO($dsn, $c['user'], $c['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }

        return self::$pdo;
    }
}
