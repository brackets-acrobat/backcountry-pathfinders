<?php

declare(strict_types=1);

use App\Core\View;

/** @var array<int,array<string,mixed>> $actualites */
?>

<section class="hero hero-accueil">
    <!-- Bloc « lettre + photos » : c'est lui qui sert de repère aux images en
         position absolue. Les actualités sont SORTIES de ce bloc (placées en
         dessous) pour que leur hauteur ne décale plus les photos. -->
    <div class="home-hero-main">
        <img class="home-illustration"
             src="<?= asset('img/old_photo_left.webp') ?>"
             alt="<?= t('home.title') ?>">

        <div class="home-letter">
            <p class="home-letter-hi"><?= t('home.letter_hi') ?></p>
            <p><?= t('home.letter_p1') ?></p>
            <p><?= t('home.letter_p2') ?></p>
            <p class="home-letter-bye"><?= t('home.letter_bye') ?></p>
            <p class="home-letter-sign">— Jim « Ridge » Vance</p>
            <p class="home-letter-cta">
                <a href="<?= BASE_URL ?>/presentation"><?= t('home.presentation_link') ?></a>
            </p>
        </div>

        <img class="home-polaroid"
             src="<?= asset('img/polaroid.webp') ?>"
             alt="">
    </div>

    <?php if (!empty($actualites)): ?>
        <div class="home-news">
            <h2 class="home-news-title"><?= t('home.news_heading') ?></h2>
            <ul class="home-news-list">
                <?php foreach ($actualites as $a): ?>
                    <?php
                    $texte = html_entity_decode(strip_tags((string) $a['contenu']), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $texte = trim(preg_replace('/\s+/u', ' ', $texte) ?? '');
                    $extrait = mb_strlen($texte) > 160 ? mb_substr($texte, 0, 160) . '…' : $texte;
                    ?>
                    <li class="home-news-item">
                        <span class="home-news-date"><?= View::e(substr((string) $a['date_creation'], 0, 10)) ?></span>
                        <h3 class="home-news-item-title">
                            <a href="<?= BASE_URL ?>/actualite/<?= (int) $a['id'] ?>"><?= View::e((string) $a['titre']) ?></a>
                        </h3>
                        <?php if ($extrait !== ''): ?>
                            <p class="home-news-extrait"><?= View::e($extrait) ?></p>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
</section>
