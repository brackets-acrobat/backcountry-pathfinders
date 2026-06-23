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

    // Moyenne mobile centrée : adoucit les marches d'altitude (échantillons au mètre).
    function lisser(arr, r) {
        if (r < 1 || arr.length < 3) { return arr.slice(); }
        var out = [];
        for (var i = 0; i < arr.length; i++) {
            var a = Math.max(0, i - r), b = Math.min(arr.length - 1, i + r), s = 0;
            for (var j = a; j <= b; j++) { s += arr[j]; }
            out.push(s / (b - a + 1));
        }
        return out;
    }

    // Courbe douce passant par tous les points (Catmull-Rom → Bézier cubique).
    function courbe(P) {
        if (P.length < 2) { return ''; }
        var d = 'M' + P[0][0].toFixed(1) + ' ' + P[0][1].toFixed(1) + ' ';
        for (var i = 0; i < P.length - 1; i++) {
            var p0 = P[i === 0 ? 0 : i - 1], p1 = P[i], p2 = P[i + 1];
            var p3 = P[i + 2 < P.length ? i + 2 : P.length - 1];
            var c1x = p1[0] + (p2[0] - p0[0]) / 6, c1y = p1[1] + (p2[1] - p0[1]) / 6;
            var c2x = p2[0] - (p3[0] - p1[0]) / 6, c2y = p2[1] - (p3[1] - p1[1]) / 6;
            d += 'C' + c1x.toFixed(1) + ' ' + c1y.toFixed(1) + ' ' +
                       c2x.toFixed(1) + ' ' + c2y.toFixed(1) + ' ' +
                       p2[0].toFixed(1) + ' ' + p2[1].toFixed(1) + ' ';
        }
        return d;
    }

    function dessinerProfil(el) {
        var raw = el.getAttribute('data-profil');
        var points;
        try { points = JSON.parse(raw); } catch (e) { return; }
        if (!Array.isArray(points) || points.length < 2) { return; }

        // Échantillons attendus : [{ d: distance, alt: altitude }, ...], triés par distance.
        var pts = points
            .map(function (p) { return { d: Number(p.d), alt: Number(p.alt) }; })
            .filter(function (p) { return isFinite(p.d) && isFinite(p.alt); })
            .sort(function (a, b) { return a.d - b.d; });
        if (pts.length < 2) { return; }

        // Domaine X = longueur du roulage enregistrée en base (si dispo) : le relief
        // occupe alors toute la largeur du roulage. Sinon, étendue des échantillons.
        var roll = parseFloat(el.getAttribute('data-roll'));
        var hasRoll = isFinite(roll) && roll > 0;
        if (hasRoll) {
            pts = pts.filter(function (p) { return p.d <= roll; });
            if (pts.length < 2) { return; }
            // Prolonge jusqu'au bout du roulage enregistré si le dernier échantillon
            // est en deçà (le relief occupe alors toute la largeur du roulage).
            var dernier = pts[pts.length - 1];
            if (dernier.d < roll) { pts.push({ d: roll, alt: dernier.alt }); }
        }

        var xs = pts.map(function (p) { return p.d; });
        var ys = lisser(pts.map(function (p) { return p.alt; }), Math.max(1, Math.round(pts.length / 24)));

        var xMin = 0;
        var xMax = hasRoll ? roll : xs[xs.length - 1];
        var yMin = Math.min.apply(null, ys), yMax = Math.max.apply(null, ys);
        var w = 320, h = 70, pad = 4;
        var spanX = (xMax - xMin) || 1;
        var spanY = (yMax - yMin) || 1;

        function px(x) { return pad + (x - xMin) / spanX * (w - 2 * pad); }
        function py(y) { return h - pad - (y - yMin) / spanY * (h - 2 * pad); }

        var P = [];
        for (var i = 0; i < xs.length; i++) { P.push([px(xs[i]), py(ys[i])]); }

        var ligne = courbe(P);
        var area = ligne + 'L' + P[P.length - 1][0].toFixed(1) + ' ' + (h - pad) +
                   ' L' + P[0][0].toFixed(1) + ' ' + (h - pad) + ' Z';

        var svg =
            '<svg viewBox="0 0 ' + w + ' ' + h + '" preserveAspectRatio="none" width="100%" height="' + h + '">' +
            '<path d="' + area + '" fill="rgba(58,242,75,0.22)"/>' +
            '<path d="' + ligne + '" fill="none" stroke="#3af24b" stroke-width="1.5" ' +
            'stroke-linejoin="round" stroke-linecap="round"/>' +
            '</svg>';
        el.innerHTML = svg;
    }

    var profils = document.querySelectorAll('.releve-profil');
    for (var i = 0; i < profils.length; i++) {
        dessinerProfil(profils[i]);
    }
}());
