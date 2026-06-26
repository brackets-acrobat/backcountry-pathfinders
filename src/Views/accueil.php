<?php declare(strict_types=1); ?>

<section class="hero hero-accueil">
    <img class="home-illustration"
         src="<?= asset('img/old_photo_left.webp') ?>"
         alt="<?= t('home.title') ?>">

    <div class="home-letter">
        <p class="home-letter-hi"><?= t('home.letter_hi') ?></p>
        <p><?= t('home.letter_p1') ?></p>
        <p><?= t('home.letter_p2') ?></p>
        <p class="home-letter-bye"><?= t('home.letter_bye') ?></p>
        <p class="home-letter-sign">— Jim « Ridge » Vance</p>
    </div>

    <img class="home-polaroid"
         src="<?= asset('img/polaroid.webp') ?>"
         alt="">
</section>
