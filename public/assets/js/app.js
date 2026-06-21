// Code front du site Backcountry Pathfinders.

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
