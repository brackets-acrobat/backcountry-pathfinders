<?php

declare(strict_types=1);

namespace App\Core;

/*
 * Géocodage inverse : à partir de coordonnées, retrouve le pays et la
 * subdivision administrative (région / état / province).
 *
 * Fournisseur : BigDataCloud (endpoint « reverse-geocode-client », gratuit
 * et sans clé). Il renvoie notamment, dans la langue demandée :
 *   - countryCode             : ISO 3166-1 alpha-2  (ex. "FR")
 *   - countryName             : nom du pays         (ex. "France")
 *   - principalSubdivision    : libellé de la région (ex. "Bavière" / "Bavaria")
 *   - principalSubdivisionCode: ISO 3166-2          (ex. "DE-BY")
 *
 * Les LIBELLÉS diffèrent selon la langue et ne sont pas dérivables hors-ligne :
 * on interroge donc l'API en français ET en anglais (deux appels) pour stocker
 * les deux. Les CODES sont identiques d'une langue à l'autre.
 *
 * Appelé une seule fois, côté serveur, à la création d'un lieu. Tout échec
 * (réseau, timeout, point en pleine mer) est silencieux : les valeurs
 * indisponibles valent null et la création du lieu se poursuit (fail-soft).
 */
class Geocodage
{
    private const URL = 'https://api.bigdatacloud.net/data/reverse-geocode-client';
    private const TIMEOUT_S = 4;

    private static bool $actif = true;

    /** @param array{actif?:bool} $config */
    public static function configure(array $config): void
    {
        if (array_key_exists('actif', $config)) {
            self::$actif = (bool) $config['actif'];
        }
    }

    /** Le géocodage est-il activé (config) ? */
    public static function estActif(): bool
    {
        return self::$actif;
    }

    /**
     * Géocode inverse un point. Renvoie toujours les six clés ; chacune vaut
     * null si indisponible (désactivé, échec réseau, ou point hors terre).
     * Les noms anglais retombent sur les français si le 2e appel échoue.
     *
     * @return array{
     *   pays:?string, region_code:?string,
     *   pays_fr:?string, pays_en:?string,
     *   region_fr:?string, region_en:?string
     * }
     */
    public static function inverse(float $lat, float $lon): array
    {
        $vide = [
            'pays' => null, 'region_code' => null,
            'pays_fr' => null, 'pays_en' => null,
            'region_fr' => null, 'region_en' => null,
        ];

        if (!self::$actif) {
            return $vide;
        }

        $fr = self::appel($lat, $lon, 'fr');
        if ($fr === null) {
            return $vide;   // pas de données de base → on renonce
        }
        $en = self::appel($lat, $lon, 'en');   // peut être null (on retombera sur fr)

        $paysFr   = self::nettoyer($fr['countryName'] ?? null);
        $regionFr = self::nettoyer($fr['principalSubdivision'] ?? null);
        $paysEn   = $en !== null ? self::nettoyer($en['countryName'] ?? null) : null;
        $regionEn = $en !== null ? self::nettoyer($en['principalSubdivision'] ?? null) : null;

        $code = self::nettoyer($fr['principalSubdivisionCode'] ?? null);
        $pays = self::nettoyer($fr['countryCode'] ?? null);

        return [
            'pays'        => $pays !== null ? strtoupper(substr($pays, 0, 2)) : null,
            'region_code' => $code !== null ? substr($code, 0, 10) : null,
            'pays_fr'     => self::tronquer(self::nomPays($paysFr), 80),
            'pays_en'     => self::tronquer(self::nomPays($paysEn ?? $paysFr), 80),
            'region_fr'   => $regionFr !== null ? substr($regionFr, 0, 120) : null,
            'region_en'   => self::tronquer($regionEn ?? $regionFr, 120),
        ];
    }

    /**
     * Nettoie un nom de pays : BigDataCloud renvoie le nom officiel ISO avec
     * l'article entre parenthèses (« Allemagne (l') », « United States of
     * America (the) »). On retire ce suffixe pour un affichage lisible.
     */
    private static function nomPays(?string $nom): ?string
    {
        if ($nom === null) {
            return null;
        }
        $nom = trim(preg_replace('/\s*\([^)]*\)\s*$/u', '', $nom) ?? $nom);

        return $nom !== '' ? $nom : null;
    }

    /** Un appel à l'API dans une langue ; tableau décodé ou null si échec. */
    private static function appel(float $lat, float $lon, string $langue): ?array
    {
        $url = self::URL . '?' . http_build_query([
            'latitude'         => $lat,
            'longitude'        => $lon,
            'localityLanguage' => $langue,
        ]);

        $corps = self::recuperer($url);
        if ($corps === null) {
            return null;
        }
        $j = json_decode($corps, true);

        return is_array($j) ? $j : null;
    }

    /** Tronque une valeur déjà nettoyée (ou null) à une longueur maximale. */
    private static function tronquer(?string $valeur, int $max): ?string
    {
        return $valeur !== null ? substr($valeur, 0, $max) : null;
    }

    /** Trim + chaîne vide → null. */
    private static function nettoyer(mixed $valeur): ?string
    {
        if (!is_string($valeur)) {
            return null;
        }
        $valeur = trim($valeur);

        return $valeur !== '' ? $valeur : null;
    }

    /**
     * GET HTTP. Utilise cURL si disponible, sinon file_get_contents.
     * Renvoie le corps de la réponse, ou null en cas d'échec.
     */
    private static function recuperer(string $url): ?string
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => self::TIMEOUT_S,
                CURLOPT_CONNECTTIMEOUT => self::TIMEOUT_S,
                CURLOPT_USERAGENT      => 'BackcountryPathfinders/1.0',
            ]);
            $res  = curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $ok   = ($res !== false) && curl_errno($ch) === 0 && $code >= 200 && $code < 300;
            curl_close($ch);

            return $ok ? (string) $res : null;
        }

        $contexte = stream_context_create([
            'http' => [
                'method'        => 'GET',
                'header'        => "User-Agent: BackcountryPathfinders/1.0\r\n",
                'timeout'       => self::TIMEOUT_S,
                'ignore_errors' => true,
            ],
        ]);
        $res = @file_get_contents($url, false, $contexte);

        return $res !== false ? $res : null;
    }
}
