// Code front du site Backcountry Pathfinders.

// --- Menu hamburger (en-tête, tablette/mobile) ---
(function () {
    var header = document.querySelector('.site-header');
    var btn = document.getElementById('nav-toggle');
    if (!header || !btn) return;

    function setOpen(open) {
        header.classList.toggle('nav-open', open);
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        setOpen(!header.classList.contains('nav-open'));
    });
    // Clic en dehors de l'en-tête → ferme.
    document.addEventListener('click', function (e) {
        if (header.classList.contains('nav-open') && !header.contains(e.target)) {
            setOpen(false);
        }
    });
    // Échap → ferme.
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') setOpen(false);
    });
})();

// --- Menu utilisateur (avatar en haut à droite) ---
(function () {
    var btn = document.getElementById('user-menu-btn');
    var menu = document.getElementById('user-dropdown');
    if (!btn || !menu) return;

    function ouvrir() {
        menu.hidden = false;
        btn.setAttribute('aria-expanded', 'true');
    }
    function fermer() {
        menu.hidden = true;
        btn.setAttribute('aria-expanded', 'false');
    }

    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        if (menu.hidden) { ouvrir(); } else { fermer(); }
    });
    // Clic en dehors → ferme.
    document.addEventListener('click', function (e) {
        if (!menu.hidden && !menu.contains(e.target) && e.target !== btn) {
            fermer();
        }
    });
    // Échap → ferme.
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') fermer();
    });
})();

// --- Lien « Page précédente » robuste ---
// Mémorise la vraie page d'origine pour ne pas être trompé par les POST/redirect
// (enregistrement, commentaire, note) qui rechargent la page courante.
(function () {
    var link = document.querySelector('a.js-back');
    if (!link) return;

    var key = 'retour:' + location.pathname;

    // On enregistre le référent SEULEMENT s'il vient d'une autre page (pas de la
    // page courante ni de ses actions POST qui y reviennent).
    try {
        var ref = document.referrer;
        if (ref && ref.indexOf(location.origin) === 0
            && ref.slice(location.origin.length).indexOf(location.pathname) !== 0) {
            sessionStorage.setItem(key, ref);
        }
    } catch (e) {}

    link.addEventListener('click', function (e) {
        var cible = null;
        try { cible = sessionStorage.getItem(key); } catch (e2) {}
        if (cible) {                 // on a retenu la page d'origine → on y va
            e.preventDefault();
            location.href = cible;
        }
        // sinon : on suit le href de repli (carte / profil pilote)
    });
})();

// --- Bandeau de consentement aux cookies ---
// Cookie de premier niveau `bcp_consent` = "accepted" | "refused", mémorisé 6 mois.
// Tant qu'aucun choix n'est fait, le bandeau reste affiché. Les scripts tiers
// non essentiels (analytics futurs, etc.) peuvent lire window.bcpConsent.get()
// ou écouter l'événement `bcp:consent` pour se déclencher seulement si accepté.
(function () {
    var NAME = 'bcp_consent';
    var MAX_AGE = 60 * 60 * 24 * 182; // ~6 mois, en secondes

    function lire() {
        var m = document.cookie.match(/(?:^|;\s*)bcp_consent=([^;]+)/);
        return m ? decodeURIComponent(m[1]) : null;
    }
    function ecrire(valeur) {
        var secure = location.protocol === 'https:' ? '; Secure' : '';
        document.cookie = NAME + '=' + encodeURIComponent(valeur) +
            '; Max-Age=' + MAX_AGE + '; Path=/; SameSite=Lax' + secure;
    }

    // API publique pour d'éventuels scripts tiers à venir.
    window.bcpConsent = {
        get: lire,
        accepted: function () { return lire() === 'accepted'; }
    };

    var banner = document.getElementById('cookie-banner');

    function afficher() { if (banner) banner.hidden = false; }
    function masquer() { if (banner) banner.hidden = true; }

    function definir(valeur) {
        ecrire(valeur);
        masquer();
        try {
            document.dispatchEvent(new CustomEvent('bcp:consent', { detail: valeur }));
        } catch (e) {}
    }

    // Affichage initial : seulement si aucun choix n'a encore été mémorisé.
    if (banner && lire() === null) afficher();

    if (banner) {
        var accept = banner.querySelector('.js-cookie-accept');
        var refuse = banner.querySelector('.js-cookie-refuse');
        if (accept) accept.addEventListener('click', function () { definir('accepted'); });
        if (refuse) refuse.addEventListener('click', function () { definir('refused'); });
    }

    // Lien « Gérer les cookies » du pied : ré-ouvre le bandeau pour changer d'avis.
    var manage = document.querySelector('.js-cookie-manage');
    if (manage) {
        manage.addEventListener('click', function (e) {
            e.preventDefault();
            afficher();
            if (banner) banner.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    }
})();

// --- Tri client des tableaux .js-sortable (liste des pilotes…) ---
// Chaque <th data-sort-type="text|number"> est cliquable : 1er clic = croissant,
// 2e clic = décroissant. Les cellules portent data-sort-value (valeur de tri).
(function () {
    var tables = document.querySelectorAll('table.js-sortable');
    Array.prototype.forEach.call(tables, function (table) {
        var tbody = table.tBodies[0];
        if (!tbody) return;
        var headers = table.querySelectorAll('thead th[data-sort-type]');

        function cellValue(cell, type) {
            if (!cell) return type === 'number' ? 0 : '';
            var raw = cell.getAttribute('data-sort-value');
            if (raw === null) raw = cell.textContent;
            if (type === 'number') {
                var n = parseFloat(raw);
                return isNaN(n) ? 0 : n;
            }
            return raw.trim().toLowerCase();
        }

        function sortBy(th, dir) {
            var col = th.cellIndex;
            var type = th.getAttribute('data-sort-type');
            var rows = Array.prototype.slice.call(tbody.rows);
            rows.sort(function (a, b) {
                var va = cellValue(a.cells[col], type);
                var vb = cellValue(b.cells[col], type);
                if (va < vb) return dir === 'asc' ? -1 : 1;
                if (va > vb) return dir === 'asc' ? 1 : -1;
                return 0;
            });
            rows.forEach(function (r) { tbody.appendChild(r); });
            Array.prototype.forEach.call(headers, function (h) {
                h.setAttribute('aria-sort',
                    h === th ? (dir === 'asc' ? 'ascending' : 'descending') : 'none');
            });
        }

        Array.prototype.forEach.call(headers, function (th) {
            th.addEventListener('click', function () {
                var dir = th.getAttribute('aria-sort') === 'ascending' ? 'desc' : 'asc';
                sortBy(th, dir);
            });
            th.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    th.click();
                }
            });
        });
    });
})();
