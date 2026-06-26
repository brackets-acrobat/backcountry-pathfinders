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
