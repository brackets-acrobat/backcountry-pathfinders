// Carte Leaflet des lieux de poser.
// Charge les marqueurs depuis l'API JSON (/api/lieux) et les colore par surface.
(function () {
    'use strict';

    var cfg = window.BCP || { base: '', i18n: {} };
    var t = cfg.i18n || {};

    var mapEl = document.getElementById('map');
    if (!mapEl || typeof L === 'undefined') { return; }

    // --- Fonds de carte sélectionnables (menu déroulant en haut à droite) ---
    var attrOsm = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>';
    var attrCarto = ' &copy; <a href="https://carto.com/attributions">CARTO</a>';

    var fonds = {
        // Dark Matter : défaut, cohérent avec la charte noir/orange.
        'Sombre': L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            maxZoom: 19, attribution: attrOsm + attrCarto,
        }),
        'Positron': L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            maxZoom: 19, attribution: attrOsm + attrCarto,
        }),
        'OpenStreetMap': L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19, attribution: attrOsm,
        }),
        'OpenTopoMap': L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
            maxZoom: 17,
            attribution: attrOsm + ' | &copy; <a href="https://opentopomap.org">OpenTopoMap</a> (CC-BY-SA)',
        }),
    };

    var labels = (t.layers) || {};
    var fondDefaut = fonds['Sombre'];

    // Libellés affichés (traduits) → on garde les objets calques, on renomme les clés.
    var fondsAffiches = {};
    fondsAffiches[labels.dark || 'Sombre'] = fonds['Sombre'];
    fondsAffiches[labels.positron || 'Positron'] = fonds['Positron'];
    fondsAffiches[labels.osm || 'OpenStreetMap'] = fonds['OpenStreetMap'];
    fondsAffiches[labels.topo || 'OpenTopoMap'] = fonds['OpenTopoMap'];

    var map = L.map(mapEl, {
        worldCopyJump: true,
        layers: [fondDefaut],
    }).setView([46.6, 2.4], 5);

    L.control.layers(fondsAffiches, null, { position: 'topright' }).addTo(map);

    // --- Couleur des marqueurs selon la surface dominante (codes MSFS) ---
    function couleurSurface(surface) {
        var s = (surface || '').toLowerCase();
        if (s.indexOf('grass') !== -1) { return '#5fbf52'; }                       // herbe
        if (s.indexOf('dirt') !== -1 || s.indexOf('sand') !== -1) { return '#b07a3c'; } // terre / sable
        if (s.indexOf('water') !== -1) { return '#3d8fd1'; }                       // eau
        if (s.indexOf('snow') !== -1 || s.indexOf('ice') !== -1) { return '#7fd0e0'; }  // neige / glace
        if (s.indexOf('concrete') !== -1 || s.indexOf('asphalt') !== -1) { return '#9aa0a6'; } // dur
        return '#c9c9c9';                                                          // inconnu
    }

    // Libellé traduit d'une surface (repli : la valeur brute).
    function libelleSurface(surface) {
        if (!surface) { return null; }
        var s = surface.toLowerCase();
        var keys = ['grass', 'dirt', 'sand', 'snow', 'ice', 'water', 'concrete', 'asphalt'];
        for (var i = 0; i < keys.length; i++) {
            if (s.indexOf(keys[i]) !== -1 && t.surfaces && t.surfaces[keys[i]]) {
                return t.surfaces[keys[i]];
            }
        }
        return surface;
    }

    function escapeHtml(str) {
        return String(str).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function etoiles(note) {
        var pleines = Math.round(note);
        var html = '';
        for (var i = 1; i <= 5; i++) {
            html += i <= pleines
                ? '<i class="ph-fill ph-star"></i>'
                : '<i class="ph-light ph-star star-empty"></i>';
        }
        return html;
    }

    function popupHtml(lieu) {
        var nom = lieu.nom ? escapeHtml(lieu.nom) : (t.untitled || 'Lieu') + ' #' + lieu.id;
        var html = '<div class="bcp-popup"><strong>' + nom + '</strong>';

        var lignes = [];
        if (lieu.surface) {
            lignes.push('<span class="bcp-dot" style="background:' + couleurSurface(lieu.surface) +
                '"></span>' + escapeHtml(libelleSurface(lieu.surface)));
        }
        if (lieu.altitude_m !== null && lieu.altitude_m !== undefined) {
            // Stockage en mètres, affichage en pieds (aéronautique).
            var altFt = Math.round(lieu.altitude_m * 3.280839895);
            lignes.push((t.altitude || 'Alt.') + ' : ' + altFt + ' ft');
        }
        lignes.push((t.surveys || 'Relevés') + ' : ' + lieu.nb_releves);
        if (lieu.note_moyenne !== null && lieu.note_moyenne !== undefined) {
            lignes.push((t.rating || 'Note') + ' : ' + etoiles(lieu.note_moyenne) +
                ' (' + lieu.note_moyenne.toFixed(1) + ')');
        }
        if (lieu.difficulte_moyenne !== null && lieu.difficulte_moyenne !== undefined) {
            lignes.push((t.difficulty || 'Difficulté') + ' : ' + lieu.difficulte_moyenne.toFixed(1) + '/5');
        }

        html += '<div class="bcp-popup-lines">' + lignes.join('<br>') + '</div>';
        html += '<a class="bcp-popup-link" href="' + cfg.base + '/lieu/' + lieu.id + '">' +
            (t.detail || 'Voir le détail') + ' <i class="ph-bold ph-arrow-right"></i></a>';
        html += '</div>';
        return html;
    }

    // --- Chargement des lieux ---
    fetch(cfg.base + '/api/lieux', { headers: { 'Accept': 'application/json' } })
        .then(function (r) {
            if (!r.ok) { throw new Error('HTTP ' + r.status); }
            return r.json();
        })
        .then(function (data) {
            var lieux = (data && data.lieux) || [];
            if (!lieux.length) {
                afficherMessage(t.empty || 'Aucun lieu pour le moment.');
                return;
            }

            var bornes = [];
            lieux.forEach(function (lieu) {
                var marker = L.circleMarker([lieu.lat, lieu.lon], {
                    radius: 7,
                    color: '#000',
                    weight: 1,
                    fillColor: couleurSurface(lieu.surface),
                    fillOpacity: 0.9,
                }).addTo(map);
                marker.bindPopup(popupHtml(lieu));
                bornes.push([lieu.lat, lieu.lon]);
            });

            if (bornes.length === 1) {
                map.setView(bornes[0], 12);
            } else {
                map.fitBounds(bornes, { padding: [40, 40] });
            }
        })
        .catch(function (err) {
            console.error('Backcountry — chargement des lieux échoué :', err);
            afficherMessage(t.error || 'Impossible de charger les lieux.');
        });

    // Petit bandeau d'information par-dessus la carte (vide / erreur).
    function afficherMessage(texte) {
        var ctrl = L.control({ position: 'topright' });
        ctrl.onAdd = function () {
            var div = L.DomUtil.create('div', 'bcp-map-msg');
            div.textContent = texte;
            return div;
        };
        ctrl.addTo(map);
    }
}());
