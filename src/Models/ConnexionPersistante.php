<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Auth;
use App\Core\Database;

/*
 * Connexion persistante (« se souvenir de moi »).
 *
 * Pattern sélecteur / validateur :
 *  - le cookie contient « selecteur:validateur » ;
 *  - en base on stocke le sélecteur (en clair, indexé) et le HASH du validateur ;
 *  - la reconnexion compare le validateur en temps constant (hash_equals).
 * Le jeton vit 30 jours, est révoqué à la déconnexion, et un sélecteur dont le
 * validateur ne correspond pas est supprimé (vol potentiel).
 */
class ConnexionPersistante
{
    private const COOKIE = 'bcp_souvenir';
    private const DUREE = 30 * 24 * 3600;   // 30 jours

    /** Émet un jeton pour l'utilisateur et pose le cookie (à l'connexion « se souvenir »). */
    public static function emettre(int $idUtilisateur): void
    {
        self::nettoyer();

        $selecteur  = bin2hex(random_bytes(12));   // 24 caractères
        $validateur = bin2hex(random_bytes(32));   // 64 caractères
        $expire = date('Y-m-d H:i:s', time() + self::DUREE);

        Database::pdo()->prepare(
            "INSERT INTO connexions_persistantes (id_utilisateur, selecteur, validateur_hash, expire_le)
             VALUES (:u, :s, :v, :e)"
        )->execute([
            'u' => $idUtilisateur,
            's' => $selecteur,
            'v' => hash('sha256', $validateur),
            'e' => $expire,
        ]);

        self::poserCookie($selecteur . ':' . $validateur, time() + self::DUREE);
    }

    /** Tente de reconnecter l'utilisateur depuis le cookie (appelé au bootstrap). */
    public static function tenterReconnexion(): void
    {
        if (Auth::estConnecte()) {
            return;
        }
        $brut = $_COOKIE[self::COOKIE] ?? '';
        if (!str_contains($brut, ':')) {
            return;
        }
        [$selecteur, $validateur] = explode(':', $brut, 2);

        $stmt = Database::pdo()->prepare(
            "SELECT * FROM connexions_persistantes WHERE selecteur = :s"
        );
        $stmt->execute(['s' => $selecteur]);
        $ligne = $stmt->fetch();

        if ($ligne === false) {
            self::effacerCookie();
            return;
        }
        // Expiré, ou validateur incorrect (vol potentiel) → on supprime le jeton.
        if (strtotime((string) $ligne['expire_le']) < time()
            || !hash_equals((string) $ligne['validateur_hash'], hash('sha256', $validateur))) {
            self::supprimerParSelecteur($selecteur);
            self::effacerCookie();
            return;
        }

        $u = Utilisateur::parId((int) $ligne['id_utilisateur']);
        if ($u !== null) {
            Auth::connecter($u);   // recrée la session
        } else {
            self::supprimerParSelecteur($selecteur);
            self::effacerCookie();
        }
    }

    /** Révoque le jeton courant (à la déconnexion). */
    public static function revoquer(): void
    {
        $brut = $_COOKIE[self::COOKIE] ?? '';
        if (str_contains($brut, ':')) {
            [$selecteur] = explode(':', $brut, 2);
            self::supprimerParSelecteur($selecteur);
        }
        self::effacerCookie();
    }

    private static function supprimerParSelecteur(string $selecteur): void
    {
        Database::pdo()->prepare(
            "DELETE FROM connexions_persistantes WHERE selecteur = :s"
        )->execute(['s' => $selecteur]);
    }

    /** Purge les jetons expirés. */
    public static function nettoyer(): void
    {
        Database::pdo()->exec("DELETE FROM connexions_persistantes WHERE expire_le < NOW()");
    }

    private static function poserCookie(string $valeur, int $expire): void
    {
        setcookie(self::COOKIE, $valeur, [
            'expires'  => $expire,
            'path'     => BASE_URL === '' ? '/' : BASE_URL,
            'secure'   => !empty($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    private static function effacerCookie(): void
    {
        if (isset($_COOKIE[self::COOKIE])) {
            self::poserCookie('', time() - 42000);
            unset($_COOKIE[self::COOKIE]);
        }
    }
}
