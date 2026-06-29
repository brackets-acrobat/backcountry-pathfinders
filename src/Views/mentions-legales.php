<?php declare(strict_types=1); ?>

<section class="legal">
    <h1 class="legal-title"><?= t('legal_notice.title') ?></h1>

    <div class="legal-body">

        <p><?= t('legal_notice.intro') ?></p>

        <h2><?= t('legal_notice.editor_h') ?></h2>
        <p>
            <strong><?= t('legal_notice.owner_label') ?></strong><br>
            <?= t('legal_notice.owner_value') ?>
        </p>
        <p>
            <strong><?= t('legal_notice.contact_label') ?></strong><br>
            <a href="mailto:<?= t('privacy.contact_email') ?>"><?= t('privacy.contact_email') ?></a>
        </p>
        <p>
            <strong><?= t('legal_notice.dev_label') ?></strong><br>
            <?= t('legal_notice.dev_value') ?>
        </p>

        <h2><?= t('legal_notice.hosting_h') ?></h2>
        <p><?= t('legal_notice.hosting_intro') ?></p>
        <p class="legal-address">
            <strong><?= t('legal_notice.host_name') ?></strong><br>
            <?= t('legal_notice.host_company') ?><br>
            <?= t('legal_notice.host_address') ?><br>
            <?= t('legal_notice.host_phone') ?><br>
            <a href="https://www.o2switch.fr" target="_blank" rel="noopener noreferrer">www.o2switch.fr</a>
        </p>

        <h2><?= t('legal_notice.ip_h') ?></h2>
        <p><?= t('legal_notice.ip_p1') ?></p>
        <p><?= t('legal_notice.ip_p2') ?></p>

        <h2><?= t('legal_notice.links_h') ?></h2>
        <p><?= t('legal_notice.links_p') ?></p>

        <h2><?= t('legal_notice.liability_h') ?></h2>
        <p><?= t('legal_notice.liability_p') ?></p>

        <h2><?= t('legal_notice.data_h') ?></h2>
        <p>
            <?= t('legal_notice.data_p') ?>
            <a href="<?= BASE_URL ?>/confidentialite"><?= t('footer.privacy') ?></a>.
        </p>

    </div>
</section>
