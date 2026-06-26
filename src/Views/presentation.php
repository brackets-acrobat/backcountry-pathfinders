<?php declare(strict_types=1); ?>

<section class="presentation">
    <h1 class="presentation-title"><?= t('presentation.title') ?></h1>

    <div class="presentation-body">

        <h2><?= t('presentation.s1_heading') ?></h2>
        <p><?= t('presentation.s1_p1') ?></p>
        <p><?= t('presentation.s1_p2') ?></p>

        <h2><?= t('presentation.s2_heading') ?></h2>
        <p><?= t('presentation.s2_p1') ?></p>
        <p><?= t('presentation.s2_intro') ?></p>
        <ul class="presentation-list">
            <li><i class="ph-light ph-mountains"></i> <?= t('presentation.s2_li1') ?></li>
            <li><i class="ph-light ph-chart-line"></i> <?= t('presentation.s2_li2') ?></li>
            <li><i class="ph-light ph-ruler"></i> <?= t('presentation.s2_li3') ?></li>
            <li><i class="ph-light ph-camera"></i> <?= t('presentation.s2_li4') ?></li>
        </ul>

        <h2><?= t('presentation.s3_heading') ?></h2>
        <p><?= t('presentation.s3_p1') ?></p>
        <p><?= t('presentation.s3_p2') ?></p>

        <h2><?= t('presentation.s4_heading') ?></h2>
        <p><?= t('presentation.s4_p1') ?></p>
        <p><?= t('presentation.s4_p2') ?></p>

        <h2><?= t('presentation.s5_heading') ?></h2>
        <blockquote class="presentation-quote">
            <p><?= t('presentation.s5_q1') ?></p>
            <p><?= t('presentation.s5_q2') ?></p>
            <p><?= t('presentation.s5_q3') ?></p>
        </blockquote>
        <p class="presentation-punch"><?= t('presentation.s5_punch') ?></p>

        <p class="presentation-cta">
            <a class="btn" href="<?= BASE_URL ?>/carte">
                <i class="ph-light ph-map-trifold"></i> <?= t('presentation.cta_map') ?>
            </a>
        </p>

    </div>
</section>
