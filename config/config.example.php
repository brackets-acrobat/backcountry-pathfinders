<?php

declare(strict_types=1);

/*
 * Modèle de configuration.
 * Copie ce fichier en "config.php" et renseigne tes identifiants.
 * config.php n'est PAS versionné (voir .gitignore).
 */

return [
    'db' => [
        'host' => '127.0.0.1',
        'name' => 'backcountry',
        'user' => 'root',
        'pass' => '',        // mot de passe MySQL (vide par défaut sous XAMPP)
    ],
    'app' => [
        'debug' => true,     // false en production
        'name'  => 'Backcountry Pathfinders',
    ],
    // Cloudflare Turnstile (anti-bot, https://dash.cloudflare.com → Turnstile).
    // Laisser les deux clés vides désactive proprement le CAPTCHA (dev sans clés).
    'turnstile' => [
        'site_key'   => '',   // clé de site (publique), commence par 0x...
        'secret_key' => '',   // clé secrète (serveur) — NE PAS versionner
    ],
    // Géocodage inverse (BigDataCloud, gratuit, sans clé) : à la création d'un
    // lieu, renseigne pays + région (FR & EN, codes ISO). Échec silencieux.
    'geocodage' => [
        'actif' => true,      // false pour désactiver (aucun appel réseau)
    ],
];
