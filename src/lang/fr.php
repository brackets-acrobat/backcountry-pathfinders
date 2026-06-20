<?php

declare(strict_types=1);

/*
 * Traductions françaises.
 * Chaque nouvelle fonctionnalité ajoute ses clés ici ET dans en.php.
 */

return [
    // Navigation
    'nav.map'      => 'Carte',
    'nav.login'    => 'Connexion',
    'nav.register' => 'Inscription',
    'nav.logout'   => 'Déconnexion',
    'lang.switch'  => 'Changer de langue',

    // Pied de page
    'footer.tagline' => 'Backcountry Pathfinders community — relevés de lieux de poser MSFS 2024',

    // Libellés communs
    'common.email'    => 'E-mail',
    'common.password' => 'Mot de passe',
    'common.pseudo'   => 'Pseudo',

    // Page « Carte »
    'page.map.title'  => 'Carte des lieux',
    'map.heading'     => 'Carte des lieux de poser',
    'map.intro'       => 'Bienvenue sur le squelette du site communautaire. La carte interactive (Leaflet) et les fiches de lieux seront ajoutées dans les prochaines étapes.',
    'map.placeholder' => 'La carte s\'affichera ici',
    'map.loading'     => 'Chargement des lieux…',
    'map.empty'       => 'Aucun lieu pour le moment.',
    'map.error'       => 'Impossible de charger les lieux.',
    'map.surveys'     => 'Relevés',
    'map.rating'      => 'Note',
    'map.difficulty'  => 'Difficulté',
    'map.altitude'    => 'Altitude',
    'map.detail'      => 'Voir le détail',
    'map.layer_dark'  => 'Sombre',

    // Fiche détail d'un lieu
    'place.untitled'         => 'Lieu sans nom',
    'place.back_to_map'      => 'Retour à la carte',
    'place.surveys_heading'  => 'Relevés',
    'place.no_surveys'       => 'Aucun relevé pour ce lieu.',
    'place.comments_heading' => 'Commentaires',
    'place.no_comments'      => 'Aucun commentaire pour le moment.',
    'place.deleted_user'     => 'Utilisateur supprimé',
    // Contribution (connecté) : noter + commenter
    'place.your_review'        => 'Votre avis',
    'place.rating_hint'        => 'Note d\'appréciation et difficulté du poser (1 à 5 étoiles).',
    'place.save_rating'        => 'Enregistrer ma note',
    'place.add_comment'        => 'Ajouter un commentaire',
    'place.comment_placeholder' => 'Partage ton expérience sur ce lieu…',
    'place.comment_submit'     => 'Publier',
    'place.login_to_contribute' => 'Connecte-toi pour noter ce lieu et laisser un commentaire.',
    'place.comment_added'      => 'Commentaire publié.',
    'place.rating_saved'       => 'Note enregistrée.',

    // Champs d'un relevé
    'survey.surface'        => 'Surface',
    'survey.condition'      => 'État du sol',
    'survey.friction'       => 'Friction',
    'survey.usable_length'  => 'Longueur utile',
    'survey.max_slope'      => 'Pente max',
    'survey.elevation_gain' => 'Dénivelé',
    'survey.heading'        => 'Cap au poser',
    'survey.aircraft'       => 'Aéronef',
    'survey.relief_profile' => 'Profil de relief',

    // Types de surface (MSFS)
    'surface.grass'    => 'Herbe',
    'surface.dirt'     => 'Terre',
    'surface.sand'     => 'Sable',
    'surface.snow'     => 'Neige',
    'surface.ice'      => 'Glace',
    'surface.water'    => 'Eau',
    'surface.concrete' => 'Béton',
    'surface.asphalt'  => 'Asphalte',
    'surface.unknown'  => 'Inconnu',

    // Page 404
    'page.404.title'   => 'Page introuvable',
    'error404.heading' => '404 — Page introuvable',
    'error404.text'    => 'La page demandée n\'existe pas (ou plus).',
    'error404.back'    => 'Retour à la carte',

    // Inscription
    'page.register.title'   => 'Inscription',
    'register.heading'      => 'Créer un compte',
    'register.password_hint' => '(8 caractères minimum)',
    'register.confirm'      => 'Confirmer le mot de passe',
    'register.submit'       => 'Créer mon compte',
    'register.have_account' => 'Déjà inscrit ?',
    'register.login_link'   => 'Se connecter',

    // Connexion
    'page.login.title'    => 'Connexion',
    'login.heading'       => 'Connexion',
    'login.submit'        => 'Se connecter',
    'login.no_account'    => 'Pas encore de compte ?',
    'login.register_link' => 'Créer un compte',

    // Navigation (suite)
    'nav.account' => 'Mon compte',

    // Espace compte / clés API
    'page.account.title'        => 'Mon compte',
    'account.heading'           => 'Mon compte',
    'account.api_section'       => 'Clés API',
    'account.api_intro'         => 'Génère une clé API et renseigne-la dans l\'application desktop pour qu\'elle envoie tes relevés vers le site.',
    'account.new_key_placeholder' => 'Nom de la clé (ex. « PC salon »)',
    'account.create_key'        => 'Générer une clé',
    'account.key_created_warning' => 'Copie cette clé maintenant : pour des raisons de sécurité, elle ne sera plus jamais affichée.',
    'account.copy'              => 'Copier',
    'account.no_keys'           => 'Aucune clé pour le moment.',
    'account.col_label'         => 'Nom',
    'account.col_created'       => 'Créée le',
    'account.col_last_used'     => 'Dernière utilisation',
    'account.never_used'        => 'Jamais',
    'account.unnamed'           => 'sans nom',
    'account.delete_key'        => 'Supprimer',
    'account.delete_confirm'    => 'Supprimer cette clé ? L\'application qui l\'utilise ne pourra plus envoyer de relevés.',

    // Messages d'erreur (validation)
    'error.csrf'             => 'Session expirée, merci de réessayer.',
    'error.pseudo_length'    => 'Le pseudo doit faire entre 3 et 40 caractères.',
    'error.email_invalid'    => 'Adresse e-mail invalide.',
    'error.password_short'   => 'Le mot de passe doit faire au moins 8 caractères.',
    'error.password_mismatch' => 'Les deux mots de passe ne correspondent pas.',
    'error.duplicate'        => 'Ce pseudo ou cet e-mail est déjà utilisé.',
    'error.login_failed'     => 'E-mail ou mot de passe incorrect.',
    'error.captcha'          => 'Vérification anti-robot échouée, merci de réessayer.',
    'error.comment_empty'    => 'Le commentaire ne peut pas être vide.',
    'error.rating_invalid'   => 'Note invalide (1 à 5).',
    'error.rating_empty'     => 'Choisis au moins une note ou une difficulté.',
];
