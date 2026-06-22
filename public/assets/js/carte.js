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

    // Cinq étoiles pleines (réutilisées pour le fond gris et le remplissage coloré).
    var CINQ_ETOILES = '';
    for (var _i = 0; _i < 5; _i++) { CINQ_ETOILES += '<i class="ph-fill ph-star"></i>'; }

    // Moyenne en étoiles fractionnaires (façon Amazon), comme la fiche détail :
    // fond gris + remplissage coloré clippé à la largeur = moyenne/5.
    // `variante` = '' (vert), 'is-orange' ou 'is-red'.
    function etoiles(valeur, variante) {
        var v = Math.max(0, Math.min(5, valeur));
        var pct = (v / 5 * 100).toFixed(1);
        var cls = 'stars-avg' + (variante ? ' ' + variante : '');
        return '<span class="' + cls + '" role="img" aria-label="' + v.toFixed(1) + '/5">' +
            '<span class="stars-base">' + CINQ_ETOILES + '</span>' +
            '<span class="stars-fill" style="width:' + pct + '%" aria-hidden="true">' + CINQ_ETOILES + '</span>' +
            '</span>';
    }

    // Palier de couleur de la difficulté : vert ≤2,33 / orange ≤3,67 / rouge au-delà.
    function couleurDifficulte(d) {
        if (d <= 2.33) { return ''; }
        if (d <= 3.67) { return 'is-orange'; }
        return 'is-red';
    }

    function popupHtml(lieu) {
        var nom = lieu.nom ? escapeHtml(lieu.nom) : (t.untitled || 'Lieu') + ' #' + lieu.id;
        var html = '<div class="bcp-popup"><strong>' + nom + '</strong>';

        var lignes = [];
        if (lieu.pays) {
            lignes.push((t.country || 'Pays') + ' : ' + escapeHtml(lieu.pays));
        }
        if (lieu.surface) {
            lignes.push((t.surface || 'Surface') + ' : ' + escapeHtml(libelleSurface(lieu.surface)));
        }
        if (lieu.altitude_m !== null && lieu.altitude_m !== undefined) {
            // Stockage en mètres, affichage en pieds (aéronautique).
            var altFt = Math.round(lieu.altitude_m * 3.280839895);
            lignes.push((t.altitude || 'Alt.') + ' : ' + altFt + ' ft');
        }
        lignes.push((t.surveys || 'Relevés') + ' : ' + lieu.nb_releves);
        if (lieu.note_moyenne !== null && lieu.note_moyenne !== undefined) {
            lignes.push((t.rating || 'Note') + ' : ' + etoiles(lieu.note_moyenne, '') +
                ' (' + lieu.note_moyenne.toFixed(1) + ')');
        }
        if (lieu.difficulte_moyenne !== null && lieu.difficulte_moyenne !== undefined) {
            lignes.push((t.difficulty || 'Difficulté') + ' : ' +
                etoiles(lieu.difficulte_moyenne, couleurDifficulte(lieu.difficulte_moyenne)) +
                ' (' + lieu.difficulte_moyenne.toFixed(2) + ')');
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

            // Regroupement des marqueurs proches (Leaflet.markercluster) :
            // pastille verte numérotée quand les points se chevauchent, qui
            // éclate en marqueurs individuels au zoom.
            var clusters = L.markerClusterGroup({
                showCoverageOnHover: false,
                maxClusterRadius: 50,
                iconCreateFunction: function (cluster) {
                    var n = cluster.getChildCount();
                    return L.divIcon({
                        html: '<div class="bcp-cluster"><span>' + n + '</span></div>',
                        className: 'bcp-cluster-wrap',
                        iconSize: L.point(38, 38),
                    });
                },
            });

            var bornes = [];
            lieux.forEach(function (lieu) {
                var marker = L.circleMarker([lieu.lat, lieu.lon], {
                    radius: 4,
                    color: '#000',
                    weight: 1,
                    fillColor: couleurSurface(lieu.surface),
                    fillOpacity: 0.9,
                });
                marker.bindPopup(popupHtml(lieu));
                clusters.addLayer(marker);
                bornes.push([lieu.lat, lieu.lon]);
            });
            map.addLayer(clusters);

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
