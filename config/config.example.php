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
        // URL absolue du site (origine + éventuel sous-dossier), SANS slash final.
        // Sert à construire les liens dans les e-mails. Laisser vide = déduit de
        // la requête courante (scheme + hôte + BASE_URL).
        'url'   => '',       // ex. 'https://pathfinders.sixk.me'
    ],
    // Envoi d'e-mails transactionnels (mot de passe oublié) via SMTP.
    // Laisser 'host' vide désactive proprement l'envoi (aucun e-mail expédié).
    // o2switch : host = mail.pathfinders.sixk.me (ou ton serveur), port 465 (ssl)
    // ou 587 (tls), user = l'adresse e-mail complète, pass = son mot de passe.
    'mail' => [
        'host'       => '',                          // serveur SMTP — vide = envoi désactivé
        'port'       => 465,                         // 465 (ssl) ou 587 (tls)
        'secure'     => 'ssl',                        // 'ssl' ou 'tls'
        'user'       => '',                          // identifiant SMTP (adresse e-mail)
        'pass'       => '',                          // mot de passe SMTP — NE PAS versionner
        'from_email' => 'contact@pathfinders.sixk.me',
        'from_name'  => 'Backcountry Pathfinders',
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
