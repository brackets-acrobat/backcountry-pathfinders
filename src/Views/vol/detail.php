<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\View;

/** @var array<string,mixed> $vol */
/** @var array<int,array<string,mixed>> $atterrissages */
/** @var bool $estProprio */

$dep = trim((string) ($vol['depart_icao'] ?? ''));
$arr = trim((string) ($vol['arrivee_icao'] ?? ''));
$route = ($dep !== '' || $arr !== '')
    ? ($dep !== '' ? $dep : '????') . ' → ' . ($arr !== '' ? $arr : '????')
    : t('flight.no_route');
$csrf = View::e(Auth::jetonCsrf());
?>
<section class="vol-detail">
    <p class="vol-back"><a class="js-back" href="<?= BASE_URL ?>/pilote/<?= (int) $vol['id_utilisateur'] ?>">&larr; <?= t('flight.back') ?></a></p>

    <header class="vol-head">
        <h1><i class="ph-light ph-airplane-tilt"></i> <?= View::e($route) ?></h1>
        <ul class="vol-head-meta">
            <li><i class="ph-light ph-calendar-blank"></i> <?= View::e(substr((string) $vol['date_debut'], 0, 16)) ?></li>
            <?php if (($vol['aeronef'] ?? '') !== ''): ?>
                <li><i class="ph-light ph-airplane-tilt"></i> <?= View::e((string) $vol['aeronef']) ?></li>
            <?php endif; ?>
            <li><i class="ph-light ph-timer"></i> <?= t('flight.time') ?> <?= View::e(duree_vol($vol['duree_sec'] ?? null)) ?></li>
            <li><i class="ph-light ph-airplane-landing"></i> <?= (int) $vol['nb_atterrissages'] ?> <?= t('flight.landings') ?></li>
            <?php if (($vol['pseudo'] ?? '') !== ''): ?>
                <li><i class="ph-light ph-user"></i>
                    <a href="<?= BASE_URL ?>/pilote/<?= (int) $vol['id_utilisateur'] ?>"><?= View::e((string) $vol['pseudo']) ?></a>
                </li>
            <?php endif; ?>
        </ul>
    </header>

    <?php if ($atterrissages === []): ?>
        <p class="muted"><?= t('flight.no_landings') ?></p>
    <?php else: ?>
        <ol class="atterrissage-list">
            <?php foreach ($atterrissages as $i => $a): ?>
                <li class="atterrissage-item">
                    <div class="att-info">
                        <div class="att-title-row">
                            <span class="att-title">
                                <?= t('flight.landing_n', ['n' => (int) $i + 1]) ?>
                                <?php if (($a['lieu_nom'] ?? '') !== ''): ?>
                                    — <?= View::e((string) $a['lieu_nom']) ?>
                                <?php endif; ?>
                            </span>
                            <span class="att-head-right muted">
                                <span><i class="ph-light ph-map-pin"></i>
                                    <?= View::e(number_format((float) $a['latitude'], 5)) ?>,
                                    <?= View::e(number_format((float) $a['longitude'], 5)) ?></span>
                                <span><i class="ph-light ph-mountains"></i> <?= View::e((string) ($a['type_surface'] ?? '—')) ?></span>
                            </span>
                        </div>
                        <?php if ($a['vitesse_toucher_kt'] !== null || $a['distance_roulage_m'] !== null): ?>
                            <div class="att-row muted">
                                <?php if ($a['vitesse_toucher_kt'] !== null): ?>
                                    <i class="ph-light ph-gauge"></i> <?= t('flight.touch_speed') ?> <?= View::e(rtrim(rtrim(number_format((float) $a['vitesse_toucher_kt'], 1), '0'), '.')) ?> kt
                                <?php endif; ?>
                                <?php if ($a['vitesse_toucher_kt'] !== null && $a['distance_roulage_m'] !== null): ?> · <?php endif; ?>
                                <?php if ($a['distance_roulage_m'] !== null): ?>
                                    <i class="ph-light ph-ruler"></i> <?= t('flight.roll_dist') ?> <?= pieds($a['distance_roulage_m']) ?> ft
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <div class="att-actions">
                            <a class="btn btn-small" href="<?= BASE_URL ?>/lieu/<?= (int) $a['id_lieu'] ?>"><?= t('flight.see_place') ?></a>
                        </div>
                    </div>
                    <?php if (($a['capture'] ?? '') !== ''): ?>
                        <a class="att-photo" href="<?= BASE_URL ?>/uploads/<?= View::e((string) $a['capture']) ?>" target="_blank" rel="noopener">
                            <img src="<?= BASE_URL ?>/uploads/<?= View::e((string) $a['capture']) ?>" alt="<?= View::e(t('flight.photo_alt')) ?>" loading="lazy">
                        </a>
                    <?php else: ?>
                        <span class="att-photo att-nophoto"><i class="ph-light ph-image"></i></span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>

    <?php if ($estProprio): ?>
        <details class="vol-delete vol-delete-detail">
            <summary class="btn btn-danger"><?= t('myflights.delete') ?></summary>
            <form method="post" action="<?= BASE_URL ?>/mes-vols/supprimer" class="vol-delete-form">
                <input type="hidden" name="csrf" value="<?= $csrf ?>">
                <input type="hidden" name="id" value="<?= (int) $vol['id'] ?>">
                <p class="muted"><?= t('myflights.delete_warn') ?></p>
                <button type="submit" class="btn btn-danger"><?= t('myflights.delete_confirm') ?></button>
            </form>
        </details>
    <?php endif; ?>
</section>
