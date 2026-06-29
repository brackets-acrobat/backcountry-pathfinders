<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\View;

/** @var string $section */
/** @var string $newsVue */
/** @var array<string,string>|null $actuOld */
/** @var array<string,mixed>|null $actuEdit */
/** @var array<int,array<string,mixed>> $actuListe */
/** @var array<int,array<string,mixed>> $activites */
/** @var string|null $filtre */
/** @var array<int,bool> $hasIp */
/** @var array<int,int> $nbContribs */
/** @var array<string,mixed>|null $flash */

// Onglets d'administration (barre du haut) : [section, icône, libellé].
$tabs = [
    ['activite', 'ph-pulse',     t('admin.tab_activite')],
    ['news',     'ph-newspaper', t('admin.tab_news')],
];

// Filtres de la barre latérale (onglet « Activité ») : [valeur du filtre, icône, libellé].
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

$moiId = Auth::id();
?>

<section class="admin">
    <h1><i class="ph-light ph-gauge"></i> <?= t('page.admin.title') ?></h1>

    <?php if ($flash !== null): ?>
        <div class="alert <?= $flash['type'] === 'ok' ? 'alert-ok' : 'alert-err' ?>">
            <?= View::e(t($flash['cle'])) ?>
        </div>
    <?php endif; ?>

    <nav class="admin-tabs" aria-label="<?= View::e(t('page.admin.title')) ?>">
        <?php foreach ($tabs as [$sec, $icone, $libelle]): ?>
            <?php $actif = $section === $sec; ?>
            <a class="admin-tab <?= $actif ? 'is-active' : '' ?>"
               href="<?= BASE_URL ?>/admin<?= $sec !== 'activite' ? '?section=' . $sec : '' ?>"
               <?= $actif ? 'aria-current="page"' : '' ?>>
                <i class="ph-light <?= $icone ?>"></i> <?= View::e($libelle) ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <?php if ($section === 'news'): ?>

        <?php
        // Menu vertical de l'onglet News : [valeur de la vue, icône, libellé].
        $newsMenu = [
            ['nouvelle', 'ph-plus-circle',  t('admin.news_new')],
            ['gerer',    'ph-list-bullets', t('admin.news_manage')],
        ];
        ?>
        <div class="admin-layout">
            <nav class="admin-nav">
                <?php foreach ($newsMenu as [$vue, $icone, $libelle]): ?>
                    <?php $actif = $newsVue === $vue; ?>
                    <a class="<?= $actif ? 'is-active' : '' ?>"
                       href="<?= BASE_URL ?>/admin?section=news<?= $vue !== 'nouvelle' ? '&vue=' . $vue : '' ?>"
                       <?= $actif ? 'aria-current="page"' : '' ?>>
                        <i class="ph-light <?= $icone ?>"></i> <?= View::e($libelle) ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="admin-content">
                <?php if ($newsVue === 'nouvelle'): ?>

                    <?php
                    // Priorité : reprise de saisie (erreur) > actualité chargée pour édition.
                    $editId     = (int) ($actuOld['id'] ?? ($actuEdit['id'] ?? 0));
                    $oldTitre   = (string) ($actuOld['titre']   ?? ($actuEdit['titre']   ?? ''));
                    $oldContenu = (string) ($actuOld['contenu'] ?? ($actuEdit['contenu'] ?? ''));
                    ?>
                    <h3 class="admin-actu-heading">
                        <?= $editId > 0 ? t('admin.actu_form_edit') : t('admin.actu_form_new') ?>
                    </h3>
                    <form method="post" action="<?= BASE_URL ?>/admin/actualite" class="form admin-actu-form" id="actu-form">
                        <input type="hidden" name="csrf" value="<?= View::e(Auth::jetonCsrf()) ?>">
                        <?php if ($editId > 0): ?>
                            <input type="hidden" name="id" value="<?= $editId ?>">
                        <?php endif; ?>

                        <label class="form-field">
                            <span><?= t('admin.actu_titre_label') ?></span>
                            <input type="text" name="titre" maxlength="120" required
                                   placeholder="<?= View::e(t('admin.actu_titre_ph')) ?>"
                                   value="<?= View::e($oldTitre) ?>">
                        </label>

                        <div class="form-field">
                            <span><?= t('admin.actu_contenu_label') ?></span>
                            <textarea name="contenu" id="actu-contenu"><?= View::e($oldContenu) ?></textarea>
                            <p class="actu-counter muted">
                                <span id="actu-count">0</span> / 3500 <?= t('admin.actu_chars') ?>
                            </p>
                        </div>

                        <button type="submit" class="btn">
                            <?= $editId > 0 ? t('admin.actu_update') : t('admin.actu_submit') ?>
                        </button>
                        <?php if ($editId > 0): ?>
                            <a class="btn btn-ghost" href="<?= BASE_URL ?>/admin?section=news&vue=gerer"><?= t('admin.actu_cancel') ?></a>
                        <?php endif; ?>
                    </form>

                    <script>
                    window.ACTU = {
                        uploadUrl: <?= json_encode(BASE_URL . '/admin/actualite/image', JSON_UNESCAPED_SLASHES) ?>,
                        csrf: <?= json_encode(Auth::jetonCsrf()) ?>,
                        maxContenu: <?= json_encode((int) 3500) ?>,
                        tinymceBase: <?= json_encode(BASE_URL . '/assets/vendor/tinymce', JSON_UNESCAPED_SLASHES) ?>
                    };
                    </script>
                    <script src="<?= asset('vendor/tinymce/tinymce.min.js') ?>"></script>
                    <script src="<?= asset('js/admin-actualite.js') ?>"></script>

                <?php else: /* gérer les actualités */ ?>

                    <?php if ($actuListe === []): ?>
                        <p class="muted"><?= t('admin.actu_manage_empty') ?></p>
                    <?php else: ?>
                        <table class="actu-table">
                            <thead>
                                <tr>
                                    <th><?= t('admin.actu_col_titre') ?></th>
                                    <th><?= t('admin.actu_col_date') ?></th>
                                    <th class="actu-col-actions"><?= t('admin.actu_col_actions') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($actuListe as $a): ?>
                                    <tr>
                                        <td class="actu-titre"><?= View::e((string) $a['titre']) ?></td>
                                        <td class="actu-date"><?= View::e(substr((string) $a['date_creation'], 0, 16)) ?></td>
                                        <td class="actu-actions">
                                            <a class="btn-lien" href="<?= BASE_URL ?>/admin?section=news&vue=nouvelle&id=<?= (int) $a['id'] ?>">
                                                <i class="ph-light ph-pencil-simple"></i> <?= t('admin.actu_edit') ?>
                                            </a>
                                            <form method="post" action="<?= BASE_URL ?>/admin/actualite/<?= (int) $a['id'] ?>/supprimer"
                                                  onsubmit="return confirm('<?= View::e(t('admin.actu_delete_confirm')) ?>');">
                                                <input type="hidden" name="csrf" value="<?= View::e(Auth::jetonCsrf()) ?>">
                                                <button type="submit" class="btn-lien btn-lien-danger">
                                                    <i class="ph-light ph-trash"></i> <?= t('admin.actu_delete') ?>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>

                <?php endif; ?>
            </div>
        </div>

    <?php else: /* onglet « Activité récente » */ ?>

        <script>
        window.ADMIN = {
            base: <?= json_encode(BASE_URL, JSON_UNESCAPED_SLASHES) ?>,
            i18n: <?= json_encode([
                'modal_title_vol'         => t('admin.modal_title_vol'),
                'modal_title_lieu'        => t('admin.modal_title_lieu'),
                'modal_title_commentaire' => t('admin.modal_title_commentaire'),
                'modal_title_note'        => t('admin.modal_title_note'),
                'modal_title_pilote'      => t('admin.modal_title_pilote'),
                'modal_confirm'           => t('admin.modal_confirm'),
                'modal_warn_ip'           => t('admin.modal_warn_ip'),
                'modal_warn_contribs'     => t('admin.modal_warn_contribs'),
                'modal_cascade'           => t('admin.modal_cascade'),
            ]) ?>
        };
        </script>

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
                    $type      = (string) $a['type'];
                    $idEntite  = (int) ($a['id_entite'] ?? 0);
                    $acteurId  = $a['acteur_id'] !== null ? (int) $a['acteur_id'] : null;
                    $pseudo    = $a['acteur'] !== null ? (string) $a['acteur'] : '—';
                    $cible     = ($a['libelle'] ?? '') !== '' ? (string) $a['libelle'] : t('place.untitled');
                    $href      = $lienCible($a);
                    $pseudoH   = $acteurId !== null
                        ? '<a href="' . BASE_URL . '/pilote/' . $acteurId . '">' . View::e($pseudo) . '</a>'
                        : View::e($pseudo);

                    $nomCible  = $type === 'membre' ? $pseudo : $cible;
                    $hasIpVal  = ($type === 'membre' && $acteurId !== null)
                        ? (($hasIp[$acteurId] ?? false) ? '1' : '0')
                        : null;
                    $nbC       = ($type === 'lieu') ? ($nbContribs[$idEntite] ?? 0) : null;
                    $estSoi    = ($type === 'membre' && $acteurId === $moiId);
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
                        <?php if (!$estSoi && $idEntite > 0): ?>
                        <span class="activite-actions">
                            <button type="button"
                                    class="btn-icon admin-del-btn"
                                    data-type="<?= View::e($type) ?>"
                                    data-id="<?= $idEntite ?>"
                                    data-nom="<?= View::e($nomCible) ?>"
                                    <?php if ($hasIpVal !== null): ?>data-has-ip="<?= $hasIpVal ?>"<?php endif; ?>
                                    <?php if ($nbC !== null): ?>data-nb-contribs="<?= $nbC ?>"<?php endif; ?>
                                    title="<?= View::e(t('admin.modal_delete')) ?>">
                                <i class="ph-light ph-trash"></i>
                            </button>
                        </span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Modale de confirmation suppression / bannissement -->
        <div id="admin-modal-overlay" class="admin-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="modal-titre">
            <div class="admin-modal">
                <h3 id="modal-titre"></h3>
                <p id="modal-confirm-text"></p>
                <p id="modal-warning" class="admin-modal-warning" hidden></p>
                <p id="modal-cascade" class="admin-modal-cascade" hidden></p>
                <div class="admin-modal-actions">
                    <button type="button" id="modal-annuler" class="btn btn-ghost">
                        <?= View::e(t('admin.modal_cancel')) ?>
                    </button>
                    <form id="modal-form-suppr" method="post" action="">
                        <input type="hidden" name="csrf" value="<?= View::e(Auth::jetonCsrf()) ?>">
                        <button type="submit" class="btn btn-danger">
                            <i class="ph-light ph-trash"></i> <?= View::e(t('admin.modal_delete')) ?>
                        </button>
                    </form>
                    <form id="modal-form-bannir" method="post" action="" hidden>
                        <input type="hidden" name="csrf" value="<?= View::e(Auth::jetonCsrf()) ?>">
                        <button type="submit" class="btn btn-danger">
                            <i class="ph-light ph-prohibit"></i> <?= View::e(t('admin.modal_ban')) ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <script>
        (function () {
            const overlay    = document.getElementById('admin-modal-overlay');
            const elTitre    = document.getElementById('modal-titre');
            const elConfirm  = document.getElementById('modal-confirm-text');
            const elWarn     = document.getElementById('modal-warning');
            const elCascade  = document.getElementById('modal-cascade');
            const formSuppr  = document.getElementById('modal-form-suppr');
            const formBannir = document.getElementById('modal-form-bannir');
            const btnAnnuler = document.getElementById('modal-annuler');
            const i18n       = window.ADMIN.i18n;
            const base       = window.ADMIN.base;

            const titres = {
                vol:         i18n.modal_title_vol,
                lieu:        i18n.modal_title_lieu,
                commentaire: i18n.modal_title_commentaire,
                note:        i18n.modal_title_note,
                membre:      i18n.modal_title_pilote,
            };

            function ouvrir(btn) {
                const type       = btn.dataset.type;
                const id         = btn.dataset.id;
                const nom        = btn.dataset.nom;
                const hasIp      = btn.dataset.hasIp;
                const nbContribs = parseInt(btn.dataset.nbContribs || '0', 10);

                elTitre.textContent   = titres[type] || type;
                elConfirm.textContent = i18n.modal_confirm.replace('{nom}', nom);

                elWarn.hidden    = true;
                elCascade.hidden = true;
                formBannir.hidden = true;

                if (type === 'membre') {
                    elCascade.textContent = i18n.modal_cascade;
                    elCascade.hidden = false;
                    if (hasIp === '0') {
                        elWarn.textContent = i18n.modal_warn_ip;
                        elWarn.hidden = false;
                    }
                    formBannir.action = base + '/admin/pilote/' + id + '/bannir';
                    formBannir.hidden = false;
                }

                if (type === 'lieu' && nbContribs > 0) {
                    elWarn.textContent = i18n.modal_warn_contribs.replace('{n}', nbContribs);
                    elWarn.hidden = false;
                }

                formSuppr.action = base + '/admin/' + (type === 'membre' ? 'pilote' : type) + '/' + id + '/supprimer';
                overlay.classList.add('is-open');
                btnAnnuler.focus();
            }

            function fermer() { overlay.classList.remove('is-open'); }

            document.querySelectorAll('.admin-del-btn').forEach(btn => {
                btn.addEventListener('click', () => ouvrir(btn));
            });

            btnAnnuler.addEventListener('click', fermer);
            overlay.addEventListener('click', e => { if (e.target === overlay) fermer(); });
            document.addEventListener('keydown', e => { if (e.key === 'Escape') fermer(); });
        }());
        </script>

    <?php endif; ?>
</section>
