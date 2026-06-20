<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\View;

/**
 * @var array<string,mixed>             $lieu
 * @var array<string,mixed>             $agregats
 * @var array<int,array<string,mixed>>  $releves
 * @var array<int,array<string,mixed>>  $commentaires
 * @var array{note:?int,difficulte:?int}|null $maNote
 * @var array{type:string,cle:string}|null    $flash
 */

$nom = ($lieu['nom'] ?? '') !== '' ? (string) $lieu['nom'] : t('place.untitled');
$lat = (float) $lieu['latitude'];
$lon = (float) $lieu['longitude'];
$idLieu = (int) $lieu['id'];
$connecte = Auth::estConnecte();

/**
 * Widget d'étoiles cliquables (radios 1..5, CSS pur — pas de JS) ; valeur
 * courante pré-cochée. Radios listés de 5 à 1 pour que le remplissage CSS
 * (sélecteur ~) colore l'étoile choisie et celles à sa gauche.
 */
$starRating = static function (string $name, ?int $courant) : string {
    $html = '<span class="star-rating" role="radiogroup">';
    for ($i = 5; $i >= 1; $i--) {
        $id  = 'r-' . View::e($name) . '-' . $i;
        $chk = ($courant === $i) ? ' checked' : '';
        $html .= '<input type="radio" name="' . View::e($name) . '" value="' . $i . '" id="' . $id . '"' . $chk . '>'
              . '<label for="' . $id . '" title="' . $i . '/5" aria-label="' . $i . '/5">'
              . '<i class="ph-fill ph-star"></i></label>';
    }
    return $html . '</span>';
};

/**
 * Rendu d'une moyenne en étoiles FRACTIONNAIRES (façon Amazon) : 5 étoiles
 * grises en fond, surmontées des mêmes 5 étoiles vertes clippées à la largeur
 * exacte (moyenne/5). Ex. 3.72 → 74,4 % de largeur verte → 3 pleines + 72 % de
 * la 4e. Suivi de la valeur numérique entre parenthèses.
 */
$etoiles = static function (?float $note, string $variante = '', int $decimales = 1): string {
    if ($note === null) {
        return '<span class="muted">—</span>';
    }
    $note = max(0.0, min(5.0, $note));
    $pct  = number_format($note / 5 * 100, 1, '.', '');          // ex. "74.4"
    $cinq = str_repeat('<i class="ph-fill ph-star"></i>', 5);
    $valeur = View::e(number_format($note, $decimales));
    $classe = 'stars-avg' . ($variante !== '' ? ' ' . $variante : '');

    return '<span class="' . $classe . '" role="img" aria-label="' . $valeur . '/5">'
         . '<span class="stars-base">' . $cinq . '</span>'
         . '<span class="stars-fill" style="width:' . $pct . '%" aria-hidden="true">' . $cinq . '</span>'
         . '</span> <span class="muted">(' . $valeur . ')</span>';
};

/** Palier de couleur de la difficulté moyenne : vert ≤2,33 / orange ≤3,67 / rouge au-delà. */
$couleurDifficulte = static function (float $d): string {
    if ($d <= 2.33) { return ''; }            // vert (couleur par défaut)
    if ($d <= 3.67) { return 'is-orange'; }
    return 'is-red';
};
?>

<link rel="stylesheet"
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin="">

<section class="place">
    <p class="place-back"><a href="<?= BASE_URL ?>/"><i class="ph-bold ph-arrow-left"></i> <?= t('place.back_to_map') ?></a></p>

    <?php if ($flash !== null): ?>
        <div class="alert<?= ($flash['type'] ?? '') === 'success' ? ' alert-success' : '' ?>">
            <?= View::e(t($flash['cle'])) ?>
        </div>
    <?php endif; ?>

    <header class="place-head">
        <h1><?= View::e($nom) ?></h1>
        <p class="place-coords muted">
            <?= number_format($lat, 5) ?>, <?= number_format($lon, 5) ?>
            <?php if (($lieu['altitude_m'] ?? null) !== null): ?>
                · <?= pieds($lieu['altitude_m']) ?> ft
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
            <span class="stat-value"><?php
                $diffMoy = $agregats['difficulte_moyenne'];
                echo $etoiles($diffMoy, $diffMoy !== null ? $couleurDifficulte($diffMoy) : '', 2);
            ?></span>
        </div>
        <div class="stat">
            <span class="stat-label"><?= t('map.surveys') ?></span>
            <span class="stat-value"><?= (int) $agregats['nb_releves'] ?></span>
        </div>
    </div>

    <div id="place-map" class="place-map"
         data-lat="<?= View::e((string) $lat) ?>" data-lon="<?= View::e((string) $lon) ?>"></div>

    <?php if ($connecte): ?>
        <section id="avis" class="contribution">
            <h2><?= t('place.your_review') ?></h2>
            <form class="form-rating" method="post" action="<?= BASE_URL ?>/lieu/<?= $idLieu ?>/note">
                <input type="hidden" name="csrf" value="<?= View::e(Auth::jetonCsrf()) ?>">
                <span class="rating-field">
                    <span class="rating-label"><?= t('map.rating') ?></span>
                    <?= $starRating('note', $maNote['note'] ?? null) ?>
                </span>
                <span class="rating-field">
                    <span class="rating-label"><?= t('map.difficulty') ?></span>
                    <?= $starRating('difficulte', $maNote['difficulte'] ?? null) ?>
                </span>
                <button type="submit" class="btn"><?= t('place.save_rating') ?></button>
            </form>
            <p class="hint muted"><?= t('place.rating_hint') ?></p>
        </section>
    <?php else: ?>
        <p class="login-prompt muted">
            <a href="<?= BASE_URL ?>/connexion"><?= t('place.login_to_contribute') ?></a>
        </p>
    <?php endif; ?>

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
                            'cap_moyen_deg'    => t('survey.heading'),
                            'aeronef'          => t('survey.aircraft'),
                        ];
                        // Distances en mètres (colonnes *_m) affichées en pieds ; pente en % ; cap en °.
                        $enPieds = ['longueur_utile_m', 'denivele_m'];
                        $unites  = ['pente_max_pct' => ' %', 'cap_moyen_deg' => ' °'];
                        foreach ($champs as $cle => $label):
                            $val = $r[$cle] ?? null;
                            if ($val === null || $val === '') { continue; }
                            if (in_array($cle, $enPieds, true)) {
                                $affichage = pieds($val) . ' ft';
                            } else {
                                $affichage = (string) $val . ($unites[$cle] ?? '');
                            }
                        ?>
                            <dt><?= View::e($label) ?></dt>
                            <dd><?= View::e($affichage) ?></dd>
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

    <h2 id="commentaires"><?= t('place.comments_heading') ?></h2>

    <?php if ($connecte): ?>
        <form class="form-comment" method="post" action="<?= BASE_URL ?>/lieu/<?= $idLieu ?>/commentaire">
            <input type="hidden" name="csrf" value="<?= View::e(Auth::jetonCsrf()) ?>">
            <label class="sr-only" for="comment-texte"><?= t('place.add_comment') ?></label>
            <textarea id="comment-texte" name="texte" rows="3" maxlength="2000"
                      placeholder="<?= View::e(t('place.comment_placeholder')) ?>" required></textarea>
            <?= turnstile_widget() ?>
            <button type="submit" class="btn"><?= t('place.comment_submit') ?></button>
        </form>
    <?php endif; ?>

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
