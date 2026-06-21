<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\View;

/** @var array<string,mixed>      $utilisateur */
/** @var array<int,array<string,mixed>> $cles */
/** @var string|null               $nouvelleCle */
/** @var array{type:string,msgs:array<int,string>}|null $flash */

$csrf = View::e(Auth::jetonCsrf());
$avatar = $utilisateur['avatar'] ?? null;
?>
<section class="compte">
    <h1><?= t('account.heading') ?></h1>

    <?php if ($flash !== null): ?>
        <div class="alert<?= $flash['type'] === 'ok' ? ' alert-success' : '' ?>">
            <ul>
                <?php foreach ($flash['msgs'] as $m): ?>
                    <li><?= View::e(t($m)) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Avatar -->
    <h2><?= t('account.avatar_section') ?></h2>
    <p class="compte-intro"><?= t('account.avatar_intro') ?></p>
    <div class="avatar-row">
        <?php if ($avatar !== null && $avatar !== ''): ?>
            <img class="avatar-preview" src="<?= BASE_URL ?>/uploads/<?= View::e((string) $avatar) ?>" alt="">
        <?php else: ?>
            <span class="avatar-preview avatar-none"><i class="ph-light ph-user"></i></span>
        <?php endif; ?>
        <form method="post" action="<?= BASE_URL ?>/compte/avatar" enctype="multipart/form-data" class="avatar-form">
            <input type="hidden" name="csrf" value="<?= $csrf ?>">
            <input type="file" name="avatar" accept="image/png,image/jpeg" required>
            <button type="submit" class="btn"><?= t('account.save_avatar') ?></button>
        </form>
    </div>

    <!-- Profil : pseudo + e-mail -->
    <h2><?= t('account.profile_section') ?></h2>
    <p class="compte-intro"><?= t('account.profile_intro') ?></p>
    <form method="post" action="<?= BASE_URL ?>/compte/profil" class="form form-compte">
        <input type="hidden" name="csrf" value="<?= $csrf ?>">
        <label><?= t('common.pseudo') ?>
            <input type="text" name="pseudo" required minlength="3" maxlength="40"
                   value="<?= View::e((string) ($utilisateur['pseudo'] ?? '')) ?>" autocomplete="username">
        </label>
        <label><?= t('common.email') ?>
            <input type="email" name="email" required
                   value="<?= View::e((string) ($utilisateur['email'] ?? '')) ?>" autocomplete="email">
        </label>
        <button type="submit" class="btn"><?= t('account.save_profile') ?></button>
    </form>

    <!-- Mot de passe -->
    <h2><?= t('account.password_section') ?></h2>
    <form method="post" action="<?= BASE_URL ?>/compte/motdepasse" class="form form-compte">
        <input type="hidden" name="csrf" value="<?= $csrf ?>">
        <label><?= t('account.new_password') ?> <span class="hint"><?= t('register.password_hint') ?></span>
            <input type="password" name="mot_de_passe" required minlength="8" autocomplete="new-password">
        </label>
        <label><?= t('account.confirm_password') ?>
            <input type="password" name="confirmation" required minlength="8" autocomplete="new-password">
        </label>
        <button type="submit" class="btn"><?= t('account.save_password') ?></button>
    </form>

    <!-- Clés API -->
    <h2><?= t('account.api_section') ?></h2>
    <p class="compte-intro"><?= t('account.api_intro') ?></p>

    <?php if ($nouvelleCle !== null): ?>
        <div class="cle-nouvelle">
            <p class="cle-avert">⚠️ <?= t('account.key_created_warning') ?></p>
            <div class="cle-affichee">
                <input type="text" readonly value="<?= View::e($nouvelleCle) ?>"
                       onclick="this.select()" id="cle-claire">
                <button type="button" class="btn" onclick="copierCle()"><?= t('account.copy') ?></button>
            </div>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= BASE_URL ?>/compte/cles" class="form-inline">
        <input type="hidden" name="csrf" value="<?= $csrf ?>">
        <input type="text" name="libelle" maxlength="60"
               placeholder="<?= t('account.new_key_placeholder') ?>">
        <button type="submit" class="btn"><?= t('account.create_key') ?></button>
    </form>

    <?php if ($cles === []): ?>
        <p class="compte-vide"><?= t('account.no_keys') ?></p>
    <?php else: ?>
        <table class="table-cles">
            <thead>
                <tr>
                    <th><?= t('account.col_label') ?></th>
                    <th><?= t('account.col_created') ?></th>
                    <th><?= t('account.col_last_used') ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cles as $cle): ?>
                    <tr>
                        <td><?= $cle['libelle'] !== null ? View::e($cle['libelle']) : '<em>' . t('account.unnamed') . '</em>' ?></td>
                        <td><?= View::e(substr((string) $cle['date_creation'], 0, 10)) ?></td>
                        <td><?= $cle['derniere_utilisation'] !== null
                                ? View::e(substr((string) $cle['derniere_utilisation'], 0, 16))
                                : '<em>' . t('account.never_used') . '</em>' ?></td>
                        <td>
                            <form method="post" action="<?= BASE_URL ?>/compte/cles/supprimer"
                                  onsubmit="return confirm('<?= View::e(t('account.delete_confirm')) ?>');">
                                <input type="hidden" name="csrf" value="<?= $csrf ?>">
                                <input type="hidden" name="id" value="<?= (int) $cle['id'] ?>">
                                <button type="submit" class="btn-lien"><?= t('account.delete_key') ?></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<script>
function copierCle() {
    var champ = document.getElementById('cle-claire');
    champ.select();
    navigator.clipboard.writeText(champ.value);
}
</script>
