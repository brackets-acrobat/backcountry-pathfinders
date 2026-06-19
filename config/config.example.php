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
];
