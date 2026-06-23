<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\View;

/** @var array<int,array<string,mixed>> $lieux */
/** @var array{type:string,msgs:array<int,string>}|null $flash */

$csrf = View::e(Auth::jetonCsrf());
?>
<section class="mes-lieux">
    <h1><?= t('myplaces.heading') ?></h1>

    <?php if (($flash ?? null) !== null): ?>
        <div class="alert<?= $flash['type'] === 'ok' ? ' alert-success' : '' ?>">
            <ul>
                <?php foreach ($flash['msgs'] as $m): ?>
                    <li><?= View::e(t($m)) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

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
                    <details class="lieu-rename">
                        <summary class="lieu-rename-toggle" title="<?= View::e(t('myplaces.rename')) ?>"
                                 aria-label="<?= View::e(t('myplaces.rename')) ?>">
                            <i class="ph-light ph-pencil-simple"></i>
                        </summary>
                        <form method="post" action="<?= BASE_URL ?>/mes-lieux/renommer" class="lieu-rename-form">
                            <input type="hidden" name="csrf" value="<?= $csrf ?>">
                            <input type="hidden" name="id" value="<?= (int) $l['id'] ?>">
                            <input type="text" name="nom" maxlength="120"
                                   value="<?= View::e((string) ($l['nom'] ?? '')) ?>"
                                   placeholder="<?= View::e(t('myplaces.rename_placeholder')) ?>">
                            <button type="submit" class="btn"><?= t('myplaces.rename_save') ?></button>
                        </form>
                    </details>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
