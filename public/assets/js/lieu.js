// Fiche détail d'un lieu : mini-carte de situation + profils de relief.
(function () {
    'use strict';

    // --- Mini-carte centrée sur le lieu ---
    var mapEl = document.getElementById('place-map');
    if (mapEl && typeof L !== 'undefined') {
        var lat = parseFloat(mapEl.getAttribute('data-lat'));
        var lon = parseFloat(mapEl.getAttribute('data-lon'));
        if (isFinite(lat) && isFinite(lon)) {
            var map = L.map(mapEl, {
                scrollWheelZoom: false,
                zoomControl: true,
            }).setView([lat, lon], 13);

            L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
                maxZoom: 17,
                attribution:
                    '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> | ' +
                    '&copy; <a href="https://opentopomap.org">OpenTopoMap</a> (CC-BY-SA)',
            }).addTo(map);

            L.circleMarker([lat, lon], {
                radius: 8, color: '#000', weight: 1,
                fillColor: '#3af24b', fillOpacity: 0.95,
            }).addTo(map);
        }
    }

    // --- Profils de relief : petit graphe SVG par relevé ---
    function dessinerProfil(el) {
        var raw = el.getAttribute('data-profil');
        var points;
        try { points = JSON.parse(raw); } catch (e) { return; }
        if (!Array.isArray(points) || points.length < 2) { return; }

        // Échantillons attendus : [{ d: distance, alt: altitude }, ...]
        var xs = points.map(function (p) { return Number(p.d); });
        var ys = points.map(function (p) { return Number(p.alt); });
        var xMin = Math.min.apply(null, xs), xMax = Math.max.apply(null, xs);
        var yMin = Math.min.apply(null, ys), yMax = Math.max.apply(null, ys);
        var w = 320, h = 70, pad = 4;
        var spanX = (xMax - xMin) || 1;
        var spanY = (yMax - yMin) || 1;

        function px(x) { return pad + (x - xMin) / spanX * (w - 2 * pad); }
        function py(y) { return h - pad - (y - yMin) / spanY * (h - 2 * pad); }

        var d = '';
        for (var i = 0; i < points.length; i++) {
            d += (i === 0 ? 'M' : 'L') + px(xs[i]).toFixed(1) + ' ' + py(ys[i]).toFixed(1) + ' ';
        }
        var area = d + 'L' + px(xMax).toFixed(1) + ' ' + (h - pad) +
                   ' L' + px(xMin).toFixed(1) + ' ' + (h - pad) + ' Z';

        var svg =
            '<svg viewBox="0 0 ' + w + ' ' + h + '" preserveAspectRatio="none" width="100%" height="' + h + '">' +
            '<path d="' + area + '" fill="rgba(58,242,75,0.22)"/>' +
            '<path d="' + d + '" fill="none" stroke="#3af24b" stroke-width="1.5"/>' +
            '</svg>';
        el.innerHTML = svg;
    }

    var profils = document.querySelectorAll('.releve-profil');
    for (var i = 0; i < profils.length; i++) {
        dessinerProfil(profils[i]);
    }
}());
