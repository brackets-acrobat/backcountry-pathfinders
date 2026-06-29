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
    private const COOKIE = 'langue';
    private const COOKIE_DUREE = 31536000;   // 1 an, en secondes

    private static string $langue = self::DEFAUT;
    /** @var array<string,string> */
    private static array $traductions = [];

    /** Détermine et charge la langue active (à appeler après le démarrage de session). */
    public static function initialiser(): void
    {
        $langue = $_SESSION['langue']
            ?? ($_COOKIE[self::COOKIE] ?? null)
            ?? self::detecterNavigateur()
            ?? self::DEFAUT;

        // Au démarrage, on ne ré-écrit pas le cookie (déjà posé lors du choix).
        self::definir($langue, false);
    }

    /**
     * Fixe la langue active, la charge, et — si demandé — mémorise le choix.
     * Le cookie (1 an) survit à la déconnexion (qui détruit la session) et à
     * la fermeture du navigateur.
     */
    public static function definir(string $langue, bool $memoriser = true): void
    {
        if (!in_array($langue, self::DISPONIBLES, true)) {
            $langue = self::DEFAUT;
        }
        self::$langue = $langue;
        $_SESSION['langue'] = $langue;

        if ($memoriser && !headers_sent()) {
            setcookie(self::COOKIE, $langue, [
                'expires'  => time() + self::COOKIE_DUREE,
                'path'     => '/',
                'samesite' => 'Lax',
                'secure'   => !empty($_SERVER['HTTPS']),   // transmis en clair sur HTTP local, chiffré en prod
            ]);
        }

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
