<?php declare(strict_types=1); ?>

<!-- Leaflet (carte interactive) -->
<link rel="stylesheet"
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin="">
<!-- Leaflet.markercluster (regroupement des marqueurs proches) -->
<link rel="stylesheet"
      href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css"
      integrity="sha256-YU3qCpj/P06tdPBJGPax0bm6Q1wltfwjsho5TR4+TYc="
      crossorigin="">

<section class="map-page">
    <div id="map" class="map"></div>
</section>

<script>
window.BCP = {
    base: <?= json_encode(BASE_URL, JSON_UNESCAPED_SLASHES) ?>,
    i18n: <?= json_encode([
        'loading'    => t('map.loading'),
        'empty'      => t('map.empty'),
        'error'      => t('map.error'),
        'surveys'    => t('map.surveys'),
        'rating'     => t('map.rating'),
        'difficulty' => t('map.difficulty'),
        'surface'    => t('survey.surface'),
        'altitude'   => t('map.altitude'),
        'detail'     => t('map.detail'),
        'untitled'   => t('place.untitled'),
        'layers'     => ['dark' => t('map.layer_dark')],
        'surfaces'   => [
            'grass'    => t('surface.grass'),
            'dirt'     => t('surface.dirt'),
            'sand'     => t('surface.sand'),
            'snow'     => t('surface.snow'),
            'ice'      => t('surface.ice'),
            'water'    => t('surface.water'),
            'concrete' => t('surface.concrete'),
            'asphalt'  => t('surface.asphalt'),
            'unknown'  => t('surface.unknown'),
        ],
    ], JSON_UNESCAPED_UNICODE) ?>,
};
</script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"
        integrity="sha256-Hk4dIpcqOSb0hZjgyvFOP+cEmDXUKKNE/tT542ZbNQg="
        crossorigin=""></script>
<script src="<?= asset('js/carte.js') ?>"></script>
