<?php

declare(strict_types=1);

namespace App\Core;

/*
 * Internationalisation (FR / EN).
 *
 * Langue choisie dans l'ordre : forcée > session > navigateur (Accept-Language) > défaut.
 * Les traductions sont de simples tableaux clé => texte dans src/lang/{langue}.php.
 * Interpolation : t('bonjour', ['nom' => 'X']) avec "Bonjour {nom}" dans le fichier.
 */
class Lang
{
    private const DISPONIBLES = ['fr', 'en'];
    private const DEFAUT = 'fr';

    private static string $langue = self::DEFAUT;
    /** @var array<string,string> */
    private static array $traductions = [];

    /** Détermine et charge la langue active (à appeler après le démarrage de session). */
    public static function initialiser(): void
    {
        $langue = $_SESSION['langue']
            ?? self::detecterNavigateur()
            ?? self::DEFAUT;

        self::definir($langue);
    }

    /** Fixe la langue active, la mémorise en session et charge ses traductions. */
    public static function definir(string $langue): void
    {
        if (!in_array($langue, self::DISPONIBLES, true)) {
            $langue = self::DEFAUT;
        }
        self::$langue = $langue;
        $_SESSION['langue'] = $langue;
        self::$traductions = require dirname(__DIR__) . "/lang/{$langue}.php";
    }

    public static function actuelle(): string
    {
        return self::$langue;
    }

    /** @return array<int,string> */
    public static function disponibles(): array
    {
        return self::DISPONIBLES;
    }

    /**
     * Traduit une clé. Repli sur la clé elle-même si absente (pratique en dev).
     *
     * @param array<string,string|int> $params
     */
    public static function t(string $cle, array $params = []): string
    {
        $texte = self::$traductions[$cle] ?? $cle;
        foreach ($params as $k => $v) {
            $texte = str_replace('{' . $k . '}', (string) $v, $texte);
        }

        return $texte;
    }

    /** Devine la langue depuis l'en-tête Accept-Language, ou null si non gérée. */
    private static function detecterNavigateur(): ?string
    {
        $accept = strtolower(trim($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''));
        $code = substr($accept, 0, 2);

        return in_array($code, self::DISPONIBLES, true) ? $code : null;
    }
}
