<?php declare(strict_types=1); ?>

<section class="hero">
    <h1><?= t('map.heading') ?></h1>
    <p><?= t('map.intro') ?></p>
</section>

<!-- Emplacement de la future carte Leaflet -->
<div id="map" class="map-placeholder">
    <span>🗺️ <?= t('map.placeholder') ?></span>
</div>
