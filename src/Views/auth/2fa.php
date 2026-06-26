<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\View;

/** @var array<int,string> $erreurs */
?>
<section class="form-section form-2fa">
    <h1><i class="ph-light ph-shield-check"></i> <?= t('2fa.heading') ?></h1>

    <?php if (!empty($erreurs)): ?>
        <div class="alert">
            <ul>
                <?php foreach ($erreurs as $e): ?>
                    <li><?= View::e(t($e)) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <p class="muted"><?= t('2fa.intro') ?></p>

    <form method="post" action="<?= BASE_URL ?>/connexion/2fa" class="form">
        <input type="hidden" name="csrf" value="<?= View::e(Auth::jetonCsrf()) ?>">

        <label><?= t('2fa.code_label') ?>
            <input class="twofa-code" type="text" name="code" inputmode="numeric"
                   autocomplete="one-time-code" pattern="\d{6}" maxlength="6"
                   required autofocus>
        </label>

        <button type="submit" class="btn"><?= t('2fa.submit') ?></button>
    </form>

    <p class="form-alt"><a href="<?= BASE_URL ?>/connexion"><?= t('2fa.back') ?></a></p>
</section>
