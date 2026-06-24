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
        <div class="header-left">
            <a class="brand" href="<?= BASE_URL ?>/">
                <img src="<?= asset('img/bpcom.png') ?>" alt="Backcountry Pathfinders">
            </a>
            <a href="<?= BASE_URL ?>/pilotes"><?= t('nav.pilots') ?></a>
            <a href="<?= BASE_URL ?>/"><?= t('nav.map') ?></a>
        </div>
        <nav class="site-nav">
            <?php if (!Auth::estConnecte()): ?>
                <a href="<?= BASE_URL ?>/connexion"><?= t('nav.login') ?></a>
                <a href="<?= BASE_URL ?>/inscription"><?= t('nav.register') ?></a>
            <?php endif; ?>
            <?php $autre = Lang::actuelle() === 'fr' ? 'en' : 'fr'; ?>
            <a class="lang-toggle" href="<?= BASE_URL ?>/langue/<?= $autre ?>"
               title="<?= t('lang.switch') ?>" aria-label="<?= t('lang.switch') ?>">
                <span class="lang-opt<?= Lang::actuelle() === 'fr' ? ' is-active' : '' ?>">FR</span>
                <span class="lang-opt<?= Lang::actuelle() === 'en' ? ' is-active' : '' ?>">EN</span>
            </a>
            <?php if (Auth::estConnecte()): ?>
                <?php $u = Auth::utilisateur(); $monAvatar = $u['avatar'] ?? null; ?>
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
                        <li><a href="<?= BASE_URL ?>/deconnexion"><?= t('nav.logout') ?></a></li>
                    </ul>
                </div>
            <?php endif; ?>
        </nav>
    </header>

    <main class="site-main">
        <?= $content ?>
    </main>

    <footer class="site-footer">
        <small><?= t('footer.tagline') ?></small>
    </footer>

    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
