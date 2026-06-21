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

// --- Session ---
App\Core\Auth::demarrer();

// Auto-déconnexion propre si le compte en session n'existe plus (ex. supprimé).
if (App\Core\Auth::estConnecte() && App\Models\Utilisateur::parId(App\Core\Auth::id()) === null) {
    App\Core\Auth::deconnecter();
}

// --- Langue (FR/EN) ---
App\Core\Lang::initialiser();

// --- Routes ---
$router = new Router();

// Site web (pages HTML)
$router->get('/', 'App\Controllers\CarteController@index');
$router->get('/lieu/(\d+)', 'App\Controllers\LieuController@detail');
$router->post('/lieu/(\d+)/commentaire', 'App\Controllers\LieuController@ajouterCommentaire');
$router->post('/lieu/(\d+)/note', 'App\Controllers\LieuController@enregistrerNote');

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

// Espace compte (clés API) — réservé aux connectés
$router->get('/compte',                 'App\Controllers\CompteController@index');
$router->post('/compte/cles',           'App\Controllers\CompteController@creerCle');
$router->post('/compte/cles/supprimer', 'App\Controllers\CompteController@supprimerCle');

// API desktop (JSON) — reçoit les relevés de l'appli, auth par clé API
$router->post('/api/releve', 'App\Api\ReleveController@store');

// Page 404
$router->set404(function () {
    http_response_code(404);
    (new View())->render('errors/404', ['title' => t('page.404.title')]);
});

$router->run();
