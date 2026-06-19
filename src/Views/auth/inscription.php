<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\View;

/** @var array<int,string>     $erreurs */
/** @var array<string,string>  $anciennes */
?>
<section class="form-section">
    <h1><?= t('register.heading') ?></h1>

    <?php if (!empty($erreurs)): ?>
        <div class="alert">
            <ul>
                <?php foreach ($erreurs as $e): ?>
                    <li><?= View::e(t($e)) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= BASE_URL ?>/inscription" class="form">
        <input type="hidden" name="csrf" value="<?= View::e(Auth::jetonCsrf()) ?>">

        <label><?= t('common.pseudo') ?>
            <input type="text" name="pseudo" value="<?= View::e($anciennes['pseudo'] ?? '') ?>"
                   required minlength="3" maxlength="40" autocomplete="username">
        </label>

        <label><?= t('common.email') ?>
            <input type="email" name="email" value="<?= View::e($anciennes['email'] ?? '') ?>"
                   required autocomplete="email">
        </label>

        <label><?= t('common.password') ?> <span class="hint"><?= t('register.password_hint') ?></span>
            <input type="password" name="mot_de_passe" required minlength="8" autocomplete="new-password">
        </label>

        <label><?= t('register.confirm') ?>
            <input type="password" name="confirmation" required minlength="8" autocomplete="new-password">
        </label>

        <button type="submit" class="btn"><?= t('register.submit') ?></button>
    </form>

    <p class="form-alt"><?= t('register.have_account') ?> <a href="<?= BASE_URL ?>/connexion"><?= t('register.login_link') ?></a></p>
</section>
