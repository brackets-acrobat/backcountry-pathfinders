<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\View;

/** @var array<int,string>     $erreurs */
/** @var array<string,string>  $anciennes */
?>
<section class="form-section">
    <h1><?= t('login.heading') ?></h1>

    <?php if (!empty($erreurs)): ?>
        <div class="alert">
            <ul>
                <?php foreach ($erreurs as $e): ?>
                    <li><?= View::e(t($e)) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= BASE_URL ?>/connexion" class="form">
        <input type="hidden" name="csrf" value="<?= View::e(Auth::jetonCsrf()) ?>">

        <label><?= t('common.email') ?>
            <input type="email" name="email" value="<?= View::e($anciennes['email'] ?? '') ?>"
                   required autocomplete="email">
        </label>

        <label><?= t('common.password') ?>
            <input type="password" name="mot_de_passe" required autocomplete="current-password">
        </label>

        <button type="submit" class="btn"><?= t('login.submit') ?></button>
    </form>

    <p class="form-alt"><?= t('login.no_account') ?> <a href="<?= BASE_URL ?>/inscription"><?= t('login.register_link') ?></a></p>
</section>
