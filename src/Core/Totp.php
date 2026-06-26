<?php

declare(strict_types=1);

namespace App\Core;

/*
 * TOTP (RFC 6238) compatible Google Authenticator / Microsoft Authenticator /
 * FreeOTP : SHA-1, période 30 s, 6 chiffres, secret en Base32 (RFC 4648).
 *
 * Sert à la double authentification des comptes administrateurs.
 */
final class Totp
{
    private const PERIODE  = 30;
    private const CHIFFRES = 6;
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';   // Base32

    /** Génère un nouveau secret aléatoire encodé en Base32 (20 octets = 160 bits). */
    public static function genererSecret(int $octets = 20): string
    {
        return self::base32Encode(random_bytes($octets));
    }

    /** URI otpauth:// à encoder dans un QR code pour l'application d'authentification. */
    public static function uriOtpauth(string $secret, string $compte, string $issuer): string
    {
        $label  = rawurlencode($issuer) . ':' . rawurlencode($compte);
        $params = http_build_query([
            'secret'    => $secret,
            'issuer'    => $issuer,
            'algorithm' => 'SHA1',
            'digits'    => self::CHIFFRES,
            'period'    => self::PERIODE,
        ]);

        return 'otpauth://totp/' . $label . '?' . $params;
    }

    /**
     * Vérifie un code saisi contre le secret, avec une fenêtre de tolérance
     * (±N périodes) pour absorber le décalage d'horloge. Comparaison à temps
     * constant.
     */
    public static function verifier(string $secret, string $code, int $fenetre = 1): bool
    {
        $code = (string) preg_replace('/\s+/', '', $code);
        if (!preg_match('/^\d{' . self::CHIFFRES . '}$/', $code)) {
            return false;
        }

        $compteur = intdiv(time(), self::PERIODE);
        for ($d = -$fenetre; $d <= $fenetre; $d++) {
            if (hash_equals(self::codeAt($secret, $compteur + $d), $code)) {
                return true;
            }
        }

        return false;
    }

    /** Code à 6 chiffres pour un secret (Base32) et un compteur de période donné. */
    public static function codeAt(string $secret, int $compteur): string
    {
        $bin = self::base32Decode($secret);
        if ($bin === '') {
            return '';
        }

        $compteur = max(0, $compteur);
        $message  = pack('N', 0) . pack('N', $compteur);   // compteur 64 bits big-endian
        $hash     = hash_hmac('sha1', $message, $bin, true);

        $offset = ord($hash[strlen($hash) - 1]) & 0x0f;
        $tronque = ((ord($hash[$offset]) & 0x7f) << 24)
                 | ((ord($hash[$offset + 1]) & 0xff) << 16)
                 | ((ord($hash[$offset + 2]) & 0xff) << 8)
                 |  (ord($hash[$offset + 3]) & 0xff);

        $code = $tronque % (10 ** self::CHIFFRES);

        return str_pad((string) $code, self::CHIFFRES, '0', STR_PAD_LEFT);
    }

    private static function base32Decode(string $b32): string
    {
        $b32 = (string) preg_replace('/[^A-Z2-7]/', '', strtoupper($b32));
        if ($b32 === '') {
            return '';
        }

        $bits = '';
        foreach (str_split($b32) as $c) {
            $bits .= str_pad(decbin((int) strpos(self::ALPHABET, $c)), 5, '0', STR_PAD_LEFT);
        }

        $out = '';
        foreach (str_split($bits, 8) as $octet) {
            if (strlen($octet) === 8) {
                $out .= chr((int) bindec($octet));
            }
        }

        return $out;
    }

    private static function base32Encode(string $bin): string
    {
        if ($bin === '') {
            return '';
        }

        $bits = '';
        foreach (str_split($bin) as $c) {
            $bits .= str_pad(decbin(ord($c)), 8, '0', STR_PAD_LEFT);
        }

        $out = '';
        foreach (str_split($bits, 5) as $chunk) {
            $out .= self::ALPHABET[(int) bindec(str_pad($chunk, 5, '0', STR_PAD_RIGHT))];
        }

        return $out;
    }
}
