<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\View;

/** @var string $jeton */
/** @var bool $valide */
/** @var bool $succes */
/** @var array<int,string> $erreurs */
?>
<section class="form-section">
    <h1><?= t('reset.heading') ?></h1>

    <?php if ($succes): ?>

        <div class="alert alert-ok"><?= t('reset.success') ?></div>
        <p class="form-alt"><a href="<?= BASE_URL ?>/connexion"><?= t('reset.go_login') ?></a></p>

    <?php elseif (!$valide): ?>

        <div class="alert"><?= t('reset.invalid') ?></div>
        <p class="form-alt"><a href="<?= BASE_URL ?>/mot-de-passe-oublie"><?= t('reset.retry') ?></a></p>

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

        <p class="form-intro"><?= t('reset.intro') ?></p>

        <form method="post" action="<?= BASE_URL ?>/reinitialiser/<?= View::e($jeton) ?>" class="form">
            <input type="hidden" name="csrf" value="<?= View::e(Auth::jetonCsrf()) ?>">

            <label><?= t('reset.new_password') ?>
                <input type="password" name="mot_de_passe" required autocomplete="new-password" autofocus>
            </label>

            <label><?= t('reset.confirm_password') ?>
                <input type="password" name="confirmation" required autocomplete="new-password">
            </label>

            <p class="form-hint muted"><?= t('reset.password_hint') ?></p>

            <button type="submit" class="btn"><?= t('reset.submit') ?></button>
        </form>

    <?php endif; ?>
</section>
