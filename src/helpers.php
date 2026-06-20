<?php

declare(strict_types=1);

/*
 * Fonctions d'aide globales, disponibles dans les vues et les contrôleurs.
 * Chargé depuis public/index.php après l'autoloader.
 */

use App\Core\Lang;
use App\Core\Turnstile;

if (!function_exists('t')) {
    /**
     * Traduit une clé dans la langue active.
     *
     * @param array<string,string|int> $params
     */
    function t(string $cle, array $params = []): string
    {
        return Lang::t($cle, $params);
    }
}

if (!function_exists('pieds')) {
    /**
     * Convertit une distance stockée en mètres vers des pieds (ft), arrondie.
     * Les données restent en mètres en base (colonnes *_m, source SimConnect) ;
     * l'aéronautique s'exprimant en pieds, on ne convertit qu'à l'affichage.
     *
     * @param int|float|string|null $metres
     */
    function pieds($metres): ?int
    {
        if ($metres === null || $metres === '') {
            return null;
        }

        return (int) round((float) $metres * 3.280839895);
    }
}

if (!function_exists('turnstile_widget')) {
    /**
     * Markup du widget Cloudflare Turnstile (script + conteneur), ou chaîne vide
     * si le CAPTCHA est désactivé. À placer à l'intérieur du <form>.
     */
    function turnstile_widget(): string
    {
        if (!Turnstile::estActif()) {
            return '';
        }

        $cle = htmlspecialchars(Turnstile::clePublique(), ENT_QUOTES, 'UTF-8');

        return '<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>'
            . '<div class="cf-turnstile" data-sitekey="' . $cle . '"></div>';
    }
}

if (!function_exists('asset')) {
    /**
     * URL d'un fichier de public/assets/, versionnée par sa date de modification
     * (« cache-busting ») : force le navigateur à recharger après chaque changement.
     */
    function asset(string $chemin): string
    {
        $chemin = ltrim($chemin, '/');
        $absolu = dirname(__DIR__) . '/public/assets/' . $chemin;
        $version = is_file($absolu) ? filemtime($absolu) : '1';

        return BASE_URL . '/assets/' . $chemin . '?v=' . $version;
    }
}
