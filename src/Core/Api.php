<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\CleApi;

/*
 * Aides pour les endpoints API (JSON entrant/sortant + authentification par clé).
 * Les messages d'erreur API sont en français (l'appli desktop consommatrice l'est).
 */
class Api
{
    /**
     * Corps de requête décodé depuis du JSON.
     *
     * @return array<string,mixed>
     */
    public static function corpsJson(): array
    {
        $brut = file_get_contents('php://input') ?: '';
        $brut = preg_replace('/^\xEF\xBB\xBF/', '', $brut);   // ignore un BOM UTF-8 éventuel
        $data = json_decode($brut, true);

        return is_array($data) ? $data : [];
    }

    /**
     * Envoie une réponse JSON et termine la requête.
     *
     * @param array<string,mixed> $donnees
     */
    public static function repondre(array $donnees, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($donnees, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /** Réponse d'erreur JSON normalisée (et fin de requête). */
    public static function erreur(string $message, int $code = 400): void
    {
        self::repondre(['ok' => false, 'erreur' => $message], $code);
    }

    /**
     * Authentifie la requête via la clé API (en-tête Authorization: Bearer ou X-Api-Key).
     * Répond 401 et termine si la clé est absente ou invalide ; sinon renvoie la ligne cle_api.
     *
     * @return array<string,mixed>
     */
    public static function authentifier(): array
    {
        $cle = self::cleFournie();
        if ($cle === null || $cle === '') {
            self::erreur('Clé API manquante.', 401);
        }

        $ligne = CleApi::authentifier($cle);
        if ($ligne === null) {
            self::erreur('Clé API invalide.', 401);
        }

        CleApi::toucher((int) $ligne['id']);

        return $ligne;
    }

    /** Extrait la clé fournie depuis les en-têtes. */
    private static function cleFournie(): ?string
    {
        $headers = self::enTetes();

        $auth = $headers['authorization'] ?? '';
        if (stripos($auth, 'Bearer ') === 0) {
            return trim(substr($auth, 7));
        }

        return $headers['x-api-key'] ?? null;
    }

    /**
     * En-têtes HTTP en minuscules.
     *
     * @return array<string,string>
     */
    private static function enTetes(): array
    {
        $out = [];

        if (function_exists('getallheaders')) {
            foreach (getallheaders() as $k => $v) {
                $out[strtolower($k)] = $v;
            }
        }

        // Repli / complément depuis $_SERVER (X-Api-Key arrive toujours ici).
        foreach ($_SERVER as $k => $v) {
            if (str_starts_with($k, 'HTTP_')) {
                $nom = strtolower(str_replace('_', '-', substr($k, 5)));
                $out[$nom] ??= $v;
            }
        }
        // Apache peut ranger Authorization ici via la règle .htaccess.
        if (!isset($out['authorization']) && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $out['authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }

        return $out;
    }
}
