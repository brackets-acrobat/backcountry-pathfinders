<?php

declare(strict_types=1);

namespace App\Core;

/*
 * Cloudflare Turnstile : CAPTCHA anti-bot, respectueux de la vie privée.
 *
 * Deux clés (voir config) : clé de site (publique, dans le HTML) et clé
 * secrète (serveur, pour vérifier le jeton). Si l'une des deux est vide, le
 * CAPTCHA est désactivé proprement (les formulaires fonctionnent sans).
 *
 * Flux : le widget JS pose un jeton dans le champ « cf-turnstile-response » ;
 * à la soumission, on POST ce jeton + la clé secrète vers l'API de vérification.
 */
class Turnstile
{
    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
    private const CHAMP = 'cf-turnstile-response';

    private static string $siteKey = '';
    private static string $secretKey = '';

    /** @param array{site_key?:string, secret_key?:string} $config */
    public static function configure(array $config): void
    {
        self::$siteKey   = trim((string) ($config['site_key'] ?? ''));
        self::$secretKey = trim((string) ($config['secret_key'] ?? ''));
    }

    /** CAPTCHA actif seulement si les deux clés sont renseignées. */
    public static function estActif(): bool
    {
        return self::$siteKey !== '' && self::$secretKey !== '';
    }

    /** Clé de site (publique) pour le widget HTML. */
    public static function clePublique(): string
    {
        return self::$siteKey;
    }

    /** Nom du champ POST renvoyé par le widget. */
    public static function champ(): string
    {
        return self::CHAMP;
    }

    /**
     * Vérifie le jeton Turnstile auprès de Cloudflare.
     * Si le CAPTCHA est désactivé (pas de clés), renvoie true (rien à vérifier).
     */
    public static function verifier(?string $jeton, ?string $ip = null): bool
    {
        if (!self::estActif()) {
            return true;
        }
        if (!is_string($jeton) || $jeton === '') {
            return false;
        }

        $donnees = [
            'secret'   => self::$secretKey,
            'response' => $jeton,
        ];
        if ($ip !== null && $ip !== '') {
            $donnees['remoteip'] = $ip;
        }

        $reponse = self::poster($donnees);
        if ($reponse === null) {
            return false;   // échec réseau/transport → on refuse (fail-closed)
        }

        $json = json_decode($reponse, true);

        return is_array($json) && ($json['success'] ?? false) === true;
    }

    /**
     * POST x-www-form-urlencoded vers l'API de vérification.
     * Utilise cURL si disponible, sinon file_get_contents. Renvoie le corps ou null.
     */
    private static function poster(array $donnees): ?string
    {
        $corps = http_build_query($donnees);

        if (function_exists('curl_init')) {
            $ch = curl_init(self::VERIFY_URL);
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $corps,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 5,
                CURLOPT_CONNECTTIMEOUT => 5,
            ]);
            $res = curl_exec($ch);
            $ok  = ($res !== false) && curl_errno($ch) === 0;
            curl_close($ch);

            return $ok ? (string) $res : null;
        }

        $contexte = stream_context_create([
            'http' => [
                'method'        => 'POST',
                'header'        => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content'       => $corps,
                'timeout'       => 5,
                'ignore_errors' => true,
            ],
        ]);
        $res = @file_get_contents(self::VERIFY_URL, false, $contexte);

        return $res !== false ? $res : null;
    }
}
