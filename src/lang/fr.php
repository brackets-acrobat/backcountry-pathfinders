<?php

declare(strict_types=1);

/*
 * Traductions françaises.
 * Chaque nouvelle fonctionnalité ajoute ses clés ici ET dans en.php.
 */

return [
    // Navigation
    'nav.menu'         => 'Menu',
    'nav.user_section' => 'Utilisateur',
    'nav.admin'        => 'Administration',
    'nav.home'     => 'Accueil',
    'nav.map'      => 'Carte',
    'nav.pilots'   => 'Liste des pilotes',
    'nav.login'    => 'Connexion',
    'nav.register' => 'Inscription',
    'nav.logout'   => 'Déconnexion',
    'lang.switch'  => 'Changer de langue',

    // Pied de page
    'footer.tagline' => 'Backcountry Pathfinders community — relevés de lieux d\'atterrissage MSFS 2024',

    // Libellés communs
    'common.email'    => 'E-mail',
    'common.password' => 'Mot de passe',
    'common.pseudo'   => 'Pseudo',

    // Page d'accueil
    'page.home.title' => 'Accueil',
    'home.title'      => 'Backcountry Pathfinders',
    'home.subtitle'   => 'La communauté du backcountry sous MSFS 2024 : relevés de lieux d\'atterrissage, partage de vols et carte interactive.',
    'home.cta_map'    => 'Voir la carte',
    'home.letter_hi'  => "Salut,",
    'home.letter_p1'  => "Moi c’est Jim « Ridge » Vance. On m’a donné ce surnom parce que j’en ai franchi quelques-unes, de ces satanées crêtes ! Durant toute ma vie de pilote de brousse, j’ai consigné sur mon carnet de vols tous les endroits sauvages où je me suis posé. Et ça m’a bien servi quand il a fallu que j’atterrisse en urgence pour me sortir de situations difficiles… Mais comme mon carnet était sur papier, je n’ai pas pu en faire profiter d’autres pilotes.",
    'home.letter_p2'  => "Mon jeune ami Sitka, qui s’y connait bien mieux que moi, a créé ce site internet où tu pourras consigner tous tes vols de brousses, prendre des photos, écrire des commentaires sur tes vols ou ceux d’autres pilotes. Fais-en bon usage,",
    'home.letter_bye' => "Bons vols et fly safe !",

    // Page « Carte »
    'page.map.title'  => 'Carte des lieux',
    'map.heading'     => 'Carte des lieux d\'atterrissage',
    'map.intro'       => 'Bienvenue sur le squelette du site communautaire. La carte interactive (Leaflet) et les fiches de lieux seront ajoutées dans les prochaines étapes.',
    'map.placeholder' => 'La carte s\'affichera ici',
    'map.loading'     => 'Chargement des lieux…',
    'map.empty'       => 'Aucun lieu pour le moment.',
    'map.error'       => 'Impossible de charger les lieux.',
    'map.surveys'     => 'Relevés',
    'map.rating'      => 'Note',
    'map.difficulty'  => 'Difficulté',
    'map.altitude'    => 'Altitude',
    'map.country'     => 'Pays',
    'map.detail'      => 'Voir le détail',
    'map.layer_dark'  => 'Sombre',

    // Fiche détail d'un lieu
    'place.untitled'         => 'Lieu sans nom',
    'place.back_to_map'      => 'Retour à la carte',
    'place.back'             => 'Page précédente',
    'place.edit'             => 'Modifier le lieu',
    'moderation.comment'     => 'Commentaire modéré',

    // Administration
    'page.admin.title'       => 'Administration',
    'admin.heading'          => 'Activité récente',
    'admin.empty'            => 'Aucune activité pour le moment.',
    'admin.filter_all'          => 'Toute l\'activité',
    'admin.filter_membre'       => 'Nouveaux membres',
    'admin.filter_vol'          => 'Vols',
    'admin.filter_lieu'         => 'Lieux',
    'admin.filter_commentaire'  => 'Commentaires',
    'admin.filter_note'         => 'Notes',

    // Double authentification (TOTP)
    'page.2fa.title'         => 'Double authentification',
    'page.2fa_setup.title'   => 'Configurer la double authentification',
    '2fa.heading'            => 'Double authentification',
    '2fa.intro'              => 'Saisissez le code à 6 chiffres affiché par votre application d\'authentification.',
    '2fa.code_label'         => 'Code de vérification',
    '2fa.submit'             => 'Vérifier',
    '2fa.back'               => 'Revenir à la connexion',
    '2fa.setup_heading'      => 'Sécurisez votre compte administrateur',
    '2fa.setup_intro'        => 'Votre compte administrateur exige une double authentification. Configurez-la maintenant :',
    '2fa.step_app'           => 'Installez une application d\'authentification (Google Authenticator, Microsoft Authenticator, FreeOTP…).',
    '2fa.step_scan'          => 'Scannez ce QR code (ou saisissez la clé manuellement) :',
    '2fa.secret_label'       => 'Clé manuelle',
    '2fa.step_confirm'       => 'Saisissez le code généré pour activer la double authentification :',
    '2fa.activate'           => 'Activer',
    'error.2fa_invalid'      => 'Code incorrect. Réessayez.',
    'admin.by'               => 'par',
    'admin.on'               => 'sur',
    'admin.ev_membre'        => 'Nouveau membre',
    'admin.ev_vol'           => 'Nouveau vol',
    'admin.ev_lieu'          => 'Nouveau lieu',
    'admin.ev_commentaire'   => 'Nouveau commentaire',
    'admin.ev_note'          => 'Nouvelle note',
    'place.surveys_heading'  => 'Relevés',
    'place.no_surveys'       => 'Aucun relevé pour ce lieu.',
    'place.comments_heading' => 'Commentaires',
    'place.no_comments'      => 'Aucun commentaire pour le moment.',
    'place.deleted_user'     => 'Utilisateur supprimé',
    'place.pilot_comments_heading' => 'Commentaires des pilotes',
    'place.comment_by'       => 'Commentaire de',
    // Contribution (connecté) : noter + commenter
    'place.your_review'        => 'Votre avis',
    'place.rating_hint'        => 'Note d\'appréciation et difficulté de l\'atterrissage (1 à 5 étoiles).',
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
    'survey.touchdown_speed' => 'Vitesse au toucher',
    'survey.roll_distance'  => 'Distance de roulage',
    'survey.friction'       => 'Friction',
    'survey.max_slope'      => 'Pente max',
    'survey.elevation_gain' => 'Dénivelé',
    'survey.heading'        => 'Cap à l\'atterrissage',
    'survey.aircraft'       => 'Aéronef',
    'survey.relief_profile' => 'Profil de relief',
    'survey.photo'          => 'Photo du spot',

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
    'register.password_hint' => '(8 car. min. : 1 majuscule, 1 minuscule, 1 chiffre, 1 caractère spécial)',
    'register.confirm'      => 'Confirmer le mot de passe',
    'register.submit'       => 'Créer mon compte',
    'register.have_account' => 'Déjà inscrit ?',
    'register.login_link'   => 'Se connecter',

    // Connexion
    'page.login.title'    => 'Connexion',
    'login.heading'       => 'Connexion',
    'login.submit'        => 'Se connecter',
    'login.remember'      => 'Se souvenir de moi (1 mois)',
    'login.no_account'    => 'Pas encore de compte ?',
    'login.register_link' => 'Créer un compte',

    // Navigation (suite)
    'nav.account' => 'Mon compte',
    'nav.my_places' => 'Mes lieux visités',
    'nav.my_flights' => 'Mes vols',

    // Mes lieux visités
    'page.my_places.title' => 'Mes lieux visités',
    'myplaces.heading'     => 'Mes lieux visités',
    'myplaces.empty'       => 'Tu n\'as encore visité aucun lieu. Pose-toi quelque part et envoie un relevé depuis l\'application !',
    'myplaces.surveys'     => 'relevé(s)',
    'myplaces.last_visit'  => 'dernière visite :',
    'myplaces.rename'      => 'Éditer ce lieu',
    'myplaces.rename_placeholder' => 'Nom du lieu',
    'myplaces.comment_label' => 'Ton commentaire sur ce lieu',
    'myplaces.comment_placeholder' => 'Ton commentaire sur ce lieu (conseils, dangers, météo…)',
    'myplaces.rename_save' => 'Enregistrer',
    'myplaces.saved'       => 'Tes modifications ont été enregistrées.',

    // Liste des pilotes
    'page.pilots.title' => 'Pilotes',
    'pilots.heading'    => 'Liste des pilotes',
    'pilots.empty'      => 'Aucun pilote pour le moment.',
    'pilots.places'     => 'lieu(x)',
    'pilots.surveys'    => 'relevé(s)',
    'pilots.flights'    => 'vol(s)',
    'pilots.since'      => 'membre depuis',
    'pilots.back'       => 'Retour à la liste des pilotes',

    // Profil public d'un pilote
    'profil.flights_heading' => 'Ses vols',
    'profil.no_flights'      => 'Ce pilote n\'a pas encore envoyé de vol.',
    'profil.awards'          => 'Écussons',

    // Mes vols
    'page.my_flights.title'   => 'Mes vols',
    'myflights.heading'       => 'Mes vols',
    'myflights.empty'         => 'Tu n\'as encore envoyé aucun vol. Pose-toi quelque part et envoie ton vol depuis l\'application !',
    'myflights.delete'        => 'Supprimer ce vol',
    'myflights.delete_warn'   => 'Le vol et ses atterrissages seront supprimés. Les lieux partagés encore visités par d\'autres pilotes sont conservés ; ceux qui n\'auraient plus aucun relevé sont effacés. Action irréversible.',
    'myflights.delete_confirm' => 'Confirmer la suppression',
    'myflights.deleted'       => 'Le vol a été supprimé.',

    // Détail d'un vol
    'flight.detail_title' => 'Détail du vol',
    'flight.back'         => 'Page précédente',
    'flight.no_route'     => 'Vol',
    'flight.time'         => 'Temps de vol',
    'flight.landings'     => 'atterrissage(s)',
    'flight.no_landings'  => 'Aucun atterrissage dans ce vol.',
    'flight.landing_n'    => 'Atterrissage {n}',
    'flight.photo_alt'    => 'Capture de l\'atterrissage',
    'flight.touch_speed'  => 'Vitesse au toucher',
    'flight.roll_dist'    => 'Roulage',
    'flight.see_place'    => 'Voir le lieu',

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

    // Mon compte — profil / mot de passe / avatar
    'account.profile_section'   => 'Profil',
    'account.profile_intro'     => 'Ton pseudo s\'affiche sur tous les lieux et commentaires que tu publies.',
    'account.save_profile'      => 'Enregistrer le profil',
    'account.profile_saved'     => 'Profil mis à jour.',
    'account.password_section'  => 'Mot de passe',
    'account.new_password'      => 'Nouveau mot de passe',
    'account.confirm_password'  => 'Confirmer le mot de passe',
    'account.save_password'     => 'Changer le mot de passe',
    'account.password_saved'    => 'Mot de passe mis à jour.',
    'account.avatar_section'    => 'Avatar',
    'account.avatar_intro'      => 'Image PNG ou JPG, 500 × 500 px maximum, 500 Ko maximum.',
    'account.avatar_choose'     => 'Choisir une image',
    'account.save_avatar'       => 'Mettre à jour l\'avatar',
    'account.avatar_saved'      => 'Avatar mis à jour.',
    'account.no_avatar'         => 'Aucun avatar pour le moment.',

    // Messages d'erreur (validation)
    'error.csrf'             => 'Session expirée, merci de réessayer.',
    'error.pseudo_length'    => 'Le pseudo doit faire entre 3 et 40 caractères.',
    'error.email_invalid'    => 'Adresse e-mail invalide.',
    'error.password_short'   => 'Le mot de passe doit faire au moins 8 caractères.',
    'error.password_weak'    => 'Le mot de passe doit faire 8 caractères minimum, avec au moins une majuscule, une minuscule, un chiffre et un caractère spécial.',
    'error.password_mismatch' => 'Les deux mots de passe ne correspondent pas.',
    'error.duplicate'        => 'Ce pseudo ou cet e-mail est déjà utilisé.',
    'error.pseudo_taken'     => 'Ce pseudo est déjà pris.',
    'error.email_taken'      => 'Cet e-mail est déjà utilisé.',
    'error.avatar_required'  => 'Aucune image sélectionnée.',
    'error.avatar_failed'    => 'Échec du téléversement de l\'image.',
    'error.avatar_size'      => 'Image trop lourde (500 Ko maximum).',
    'error.avatar_type'      => 'Format non autorisé (PNG ou JPG uniquement).',
    'error.avatar_dims'      => 'Image trop grande (500 × 500 px maximum).',
    'error.place_name_length' => 'Le nom du lieu ne peut pas dépasser 120 caractères.',
    'error.place_not_yours'  => 'Tu ne peux renommer qu\'un lieu que tu as visité.',
    'error.flight_not_yours' => 'Tu ne peux supprimer qu\'un de tes propres vols.',
    'error.login_failed'     => 'E-mail ou mot de passe incorrect.',
    'error.captcha'          => 'Vérification anti-robot échouée, merci de réessayer.',
    'error.comment_empty'    => 'Le commentaire ne peut pas être vide.',
    'error.rating_invalid'   => 'Note invalide (1 à 5).',
    'error.rating_empty'     => 'Choisis au moins une note ou une difficulté.',
];
