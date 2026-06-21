<?php

declare(strict_types=1);

use App\Core\View;

/** @var array<int,array<string,mixed>> $lieux */
?>
<section class="mes-lieux">
    <h1><?= t('myplaces.heading') ?></h1>

    <?php if ($lieux === []): ?>
        <p class="muted"><?= t('myplaces.empty') ?></p>
    <?php else: ?>
        <ul class="lieu-list">
            <?php foreach ($lieux as $l): ?>
                <li class="lieu-item">
                    <a class="lieu-item-main" href="<?= BASE_URL ?>/lieu/<?= (int) $l['id'] ?>">
                        <span class="lieu-item-nom">
                            <i class="ph-light ph-mountains"></i>
                            <?= $l['nom'] !== null && $l['nom'] !== ''
                                ? View::e((string) $l['nom'])
                                : t('place.untitled') ?>
                        </span>
                        <span class="lieu-item-coords">
                            <?= View::e(number_format((float) $l['latitude'], 5)) ?>,
                            <?= View::e(number_format((float) $l['longitude'], 5)) ?>
                            <?php if ($l['altitude_m'] !== null): ?>
                                · <?= pieds($l['altitude_m']) ?> ft
                            <?php endif; ?>
                        </span>
                    </a>
                    <span class="lieu-item-meta muted">
                        <?= (int) $l['nb_releves'] ?> <?= t('myplaces.surveys') ?>
                        · <?= t('myplaces.last_visit') ?> <?= View::e(substr((string) $l['derniere_visite'], 0, 10)) ?>
                    </span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
