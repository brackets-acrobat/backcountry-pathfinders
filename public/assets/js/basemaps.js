// Fonds de carte partagés par TOUTES les cartes du site (accueil, fiche lieu…).
// Inclut le satellite Esri. Un sélecteur (L.control.layers) est ajouté à chaque
// carte, et le choix de l'utilisateur est mémorisé durablement (localStorage,
// retenu d'une visite à l'autre) puis réappliqué sur toutes les cartes.
(function () {
    'use strict';
    if (typeof L === 'undefined') { return; }

    // Barre d'échelle à trois unités (km, miles, NM). Leaflet ne fournit que le
    // métrique et l'impérial : on étend L.Control.Scale pour ajouter une ligne
    // NM (1 NM = 1852 m). Indépendante du fond : valable sur satellite, CARTO,
    // OSM et OpenTopoMap.
    var ScaleTriple = L.Control.Scale.extend({
        options: { metric: true, imperial: true, nautical: true },
        _addScales: function (options, className, container) {
            L.Control.Scale.prototype._addScales.call(this, options, className, container);
            if (options.nautical) { this._nScale = L.DomUtil.create('div', className, container); }
        },
        _updateScales: function (maxMeters) {
            L.Control.Scale.prototype._updateScales.call(this, maxMeters);
            if (this.options.nautical && maxMeters) { this._updateNautical(maxMeters); }
        },
        _updateNautical: function (maxMeters) {
            var maxNm = maxMeters / 1852;
            if (maxNm < 1) { this._updateScale(this._nScale, '', 0); return; }  // trop zoomé : pas de NM
            var nm = this._getRoundNum(maxNm);
            this._updateScale(this._nScale, nm + ' NM', nm / maxNm);
        },
    });

    var STORAGE_KEY = 'bcp-basemap';
    var attrOsm = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>';
    var attrCarto = ' &copy; <a href="https://carto.com/attributions">CARTO</a>';

    // Définitions canoniques (ordre = ordre dans le sélecteur).
    var DEFS = [
        { key: 'satellite', label: 'Satellite', url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
          opt: { maxZoom: 19, attribution: 'Tuiles &copy; Esri' } },
        { key: 'dark', label: 'Sombre', url: 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png',
          opt: { maxZoom: 19, attribution: attrOsm + attrCarto } },
        { key: 'positron', label: 'Positron', url: 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png',
          opt: { maxZoom: 19, attribution: attrOsm + attrCarto } },
        { key: 'osm', label: 'OpenStreetMap', url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
          opt: { maxZoom: 19, attribution: attrOsm } },
        { key: 'topo', label: 'OpenTopoMap', url: 'https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png',
          opt: { maxZoom: 17, attribution: attrOsm + ' | &copy; <a href="https://opentopomap.org">OpenTopoMap</a> (CC-BY-SA)' } },
    ];

    function lireChoix() {
        try { return window.localStorage.getItem(STORAGE_KEY); } catch (e) { return null; }
    }
    function ecrireChoix(key) {
        try { window.localStorage.setItem(STORAGE_KEY, key); } catch (e) { /* ignore */ }
    }

    // Attache les fonds + un sélecteur à une carte.
    // opts = { defaut: clé par défaut si aucun choix mémorisé, labels: { key: libellé },
    //          position: coin du sélecteur }. Retourne la couche active.
    function attacher(map, opts) {
        opts = opts || {};
        var labels = opts.labels || {};
        var choix = lireChoix() || opts.defaut || 'dark';

        var couches = {};       // libellé affiché -> couche Leaflet
        var keyParLabel = {};   // libellé affiché -> clé canonique
        var couchesParKey = {}; // clé canonique -> couche
        DEFS.forEach(function (d) {
            var lib = labels[d.key] || d.label;
            var couche = L.tileLayer(d.url, d.opt);
            couches[lib] = couche;
            keyParLabel[lib] = d.key;
            couchesParKey[d.key] = couche;
        });

        var active = couchesParKey[choix] || couchesParKey.dark || couchesParKey[DEFS[0].key];
        active.addTo(map);

        L.control.layers(couches, null, { position: opts.position || 'topright' }).addTo(map);

        // Échelle km / mi / NM, valable quel que soit le fond actif.
        new ScaleTriple({ position: 'bottomleft', maxWidth: 120 }).addTo(map);

        // Mémorise le choix pour la session, sur toutes les cartes.
        map.on('baselayerchange', function (e) {
            var key = keyParLabel[e.name];
            if (key) { ecrireChoix(key); }
        });

        return active;
    }

    window.BCPBasemaps = { attacher: attacher, DEFS: DEFS };
}());
