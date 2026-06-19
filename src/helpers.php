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
