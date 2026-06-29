<?php

declare(strict_types=1);

use App\Core\View;

/** @var array<string,mixed> $actualite */

$auteur  = trim((string) ($actualite['auteur'] ?? ''));
$dateMaj = $actualite['date_maj'] ?? null;
?>
<article class="actu-article">
    <p class="actu-back">
        <a href="<?= BASE_URL ?>/actualites"
           onclick="if (history.length > 1) { history.back(); return false; }"><i class="ph-light ph-arrow-left"></i> <?= t('actu.back') ?></a>
    </p>

    <h1 class="actu-article-title"><?= View::e((string) $actualite['titre']) ?></h1>

    <p class="actu-article-meta muted">
        <i class="ph-light ph-calendar-blank"></i>
        <?= t('actu.published_on') ?> <?= View::e(substr((string) $actualite['date_creation'], 0, 10)) ?>
        <?php if ($auteur !== ''): ?>
            · <?= t('actu.by') ?> <?= View::e($auteur) ?>
        <?php endif; ?>
        <?php if ($dateMaj !== null): ?>
            · <?= t('actu.updated_on') ?> <?= View::e(substr((string) $dateMaj, 0, 10)) ?>
        <?php endif; ?>
    </p>

    <!-- Contenu déjà assaini à l'enregistrement (App\Core\HtmlSanitizer). -->
    <div class="actu-article-body">
        <?= $actualite['contenu'] ?>
    </div>
</article>
