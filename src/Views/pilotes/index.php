<?php

declare(strict_types=1);

use App\Core\View;

/** @var array<int,array<string,mixed>> $pilotes */
?>
<section class="pilotes">
    <h1><?= t('pilots.heading') ?></h1>

    <?php if ($pilotes === []): ?>
        <p class="muted"><?= t('pilots.empty') ?></p>
    <?php else: ?>
        <div class="pilote-table-wrap">
            <table class="pilote-table js-sortable">
                <thead>
                    <tr>
                        <th scope="col" data-sort-type="text"   aria-sort="ascending" tabindex="0" role="button" title="<?= t('pilots.sort_hint') ?>"><?= t('pilots.col_name') ?></th>
                        <th scope="col" data-sort-type="number" aria-sort="none" tabindex="0" role="button" title="<?= t('pilots.sort_hint') ?>"><?= t('pilots.col_hours') ?></th>
                        <th scope="col" data-sort-type="number" aria-sort="none" tabindex="0" role="button" title="<?= t('pilots.sort_hint') ?>"><?= t('pilots.col_flights') ?></th>
                        <th scope="col" data-sort-type="number" aria-sort="none" tabindex="0" role="button" title="<?= t('pilots.sort_hint') ?>"><?= t('pilots.col_places') ?></th>
                        <th scope="col" data-sort-type="number" aria-sort="none" tabindex="0" role="button" title="<?= t('pilots.sort_hint') ?>"><?= t('pilots.col_countries') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pilotes as $p): ?>
                        <?php
                        $totalSec = (int) ($p['total_sec'] ?? 0);
                        $nbVols   = (int) ($p['nb_vols'] ?? 0);
                        $nbLieux  = (int) ($p['nb_lieux'] ?? 0);
                        $nbPays   = (int) ($p['nb_pays'] ?? 0);
                        ?>
                        <tr>
                            <td class="col-name" data-sort-value="<?= View::e(strtolower((string) $p['pseudo'])) ?>">
                                <a class="pilote-ident" href="<?= BASE_URL ?>/pilote/<?= (int) $p['id'] ?>">
                                    <?php if (($p['avatar'] ?? '') !== ''): ?>
                                        <img class="avatar-mini" src="<?= BASE_URL ?>/uploads/<?= View::e((string) $p['avatar']) ?>" alt="">
                                    <?php else: ?>
                                        <span class="avatar-mini avatar-none"><i class="ph-light ph-user"></i></span>
                                    <?php endif; ?>
                                    <strong><?= View::e((string) $p['pseudo']) ?></strong>
                                </a>
                            </td>
                            <td class="col-num" data-sort-value="<?= $totalSec ?>"><i class="ph-light ph-timer"></i> <?= View::e(duree_vol($totalSec)) ?></td>
                            <td class="col-num" data-sort-value="<?= $nbVols ?>"><?= $nbVols ?></td>
                            <td class="col-num" data-sort-value="<?= $nbLieux ?>"><?= $nbLieux ?></td>
                            <td class="col-num" data-sort-value="<?= $nbPays ?>"><?= $nbPays ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
