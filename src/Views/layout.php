<?php

declare(strict_types=1);

use App\Core\View;

/** @var string $content  Contenu de la vue, injecté par View::render() */
/** @var string $title    Titre de page (optionnel) */
$title = $title ?? 'Backcountry Pathfinders';
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= View::e($title) ?> — Backcountry Pathfinders</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <a class="brand" href="<?= BASE_URL ?>/">🏔️ Backcountry Pathfinders</a>
        <nav class="site-nav">
            <a href="<?= BASE_URL ?>/">Carte</a>
        </nav>
    </header>

    <main class="site-main">
        <?= $content ?>
    </main>

    <footer class="site-footer">
        <small>Backcountry Pathfinders community — relevés de lieux de poser MSFS 2024</small>
    </footer>

    <script src="<?= BASE_URL ?>/assets/js/app.js"></script>
</body>
</html>
