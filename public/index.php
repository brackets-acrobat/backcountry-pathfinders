<?php

declare(strict_types=1);

/*
 * Front controller : point d'entrée unique de l'application.
 * Toutes les URL passent ici (voir .htaccess), puis sont dispatchées
 * vers le bon contrôleur par le routeur.
 */

// URL de base de l'app (ex. "/backcountry"), pour construire les liens et assets.
define('BASE_URL', rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/'));

// --- Autoloading Composer ---
$autoload = dirname(__DIR__) . '/vendor/autoload.php';
if (!is_file($autoload)) {
    // Composer pas encore installé : on affiche une page d'aide plutôt qu'une erreur fatale.
    require dirname(__DIR__) . '/src/Core/setup_page.php';
    exit;
}
require $autoload;
require dirname(__DIR__) . '/src/helpers.php';   // fonctions globales (t(), …)

use App\Core\View;
use App\Models\IpBannie;
use Bramus\Router\Router;

// --- Configuration ---
$config = require dirname(__DIR__) . '/config/config.php';

// Affichage des erreurs en mode debug uniquement.
if (!empty($config['app']['debug'])) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

// --- Base de données ---
// Connexion paresseuse : configurée ici, établie au premier accès réel.
App\Core\Database::configure($config['db']);

// --- CAPTCHA anti-bot (Cloudflare Turnstile) ---
App\Core\Turnstile::configure($config['turnstile'] ?? []);

// --- Géocodage inverse (pays + région à la création d'un lieu) ---
App\Core\Geocodage::configure($config['geocodage'] ?? []);

// --- Envoi d'e-mails (SMTP) : « mot de passe oublié », etc. ---
App\Core\Mailer::configure($config['mail'] ?? []);

// URL absolue du site (pour les liens dans les e-mails). Priorité à la config ;
// sinon déduite de la requête courante (scheme + hôte + BASE_URL).
$urlConfig = trim((string) ($config['app']['url'] ?? ''));
if ($urlConfig !== '') {
    define('SITE_URL', rtrim($urlConfig, '/'));
} else {
    $scheme = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    $hote   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('SITE_URL', $scheme . '://' . $hote . BASE_URL);
}

// --- Session ---
App\Core\Auth::demarrer();

// « Se souvenir de moi » : reconnexion auto depuis le cookie si pas de session.
if (!App\Core\Auth::estConnecte()) {
    App\Models\ConnexionPersistante::tenterReconnexion();
}

// Auto-déconnexion propre si le compte en session n'existe plus (ex. supprimé).
if (App\Core\Auth::estConnecte() && App\Models\Utilisateur::parId(App\Core\Auth::id()) === null) {
    App\Core\Auth::deconnecter();
}

// Vérification des IP bannies (bloquées avant le routage, sauf admins connectés).
if (!App\Core\Auth::estAdmin()) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    if ($ip !== '') {
        try {
            if (IpBannie::estBannie($ip)) {
                http_response_code(403);
                App\Core\Lang::initialiser();
                (new View())->render('errors/403', ['title' => t('error403.heading')]);
                exit;
            }
        } catch (\Throwable) {
            // Table ip_bannies absente si migration pas encore jouée : on laisse passer.
        }
    }
}

// --- Langue (FR/EN) ---
App\Core\Lang::initialiser();

// --- Routes ---
$router = new Router();

// Site web (pages HTML)
$router->get('/', 'App\Controllers\AccueilController@index');
$router->get('/carte', 'App\Controllers\CarteController@index');
$router->get('/presentation', 'App\Controllers\PresentationController@index');
$router->get('/confidentialite', 'App\Controllers\ConfidentialiteController@index');
$router->get('/mentions-legales', 'App\Controllers\MentionsLegalesController@index');
$router->get('/pilotes', 'App\Controllers\PiloteController@index');
$router->get('/pilote/(\d+)', 'App\Controllers\PiloteController@profil');
$router->get('/actualites', 'App\Controllers\ActualiteController@liste');
$router->get('/actualite/(\d+)', 'App\Controllers\ActualiteController@detail');
$router->get('/lieu/(\d+)', 'App\Controllers\LieuController@detail');
$router->post('/lieu/(\d+)/commentaire', 'App\Controllers\LieuController@ajouterCommentaire');
$router->post('/lieu/(\d+)/note', 'App\Controllers\LieuController@enregistrerNote');
$router->post('/lieu/(\d+)/editer', 'App\Controllers\LieuController@editer');

// Vols (consultation, réservée aux connectés)
$router->get('/vol/(\d+)', 'App\Controllers\VolController@detail');

// Administration (réservée au rôle admin)
$router->get('/admin', 'App\Controllers\AdminController@index');
$router->post('/admin/vol/(\d+)/supprimer',         'App\Controllers\AdminController@supprimerVol');
$router->post('/admin/lieu/(\d+)/supprimer',        'App\Controllers\AdminController@supprimerLieu');
$router->post('/admin/commentaire/(\d+)/supprimer', 'App\Controllers\AdminController@supprimerCommentaire');
$router->post('/admin/note/(\d+)/supprimer',        'App\Controllers\AdminController@supprimerNote');
$router->post('/admin/pilote/(\d+)/supprimer',      'App\Controllers\AdminController@supprimerPilote');
$router->post('/admin/pilote/(\d+)/bannir',         'App\Controllers\AdminController@bannirPilote');
$router->post('/admin/actualite',                   'App\Controllers\AdminController@enregistrerActualite');
$router->post('/admin/actualite/image',             'App\Controllers\AdminController@televerserImage');
$router->post('/admin/actualite/(\d+)/supprimer',   'App\Controllers\AdminController@supprimerActualite');

// Données de la carte (JSON public)
$router->get('/api/lieux', 'App\Controllers\CarteController@lieux');

// Images de spots (servies depuis storage/uploads, hors docroot)
$router->get('/uploads/([\w.\-]+)', 'App\Controllers\UploadController@serve');

// Changement de langue
$router->get('/langue/(\w+)', 'App\Controllers\LangController@changer');

// Authentification
$router->get('/inscription',  'App\Controllers\AuthController@formulaireInscription');
$router->post('/inscription', 'App\Controllers\AuthController@inscription');
$router->get('/connexion',    'App\Controllers\AuthController@formulaireConnexion');
$router->post('/connexion',   'App\Controllers\AuthController@connexion');
$router->get('/deconnexion',  'App\Controllers\AuthController@deconnexion');

// Mot de passe oublié + réinitialisation par lien e-mail.
$router->get('/mot-de-passe-oublie',  'App\Controllers\AuthController@formulaireMotDePasseOublie');
$router->post('/mot-de-passe-oublie', 'App\Controllers\AuthController@envoyerLienReinit');
$router->get('/reinitialiser/([a-f0-9]{64})',  'App\Controllers\AuthController@formulaireReinit');
$router->post('/reinitialiser/([a-f0-9]{64})', 'App\Controllers\AuthController@reinitialiser');

// Double authentification (TOTP) des administrateurs
$router->get('/connexion/2fa',            'App\Controllers\AuthController@formulaire2fa');
$router->post('/connexion/2fa',           'App\Controllers\AuthController@verifier2fa');
$router->get('/connexion/2fa/configurer', 'App\Controllers\AuthController@formulaire2faSetup');
$router->post('/connexion/2fa/configurer','App\Controllers\AuthController@activer2fa');

// Espace « Mon compte » — réservé aux connectés
$router->get('/compte',                 'App\Controllers\CompteController@index');
$router->get('/mes-lieux',              'App\Controllers\CompteController@mesLieux');
$router->post('/mes-lieux/editer',      'App\Controllers\CompteController@editerLieu');
$router->get('/mes-vols',               'App\Controllers\CompteController@mesVols');
$router->post('/mes-vols/supprimer',    'App\Controllers\CompteController@supprimerVol');
$router->post('/compte/profil',         'App\Controllers\CompteController@majProfil');
$router->post('/compte/motdepasse',     'App\Controllers\CompteController@majMotDePasse');
$router->post('/compte/avatar',         'App\Controllers\CompteController@majAvatar');
$router->post('/compte/cles',           'App\Controllers\CompteController@creerCle');
$router->post('/compte/cles/supprimer', 'App\Controllers\CompteController@supprimerCle');

// API desktop (JSON) — reçoit un vol entier (posers groupés), auth par clé API
$router->post('/api/vol', 'App\Api\VolController@store');

// Page 404
$router->set404(function () {
    http_response_code(404);
    (new View())->render('errors/404', ['title' => t('page.404.title')]);
});

$router->run();
