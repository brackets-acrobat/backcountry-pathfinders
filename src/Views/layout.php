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
    <?php
        // --- SEO / Open Graph ---
        // Description : override possible via $description passé par le contrôleur,
        // sinon description générale du site.
        $metaDescription = $description ?? t('meta.description');
        // Titre social complet (identique à la balise <title>).
        $metaTitre = $title . ' — Backcountry Pathfinders';
        // URL canonique : construite depuis la requête (inclut BASE_URL), sans la
        // chaîne de requête. On ne réutilise pas SITE_URL pour éviter de doubler BASE_URL.
        $metaScheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $metaHote   = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $metaChemin = strtok((string) ($_SERVER['REQUEST_URI'] ?? '/'), '?');
        $metaUrl    = $metaScheme . '://' . $metaHote . $metaChemin;
        // Image de partage : override possible via $ogImage, sinon le logo (absolu).
        $metaImage  = $ogImage ?? (SITE_URL . '/assets/img/logo.png');
        $metaLocale = Lang::actuelle() === 'fr' ? 'fr_FR' : 'en_US';
    ?>
    <meta name="description" content="<?= View::e($metaDescription) ?>">
    <link rel="canonical" href="<?= View::e($metaUrl) ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Backcountry Pathfinders">
    <meta property="og:title" content="<?= View::e($metaTitre) ?>">
    <meta property="og:description" content="<?= View::e($metaDescription) ?>">
    <meta property="og:url" content="<?= View::e($metaUrl) ?>">
    <meta property="og:image" content="<?= View::e($metaImage) ?>">
    <meta property="og:locale" content="<?= View::e($metaLocale) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= View::e($metaTitre) ?>">
    <meta name="twitter:description" content="<?= View::e($metaDescription) ?>">
    <meta name="twitter:image" content="<?= View::e($metaImage) ?>">
    <!-- Favicons (déposés à la racine public/) : chemins basés sur BASE_URL car le
         site peut être servi sous un sous-chemin (ex. /backcountry/). -->
    <link rel="icon" href="<?= BASE_URL ?>/favicon.ico" sizes="any">
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/favicon.svg">
    <link rel="icon" type="image/png" sizes="96x96" href="<?= BASE_URL ?>/favicon-96x96.png">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>/apple-touch-icon.png">
    <link rel="manifest" href="<?= BASE_URL ?>/site.webmanifest">
    <meta name="theme-color" content="#0b0c0e">
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
            <span class="site-footer-sep">·</span>
            <a href="#" class="js-cookie-manage"><?= t('cookie.manage') ?></a>
        </small>
    </footer>

    <!-- Bandeau de consentement aux cookies (affiché par app.js si aucun choix mémorisé) -->
    <div class="cookie-banner" id="cookie-banner" role="dialog" aria-live="polite"
         aria-label="<?= t('cookie.aria') ?>" hidden>
        <p class="cookie-banner-text">
            <?= t('cookie.message') ?>
            <a href="<?= BASE_URL ?>/confidentialite"><?= t('cookie.learn_more') ?></a>
        </p>
        <div class="cookie-banner-actions">
            <button type="button" class="btn btn-ghost js-cookie-refuse"><?= t('cookie.refuse') ?></button>
            <button type="button" class="btn js-cookie-accept"><?= t('cookie.accept') ?></button>
        </div>
    </div>

    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
