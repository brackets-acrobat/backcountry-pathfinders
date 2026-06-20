<?php

declare(strict_types=1);

/*
 * Fonctions d'aide globales, disponibles dans les vues et les contrôleurs.
 * Chargé depuis public/index.php après l'autoloader.
 */

use App\Core\Lang;

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
