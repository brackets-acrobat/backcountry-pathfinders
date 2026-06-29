<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\View;

/** @var array<int,string> $erreurs */
/** @var bool $envoye */
?>
<section class="form-section">
    <h1><?= t('forgot.heading') ?></h1>

    <?php if ($envoye): ?>

        <div class="alert alert-ok">
            <?= t('forgot.sent') ?>
        </div>
        <p class="form-alt"><a href="<?= BASE_URL ?>/connexion"><?= t('forgot.back_login') ?></a></p>

    <?php else: ?>

        <?php if (!empty($erreurs)): ?>
            <div class="alert">
                <ul>
                    <?php foreach ($erreurs as $e): ?>
                        <li><?= View::e(t($e)) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <p class="form-intro"><?= t('forgot.intro') ?></p>

        <form method="post" action="<?= BASE_URL ?>/mot-de-passe-oublie" class="form">
            <input type="hidden" name="csrf" value="<?= View::e(Auth::jetonCsrf()) ?>">

            <label><?= t('common.email') ?>
                <input type="email" name="email" required autocomplete="email" autofocus>
            </label>

            <?= turnstile_widget() ?>

            <button type="submit" class="btn"><?= t('forgot.submit') ?></button>
        </form>

        <p class="form-alt"><a href="<?= BASE_URL ?>/connexion"><?= t('forgot.back_login') ?></a></p>

    <?php endif; ?>
</section>
