<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\View;

/** @var string $secret */
/** @var string $uri */
/** @var array<int,string> $erreurs */

// Clé groupée par blocs de 4 pour une saisie manuelle plus facile.
$secretLisible = trim(chunk_split($secret, 4, ' '));
?>
<section class="form-section form-2fa form-2fa-setup">
    <h1><i class="ph-light ph-shield-check"></i> <?= t('2fa.setup_heading') ?></h1>

    <?php if (!empty($erreurs)): ?>
        <div class="alert">
            <ul>
                <?php foreach ($erreurs as $e): ?>
                    <li><?= View::e(t($e)) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <p class="muted"><?= t('2fa.setup_intro') ?></p>

    <ol class="twofa-steps">
        <li><?= t('2fa.step_app') ?></li>
        <li>
            <?= t('2fa.step_scan') ?>
            <div class="twofa-qr" id="twofa-qr" data-uri="<?= View::e($uri) ?>"></div>
            <p class="twofa-secret">
                <span class="muted"><?= t('2fa.secret_label') ?> :</span>
                <code><?= View::e($secretLisible) ?></code>
            </p>
        </li>
        <li><?= t('2fa.step_confirm') ?></li>
    </ol>

    <form method="post" action="<?= BASE_URL ?>/connexion/2fa/configurer" class="form">
        <input type="hidden" name="csrf" value="<?= View::e(Auth::jetonCsrf()) ?>">

        <label><?= t('2fa.code_label') ?>
            <input class="twofa-code" type="text" name="code" inputmode="numeric"
                   autocomplete="one-time-code" pattern="\d{6}" maxlength="6" required>
        </label>

        <button type="submit" class="btn"><?= t('2fa.activate') ?></button>
    </form>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
(function () {
    var el = document.getElementById('twofa-qr');
    if (el && window.QRCode) {
        new QRCode(el, {
            text: el.dataset.uri,
            width: 200,
            height: 200,
            colorDark: '#000000',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.M
        });
    }
})();
</script>
