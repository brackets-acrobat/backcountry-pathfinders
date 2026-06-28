<?php

declare(strict_types=1);

use App\Core\View;

/** @var array<string,mixed> $pilote */
/** @var array{nb_vols:int, total_sec:int, nb_lieux:int, nb_pays:int} $stats */
/** @var array<int,array<string,mixed>> $vols */
?>
<section class="pilote-profil">
    <p class="vol-back"><a href="<?= BASE_URL ?>/pilotes">&larr; <?= t('pilots.back') ?></a></p>

    <header class="profil-head">
        <?php if (($pilote['avatar'] ?? '') !== ''): ?>
            <img class="avatar-mini avatar-profil" src="<?= BASE_URL ?>/uploads/<?= View::e((string) $pilote['avatar']) ?>" alt="">
        <?php else: ?>
            <span class="avatar-mini avatar-profil avatar-none"><i class="ph-light ph-user"></i></span>
        <?php endif; ?>
        <div>
            <h1><?= View::e((string) $pilote['pseudo']) ?></h1>
            <p class="profil-stats muted">
                <i class="ph-light ph-airplane-tilt"></i> <?= (int) $stats['nb_vols'] ?> <?= t('pilots.flights') ?>
                · <i class="ph-light ph-timer"></i> <?= View::e(duree_vol($stats['total_sec'])) ?>
                · <i class="ph-light ph-map-pin"></i> <?= (int) ($stats['nb_lieux'] ?? 0) ?> <?= t('pilots.places') ?>
                · <i class="ph-light ph-globe-hemisphere-west"></i> <?= (int) ($stats['nb_pays'] ?? 0) ?> <?= t('pilots.countries') ?>
                · <?= t('pilots.since') ?> <?= View::e(substr((string) $pilote['date_inscription'], 0, 10)) ?>
            </p>
        </div>

        <aside class="profil-awards">
            <h2 class="profil-awards-title"><i class="ph-light ph-medal"></i> <?= t('profil.awards') ?></h2>
            <div class="profil-awards-body">
                <?php include __DIR__ . '/../partials/ecussons.php'; ?>
            </div>
        </aside>
    </header>

    <h2><?= t('profil.flights_heading') ?></h2>

    <?php if ($vols === []): ?>
        <p class="muted"><?= t('profil.no_flights') ?></p>
    <?php else: ?>
        <ul class="vol-list">
            <?php foreach ($vols as $v): ?>
                <?php
                // Nom du vol comme à la sauvegarde d'un plan : « ICAO dép - ICAO arr ».
                $dep = trim((string) ($v['depart_icao'] ?? ''));
                $arr = trim((string) ($v['arrivee_icao'] ?? ''));
                $route = ($dep !== '' || $arr !== '')
                    ? ($dep !== '' ? $dep : '????') . ' - ' . ($arr !== '' ? $arr : '????')
                    : t('flight.no_route');
                ?>
                <li class="vol-item">
                    <a class="vol-item-line" href="<?= BASE_URL ?>/vol/<?= (int) $v['id'] ?>">
                        <span class="vol-item-name"><i class="ph-light ph-airplane-tilt"></i> <?= View::e($route) ?></span>
                        <span class="vol-item-infos muted">
                            <span><i class="ph-light ph-calendar-blank"></i> <?= View::e(substr((string) $v['date_debut'], 0, 16)) ?></span>
                            <span><i class="ph-light ph-timer"></i> <?= View::e(duree_vol($v['duree_sec'] ?? null)) ?></span>
                            <span><i class="ph-light ph-airplane-landing"></i> <?= (int) $v['nb_atterrissages'] ?> <?= t('flight.landings') ?></span>
                        </span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
