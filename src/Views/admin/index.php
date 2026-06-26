<?php

declare(strict_types=1);

use App\Core\View;

/** @var array<int,array<string,mixed>> $activites */
/** @var string|null $filtre */

// Onglets de la barre latérale : [valeur du filtre, icône, libellé].
$onglets = [
    ['',            'ph-stack',         t('admin.filter_all')],
    ['membre',      'ph-user-plus',     t('admin.filter_membre')],
    ['vol',         'ph-airplane-tilt', t('admin.filter_vol')],
    ['lieu',        'ph-map-pin',       t('admin.filter_lieu')],
    ['commentaire', 'ph-chat-circle',   t('admin.filter_commentaire')],
    ['note',        'ph-star',          t('admin.filter_note')],
];

// Icône Phosphor par type d'événement.
$icones = [
    'membre'      => 'ph-user-plus',
    'vol'         => 'ph-airplane-tilt',
    'lieu'        => 'ph-map-pin',
    'commentaire' => 'ph-chat-circle',
    'note'        => 'ph-star',
];

// Lien vers la cible de l'événement selon son type.
$lienCible = static function (array $a): string {
    $ref = (int) $a['ref'];
    switch ($a['type']) {
        case 'vol':         return BASE_URL . '/vol/' . $ref;
        case 'membre':      return BASE_URL . '/pilote/' . $ref;
        case 'lieu':
        case 'commentaire':
        case 'note':        return BASE_URL . '/lieu/' . $ref;
        default:            return '#';
    }
};
?>
<section class="admin">
    <h1><i class="ph-light ph-gauge"></i> <?= t('page.admin.title') ?></h1>
    <h2 class="admin-heading"><?= t('admin.heading') ?></h2>

    <div class="admin-layout">
        <nav class="admin-nav">
            <?php foreach ($onglets as [$val, $icone, $libelle]): ?>
                <?php $actif = ($filtre ?? '') === $val; ?>
                <a class="<?= $actif ? 'is-active' : '' ?>"
                   href="<?= BASE_URL ?>/admin<?= $val !== '' ? '?filtre=' . $val : '' ?>"
                   <?= $actif ? 'aria-current="page"' : '' ?>>
                    <i class="ph-light <?= $icone ?>"></i> <?= View::e($libelle) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="admin-content">
            <?php if ($activites === []): ?>
                <p class="muted"><?= t('admin.empty') ?></p>
            <?php else: ?>
                <ul class="activite-list">
            <?php foreach ($activites as $a): ?>
                <?php
                $type    = (string) $a['type'];
                $pseudo  = $a['acteur'] !== null ? (string) $a['acteur'] : '—';
                $cible   = ($a['libelle'] ?? '') !== '' ? (string) $a['libelle'] : t('place.untitled');
                $href    = $lienCible($a);
                $pseudoH = $a['acteur_id'] !== null
                    ? '<a href="' . BASE_URL . '/pilote/' . (int) $a['acteur_id'] . '">' . View::e($pseudo) . '</a>'
                    : View::e($pseudo);
                ?>
                <li class="activite-item activite-<?= View::e($type) ?>">
                    <i class="ph-light <?= $icones[$type] ?? 'ph-circle' ?>"></i>
                    <span class="activite-corps">
                        <span class="activite-type"><?= t('admin.ev_' . $type) ?></span>
                        <span class="activite-detail">
                            <?php if ($type === 'membre'): ?>
                                <?= $pseudoH ?>
                            <?php elseif ($type === 'vol' || $type === 'lieu'): ?>
                                <a href="<?= $href ?>"><?= View::e($cible) ?></a>
                                <span class="muted">· <?= t('admin.by') ?> <?= $pseudoH ?></span>
                            <?php else: /* commentaire / note */ ?>
                                <span class="muted"><?= t('admin.by') ?></span> <?= $pseudoH ?>
                                <span class="muted"><?= t('admin.on') ?></span> <a href="<?= $href ?>"><?= View::e($cible) ?></a>
                            <?php endif; ?>
                        </span>
                    </span>
                    <time class="activite-date"><?= View::e(substr((string) $a['quand'], 0, 16)) ?></time>
                </li>
            <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</section>
