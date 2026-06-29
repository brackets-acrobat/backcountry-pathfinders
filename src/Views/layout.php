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
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <!-- Icônes Phosphor (https://phosphoricons.com/) — tous les poids -->
    <script src="https://unpkg.com/@phosphor-icons/web@2"></script>
</head>
<body>
    <header class="site-header">
        <a class="brand" href="<?= BASE_URL ?>/">
            <img src="<?= asset('img/logo.png') ?>" alt="Backcountry Pathfinders">
        </a>

        <!-- Bloc nav : en ligne sur desktop, popover déroulant (hamburger) sur mobile -->
        <div class="nav-panel" id="nav-panel">
            <div class="header-left">
                <a href="<?= BASE_URL ?>/presentation"><?= t('nav.presentation') ?></a>
                <a href="<?= BASE_URL ?>/actualites"><?= t('nav.news') ?></a>
                <a href="<?= BASE_URL ?>/pilotes"><?= t('nav.pilots') ?></a>
                <a href="<?= BASE_URL ?>/carte"><?= t('nav.map') ?></a>
            </div>
            <nav class="site-nav">
                <?php if (!Auth::estConnecte()): ?>
                    <a href="<?= BASE_URL ?>/connexion"><?= t('nav.login') ?></a>
                    <a href="<?= BASE_URL ?>/inscription"><?= t('nav.register') ?></a>
                <?php else: ?>
                    <?php $u = Auth::utilisateur(); $monAvatar = $u['avatar'] ?? null; ?>

                    <!-- Desktop : avatar + menu déroulant -->
                    <div class="user-menu">
                        <button type="button" class="user-avatar" id="user-menu-btn"
                                aria-haspopup="true" aria-expanded="false" aria-label="<?= t('nav.account') ?>">
                            <?php if ($monAvatar !== null && $monAvatar !== ''): ?>
                                <img src="<?= BASE_URL ?>/uploads/<?= View::e((string) $monAvatar) ?>" alt="">
                            <?php else: ?>
                                <i class="ph-light ph-user"></i>
                            <?php endif; ?>
                        </button>
                        <ul class="user-dropdown" id="user-dropdown" hidden>
                            <li class="user-dropdown-head"><?= View::e((string) $u['pseudo']) ?></li>
                            <li><a href="<?= BASE_URL ?>/compte"><?= t('nav.account') ?></a></li>
                            <li><a href="<?= BASE_URL ?>/mes-lieux"><?= t('nav.my_places') ?></a></li>
                            <li><a href="<?= BASE_URL ?>/mes-vols"><?= t('nav.my_flights') ?></a></li>
                            <?php if (Auth::estAdmin()): ?>
                                <li><a href="<?= BASE_URL ?>/admin"><?= t('nav.admin') ?></a></li>
                            <?php endif; ?>
                            <li><a href="<?= BASE_URL ?>/deconnexion"><?= t('nav.logout') ?></a></li>
                        </ul>
                    </div>

                    <!-- Mobile : rubrique « Utilisateur » repliable (accordéon natif) -->
                    <details class="nav-acc">
                        <summary class="nav-acc-summary">
                            <i class="ph-light ph-user"></i>
                            <span><?= t('nav.user_section') ?></span>
                            <i class="ph-light ph-plus nav-acc-plus"></i>
                        </summary>
                        <ul class="nav-acc-links">
                            <li><a href="<?= BASE_URL ?>/compte"><?= t('nav.account') ?></a></li>
                            <li><a href="<?= BASE_URL ?>/mes-lieux"><?= t('nav.my_places') ?></a></li>
                            <li><a href="<?= BASE_URL ?>/mes-vols"><?= t('nav.my_flights') ?></a></li>
                            <?php if (Auth::estAdmin()): ?>
                                <li><a href="<?= BASE_URL ?>/admin"><?= t('nav.admin') ?></a></li>
                            <?php endif; ?>
                            <li><a href="<?= BASE_URL ?>/deconnexion"><?= t('nav.logout') ?></a></li>
                        </ul>
                    </details>
                <?php endif; ?>
            </nav>
        </div>

        <!-- Toggle de langue : sur la barre, à gauche du hamburger (hors du menu) -->
        <?php $autre = Lang::actuelle() === 'fr' ? 'en' : 'fr'; ?>
        <a class="lang-toggle" href="<?= BASE_URL ?>/langue/<?= $autre ?>"
           title="<?= t('lang.switch') ?>" aria-label="<?= t('lang.switch') ?>">
            <span class="lang-opt<?= Lang::actuelle() === 'fr' ? ' is-active' : '' ?>">FR</span>
            <span class="lang-opt<?= Lang::actuelle() === 'en' ? ' is-active' : '' ?>">EN</span>
        </a>

        <button type="button" class="nav-toggle" id="nav-toggle"
                aria-label="<?= t('nav.menu') ?>" aria-expanded="false" aria-controls="nav-panel">
            <i class="ph-light ph-list nav-toggle-open"></i>
            <i class="ph-light ph-x nav-toggle-close"></i>
        </button>
    </header>

    <main class="site-main">
        <?= $content ?>
    </main>

    <footer class="site-footer">
        <small><?= t('footer.tagline') ?></small>
        <small class="site-footer-links">
            <a href="<?= BASE_URL ?>/mentions-legales"><?= t('footer.legal_notice') ?></a>
            <span class="site-footer-sep">·</span>
            <a href="<?= BASE_URL ?>/confidentialite"><?= t('footer.privacy') ?></a>
        </small>
    </footer>

    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
