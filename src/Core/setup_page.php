<?php

declare(strict_types=1);

/*
 * Page affichée tant que les dépendances Composer ne sont pas installées
 * (dossier vendor/ absent). Sert de confirmation que le squelette est en place
 * et rappelle les étapes restantes.
 */

http_response_code(503);
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Backcountry Pathfinders — Installation</title>
    <script src="https://unpkg.com/@phosphor-icons/web@2"></script>
    <style>
        body { font-family: system-ui, sans-serif; background: #1b2a23; color: #e8efe9;
               margin: 0; min-height: 100vh; display: grid; place-items: center; }
        .card { max-width: 640px; padding: 2.5rem; background: #233a2f; border-radius: 14px;
                box-shadow: 0 10px 40px rgba(0,0,0,.35); }
        h1 { margin: 0 0 .25rem; font-size: 1.5rem; }
        .sub { color: #9fc2ad; margin: 0 0 1.5rem; }
        ol { line-height: 1.8; }
        code { background: #16241d; padding: .15em .45em; border-radius: 5px; color: #aef0c4; }
        .ok { color: #6ee7a0; }
        i[class^="ph-"] { color: #3af24b; vertical-align: middle; }
        h1 i { color: #3af24b; }
    </style>
</head>
<body>
    <div class="card">
        <h1><i class="ph-light ph-mountains"></i> Backcountry Pathfinders</h1>
        <p class="sub">Le squelette du site est en place. <i class="ph-bold ph-check ok"></i></p>
        <p>Il reste à installer les dépendances PHP pour activer le routeur :</p>
        <ol>
            <li>Installer <strong>Composer</strong> (une fois) : <code>getcomposer.org</code></li>
            <li>Dans le dossier du projet, lancer : <code>composer install</code></li>
            <li>Recharger cette page.</li>
        </ol>
    </div>
</body>
</html>
