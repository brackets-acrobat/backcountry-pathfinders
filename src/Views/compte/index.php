<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\View;

/** @var array<int,array<string,mixed>> $cles */
/** @var string|null $nouvelleCle */
?>
<section class="compte">
    <h1><?= t('account.heading') ?></h1>

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
        <input type="hidden" name="csrf" value="<?= View::e(Auth::jetonCsrf()) ?>">
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
                                <input type="hidden" name="csrf" value="<?= View::e(Auth::jetonCsrf()) ?>">
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
