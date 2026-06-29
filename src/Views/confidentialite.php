<?php declare(strict_types=1); ?>

<section class="legal">
    <h1 class="legal-title"><?= t('privacy.title') ?></h1>
    <p class="legal-updated muted"><?= t('privacy.updated') ?></p>

    <div class="legal-body">

        <h2><?= t('privacy.s_controller_h') ?></h2>
        <p><?= t('privacy.s_controller_p') ?></p>

        <h2><?= t('privacy.s_data_h') ?></h2>
        <p><?= t('privacy.s_data_intro') ?></p>
        <ul class="legal-list">
            <li><strong><?= t('privacy.data_account_t') ?></strong> <?= t('privacy.data_account_d') ?></li>
            <li><strong><?= t('privacy.data_content_t') ?></strong> <?= t('privacy.data_content_d') ?></li>
            <li><strong><?= t('privacy.data_technical_t') ?></strong> <?= t('privacy.data_technical_d') ?></li>
        </ul>

        <h2><?= t('privacy.s_cookies_h') ?></h2>
        <p><?= t('privacy.s_cookies_intro') ?></p>

        <h3><?= t('privacy.cookies_first_h') ?></h3>
        <div class="legal-table-wrap">
            <table class="legal-table">
                <thead>
                    <tr>
                        <th><?= t('privacy.cookies_th_name') ?></th>
                        <th><?= t('privacy.cookies_th_purpose') ?></th>
                        <th><?= t('privacy.cookies_th_duration') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>PHPSESSID</code></td>
                        <td><?= t('privacy.cookie_session_d') ?></td>
                        <td><?= t('privacy.cookie_session_dur') ?></td>
                    </tr>
                    <tr>
                        <td><code>langue</code></td>
                        <td><?= t('privacy.cookie_lang_d') ?></td>
                        <td><?= t('privacy.cookie_lang_dur') ?></td>
                    </tr>
                    <tr>
                        <td><code>bcp_souvenir</code></td>
                        <td><?= t('privacy.cookie_remember_d') ?></td>
                        <td><?= t('privacy.cookie_remember_dur') ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="muted"><?= t('privacy.cookies_necessary_note') ?></p>

        <h3><?= t('privacy.cookies_third_h') ?></h3>
        <p><?= t('privacy.cookies_third_p') ?></p>

        <h2><?= t('privacy.s_thirdparty_h') ?></h2>
        <p><?= t('privacy.s_thirdparty_intro') ?></p>
        <ul class="legal-list">
            <li><strong>Cloudflare Turnstile</strong> — <?= t('privacy.tp_turnstile') ?></li>
            <li><strong>unpkg / cdnjs</strong> — <?= t('privacy.tp_cdn') ?></li>
            <li><strong>CARTO, OpenTopoMap, OpenStreetMap</strong> — <?= t('privacy.tp_tiles') ?></li>
            <li><strong>BigDataCloud</strong> — <?= t('privacy.tp_geocode') ?></li>
        </ul>
        <p class="muted"><?= t('privacy.tp_ip_note') ?></p>

        <h2><?= t('privacy.s_legal_h') ?></h2>
        <p><?= t('privacy.s_legal_p') ?></p>

        <h2><?= t('privacy.s_retention_h') ?></h2>
        <ul class="legal-list">
            <li><?= t('privacy.retention_account') ?></li>
            <li><?= t('privacy.retention_session') ?></li>
            <li><?= t('privacy.retention_remember') ?></li>
            <li><?= t('privacy.retention_content') ?></li>
        </ul>

        <h2><?= t('privacy.s_recipients_h') ?></h2>
        <p><?= t('privacy.s_recipients_p') ?></p>

        <h2><?= t('privacy.s_rights_h') ?></h2>
        <p><?= t('privacy.s_rights_intro') ?></p>
        <ul class="legal-list">
            <li><?= t('privacy.right_access') ?></li>
            <li><?= t('privacy.right_rectify') ?></li>
            <li><?= t('privacy.right_erase') ?></li>
            <li><?= t('privacy.right_portability') ?></li>
            <li><?= t('privacy.right_object') ?></li>
            <li><?= t('privacy.right_complaint') ?></li>
        </ul>
        <p><?= t('privacy.rights_exercise') ?> <a href="mailto:<?= t('privacy.contact_email') ?>"><?= t('privacy.contact_email') ?></a></p>

        <h2><?= t('privacy.s_security_h') ?></h2>
        <p><?= t('privacy.s_security_p') ?></p>

        <h2><?= t('privacy.s_changes_h') ?></h2>
        <p><?= t('privacy.s_changes_p') ?></p>

    </div>
</section>
