// Éditeurs TinyMCE du formulaire « Nouvelle actualité » (admin).
// Chapô = mise en forme simple ; Contenu = riche (image téléversée, vidéo par URL),
// limité à window.ACTU.maxContenu caractères de texte visible.
(function () {
    if (typeof tinymce === 'undefined' || !window.ACTU) return;

    var MAX = window.ACTU.maxContenu || 3500;

    // Upload d'image → endpoint admin (renvoie { location }). CSRF en champ POST.
    function uploadImage(blobInfo) {
        return new Promise(function (resolve, reject) {
            var fd = new FormData();
            fd.append('csrf', window.ACTU.csrf);
            fd.append('file', blobInfo.blob(), blobInfo.filename());
            fetch(window.ACTU.uploadUrl, { method: 'POST', body: fd })
                .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
                .then(function (res) {
                    if (res.ok && res.j && res.j.location) resolve(res.j.location);
                    else reject({ message: (res.j && res.j.error) || 'upload', remove: true });
                })
                .catch(function () { reject({ message: 'network', remove: true }); });
        });
    }

    // Contenu : riche, image téléversée + vidéo par URL (plugin media), compteur.
    tinymce.init({
        selector: '#actu-contenu',
        license_key: 'gpl',
        base_url: window.ACTU.tinymceBase,   // auto-hébergé : plugins/skins en local
        suffix: '.min',
        skin: 'oxide-dark',                  // accord avec le thème sombre du site
        content_css: 'dark',
        convert_urls: false,                 // garde l'URL racine /…/uploads/… (sinon relative → rejetée + cassée sur la page publique)
        menubar: false,
        branding: false,
        promotion: false,
        height: 380,
        plugins: 'lists link autolink image media table wordcount',
        toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright | '
               + 'bullist numlist | link image media table | removeformat',
        content_style: 'body{font-family:system-ui,Segoe UI,Roboto,sans-serif;font-size:15px}',
        images_upload_handler: uploadImage,
        automatic_uploads: true,
        // Vidéos : on autorise l'iframe d'intégration (YouTube/Vimeo).
        media_alt_source: false,
        media_poster: false,
        setup: function (editor) {
            function maj() {
                var n = editor.plugins.wordcount
                    ? editor.plugins.wordcount.body.getCharacterCount()
                    : editor.getContent({ format: 'text' }).length;
                var el = document.getElementById('actu-count');
                if (el) {
                    el.textContent = n;
                    el.parentElement.classList.toggle('is-over', n > MAX);
                }
            }
            editor.on('init input keyup change SetContent', maj);
        }
    });

    // Soumission : on attend que TOUTES les images soient téléversées (blob: →
    // /uploads/...) AVANT d'envoyer, sinon le <img> temporaire est rejeté par
    // l'assainisseur côté serveur. On contrôle aussi la limite de caractères.
    var form = document.getElementById('actu-form');
    var enCours = false;
    if (form) {
        form.addEventListener('submit', function (e) {
            if (enCours) return;                  // laisse passer l'envoi programmatique
            var ed = tinymce.get('actu-contenu');
            if (!ed) return;
            e.preventDefault();

            ed.uploadImages().then(function () {
                ed.save();                        // recopie le contenu (URLs finales) dans la textarea
                var n = ed.plugins.wordcount
                    ? ed.plugins.wordcount.body.getCharacterCount()
                    : ed.getContent({ format: 'text' }).length;
                if (n > MAX) {
                    var el = document.getElementById('actu-count');
                    if (el) el.parentElement.classList.add('is-over');
                    ed.focus();
                    return;
                }
                enCours = true;
                form.submit();
            }).catch(function () {
                enCours = true;
                form.submit();                    // en cas d'échec d'upload, on envoie quand même
            });
        });
    }
}());
