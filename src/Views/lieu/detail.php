<?php

declare(strict_types=1);

use App\Core\View;

/**
 * @var array<string,mixed>             $lieu
 * @var array<string,mixed>             $agregats
 * @var array<int,array<string,mixed>>  $releves
 * @var array<int,array<string,mixed>>  $commentaires
 */

$nom = ($lieu['nom'] ?? '') !== '' ? (string) $lieu['nom'] : t('place.untitled');
$lat = (float) $lieu['latitude'];
$lon = (float) $lieu['longitude'];

/** Rendu compact d'une note moyenne en étoiles. */
$etoiles = static function (?float $note): string {
    if ($note === null) {
        return '<span class="muted">—</span>';
    }
    $pleines = (int) round($note);
    $s = '';
    for ($i = 1; $i <= 5; $i++) {
        $s .= $i <= $pleines
            ? '<i class="ph-fill ph-star"></i>'
            : '<i class="ph-light ph-star star-empty"></i>';
    }
    return '<span class="stars">' . $s . '</span> <span class="muted">(' . number_format($note, 1) . ')</span>';
};
?>

<link rel="stylesheet"
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin="">

<section class="place">
    <p class="place-back"><a href="<?= BASE_URL ?>/"><i class="ph-bold ph-arrow-left"></i> <?= t('place.back_to_map') ?></a></p>

    <header class="place-head">
        <h1><?= View::e($nom) ?></h1>
        <p class="place-coords muted">
            <?= number_format($lat, 5) ?>, <?= number_format($lon, 5) ?>
            <?php if (($lieu['altitude_m'] ?? null) !== null): ?>
                · <?= (int) $lieu['altitude_m'] ?> m
            <?php endif; ?>
        </p>
    </header>

    <div class="place-stats">
        <div class="stat">
            <span class="stat-label"><?= t('map.rating') ?></span>
            <span class="stat-value"><?= $etoiles($agregats['note_moyenne']) ?></span>
        </div>
        <div class="stat">
            <span class="stat-label"><?= t('map.difficulty') ?></span>
            <span class="stat-value">
                <?= $agregats['difficulte_moyenne'] !== null
                    ? View::e(number_format($agregats['difficulte_moyenne'], 1)) . '/5'
                    : '<span class="muted">—</span>' ?>
            </span>
        </div>
        <div class="stat">
            <span class="stat-label"><?= t('map.surveys') ?></span>
            <span class="stat-value"><?= (int) $agregats['nb_releves'] ?></span>
        </div>
    </div>

    <div id="place-map" class="place-map"
         data-lat="<?= View::e((string) $lat) ?>" data-lon="<?= View::e((string) $lon) ?>"></div>

    <h2><?= t('place.surveys_heading') ?></h2>
    <?php if ($releves === []): ?>
        <p class="muted"><?= t('place.no_surveys') ?></p>
    <?php else: ?>
        <ul class="releve-list">
            <?php foreach ($releves as $r): ?>
                <li class="releve">
                    <div class="releve-head">
                        <span class="releve-date"><?= View::e((string) $r['date_releve']) ?></span>
                        <?php if (($r['pseudo'] ?? null) !== null): ?>
                            <span class="releve-auteur muted">· <?= View::e((string) $r['pseudo']) ?></span>
                        <?php endif; ?>
                    </div>
                    <dl class="releve-grid">
                        <?php
                        $champs = [
                            'type_surface'     => t('survey.surface'),
                            'etat_surface'     => t('survey.condition'),
                            'friction'         => t('survey.friction'),
                            'longueur_utile_m' => t('survey.usable_length'),
                            'pente_max_pct'    => t('survey.max_slope'),
                            'denivele_m'       => t('survey.elevation_gain'),
                            'aeronef'          => t('survey.aircraft'),
                        ];
                        $unites = ['longueur_utile_m' => ' m', 'pente_max_pct' => ' %', 'denivele_m' => ' m'];
                        foreach ($champs as $cle => $label):
                            $val = $r[$cle] ?? null;
                            if ($val === null || $val === '') { continue; }
                        ?>
                            <dt><?= View::e($label) ?></dt>
                            <dd><?= View::e((string) $val) . ($unites[$cle] ?? '') ?></dd>
                        <?php endforeach; ?>
                    </dl>
                    <?php if (!empty($r['profil_relief'])): ?>
                        <div class="releve-profil" data-profil='<?= View::e((string) $r['profil_relief']) ?>'
                             aria-label="<?= t('survey.relief_profile') ?>"></div>
                    <?php endif; ?>
                    <?php if (($r['commentaire'] ?? '') !== ''): ?>
                        <p class="releve-note"><?= View::e((string) $r['commentaire']) ?></p>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <h2><?= t('place.comments_heading') ?></h2>
    <?php if ($commentaires === []): ?>
        <p class="muted"><?= t('place.no_comments') ?></p>
    <?php else: ?>
        <ul class="commentaire-list">
            <?php foreach ($commentaires as $c): ?>
                <li class="commentaire">
                    <div class="commentaire-head muted">
                        <strong><?= View::e((string) ($c['pseudo'] ?? t('place.deleted_user'))) ?></strong>
                        · <?= View::e((string) $c['date_creation']) ?>
                    </div>
                    <p class="commentaire-texte"><?= nl2br(View::e((string) $c['texte'])) ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>
<script src="<?= asset('js/lieu.js') ?>"></script>
