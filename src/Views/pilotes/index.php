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
        <ul class="pilote-list">
            <?php foreach ($pilotes as $p): ?>
                <li class="pilote-item">
                    <span class="pilote-ident">
                        <?php if (($p['avatar'] ?? '') !== ''): ?>
                            <img class="avatar-mini" src="<?= BASE_URL ?>/uploads/<?= View::e((string) $p['avatar']) ?>" alt="">
                        <?php else: ?>
                            <span class="avatar-mini avatar-none"><i class="ph-light ph-user"></i></span>
                        <?php endif; ?>
                        <strong><?= View::e((string) $p['pseudo']) ?></strong>
                    </span>
                    <span class="pilote-meta muted">
                        <?= (int) $p['nb_lieux'] ?> <?= t('pilots.places') ?>
                        · <?= (int) $p['nb_releves'] ?> <?= t('pilots.surveys') ?>
                        · <?= t('pilots.since') ?> <?= View::e(substr((string) $p['date_inscription'], 0, 10)) ?>
                    </span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
