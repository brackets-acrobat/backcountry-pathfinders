<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\View;

/** @var array<int,array<string,mixed>> $vols */
/** @var array{type:string,msgs:array<int,string>}|null $flash */

$csrf = View::e(Auth::jetonCsrf());
?>
<section class="mes-vols">
    <h1><?= t('myflights.heading') ?></h1>

    <?php if (($flash ?? null) !== null): ?>
        <div class="alert<?= $flash['type'] === 'ok' ? ' alert-success' : '' ?>">
            <ul>
                <?php foreach ($flash['msgs'] as $m): ?>
                    <li><?= View::e(t($m)) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($vols === []): ?>
        <p class="muted"><?= t('myflights.empty') ?></p>
    <?php else: ?>
        <ul class="vol-list">
            <?php foreach ($vols as $v): ?>
                <?php
                $dep = trim((string) ($v['depart_icao'] ?? ''));
                $arr = trim((string) ($v['arrivee_icao'] ?? ''));
                $route = ($dep !== '' || $arr !== '')
                    ? ($dep !== '' ? $dep : '????') . ' → ' . ($arr !== '' ? $arr : '????')
                    : t('flight.no_route');
                ?>
                <li class="vol-item">
                    <a class="vol-item-main" href="<?= BASE_URL ?>/vol/<?= (int) $v['id'] ?>">
                        <span class="vol-item-route">
                            <i class="ph-light ph-airplane-tilt"></i> <?= View::e($route) ?>
                        </span>
                        <span class="vol-item-date muted">
                            <?= View::e(substr((string) $v['date_debut'], 0, 16)) ?>
                        </span>
                    </a>
                    <span class="vol-item-meta muted">
                        <?php if (($v['aeronef'] ?? '') !== ''): ?>
                            <?= View::e((string) $v['aeronef']) ?> ·
                        <?php endif; ?>
                        <i class="ph-light ph-timer"></i> <?= View::e(duree_vol($v['duree_sec'] ?? null)) ?>
                        · <i class="ph-light ph-airplane-landing"></i>
                        <?= (int) $v['nb_atterrissages'] ?> <?= t('flight.landings') ?>
                    </span>
                    <details class="vol-delete">
                        <summary class="vol-delete-toggle" title="<?= View::e(t('myflights.delete')) ?>"
                                 aria-label="<?= View::e(t('myflights.delete')) ?>">
                            <i class="ph-light ph-trash"></i>
                        </summary>
                        <form method="post" action="<?= BASE_URL ?>/mes-vols/supprimer" class="vol-delete-form">
                            <input type="hidden" name="csrf" value="<?= $csrf ?>">
                            <input type="hidden" name="id" value="<?= (int) $v['id'] ?>">
                            <p class="muted"><?= t('myflights.delete_warn') ?></p>
                            <button type="submit" class="btn btn-danger"><?= t('myflights.delete_confirm') ?></button>
                        </form>
                    </details>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
