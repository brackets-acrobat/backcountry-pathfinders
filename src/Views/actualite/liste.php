<?php

declare(strict_types=1);

use App\Core\View;

/** @var array<int,array<string,mixed>> $actualites */
/** @var int $page */
/** @var int $nbPages */
?>
<section class="news-page">
    <h1 class="news-page-title"><?= t('news.page_title') ?></h1>

    <?php if ($actualites === []): ?>
        <p class="muted"><?= t('news.empty') ?></p>
    <?php else: ?>
        <ul class="home-news-list">
            <?php foreach ($actualites as $a): ?>
                <?php
                $texte = html_entity_decode(strip_tags((string) $a['contenu']), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $texte = trim(preg_replace('/\s+/u', ' ', $texte) ?? '');
                $extrait = mb_strlen($texte) > 220 ? mb_substr($texte, 0, 220) . '…' : $texte;
                ?>
                <li class="home-news-item">
                    <span class="home-news-date"><?= View::e(substr((string) $a['date_creation'], 0, 10)) ?></span>
                    <h2 class="home-news-item-title">
                        <a href="<?= BASE_URL ?>/actualite/<?= (int) $a['id'] ?>"><?= View::e((string) $a['titre']) ?></a>
                    </h2>
                    <?php if ($extrait !== ''): ?>
                        <p class="home-news-extrait"><?= View::e($extrait) ?></p>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php if ($nbPages > 1): ?>
            <nav class="pager" aria-label="<?= t('news.page_title') ?>">
                <?php if ($page > 1): ?>
                    <a class="pager-link" href="<?= BASE_URL ?>/actualites?page=<?= $page - 1 ?>">
                        <i class="ph-light ph-arrow-left"></i> <?= t('pager.prev') ?>
                    </a>
                <?php else: ?>
                    <span class="pager-link pager-disabled">
                        <i class="ph-light ph-arrow-left"></i> <?= t('pager.prev') ?>
                    </span>
                <?php endif; ?>

                <span class="pager-status"><?= sprintf(t('pager.page'), $page, $nbPages) ?></span>

                <?php if ($page < $nbPages): ?>
                    <a class="pager-link" href="<?= BASE_URL ?>/actualites?page=<?= $page + 1 ?>">
                        <?= t('pager.next') ?> <i class="ph-light ph-arrow-right"></i>
                    </a>
                <?php else: ?>
                    <span class="pager-link pager-disabled">
                        <?= t('pager.next') ?> <i class="ph-light ph-arrow-right"></i>
                    </span>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</section>
