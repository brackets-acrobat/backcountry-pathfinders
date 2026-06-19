<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Lang;
use App\Core\View;

/** @var string $content  Contenu de la vue, injecté par View::render() */
/** @var string $title    Titre de page (optionnel) */
$title = $title ?? 'Backcountry Pathfinders';
?>
<!doctype html>
<html lang="<?= View::e(Lang::actuelle()) ?>">
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
            <a href="<?= BASE_URL ?>/"><?= t('nav.map') ?></a>
            <?php if (Auth::estConnecte()): ?>
                <span class="nav-user"><?= View::e(Auth::utilisateur()['pseudo']) ?></span>
                <a href="<?= BASE_URL ?>/deconnexion"><?= t('nav.logout') ?></a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/connexion"><?= t('nav.login') ?></a>
                <a href="<?= BASE_URL ?>/inscription"><?= t('nav.register') ?></a>
            <?php endif; ?>
            <?php $autre = Lang::actuelle() === 'fr' ? 'en' : 'fr'; ?>
            <a class="lang-toggle" href="<?= BASE_URL ?>/langue/<?= $autre ?>"
               title="<?= t('lang.switch') ?>" aria-label="<?= t('lang.switch') ?>">
                <span class="lang-opt<?= Lang::actuelle() === 'fr' ? ' is-active' : '' ?>">FR</span>
                <span class="lang-opt<?= Lang::actuelle() === 'en' ? ' is-active' : '' ?>">EN</span>
            </a>
        </nav>
    </header>

    <main class="site-main">
        <?= $content ?>
    </main>

    <footer class="site-footer">
        <small><?= t('footer.tagline') ?></small>
    </footer>

    <script src="<?= BASE_URL ?>/assets/js/app.js"></script>
</body>
</html>
