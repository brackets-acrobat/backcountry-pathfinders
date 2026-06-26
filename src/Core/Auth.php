<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Database;

/*
 * Gestion de la session et de l'utilisateur connecté + protection CSRF.
 * On ne stocke en session que l'essentiel (id, pseudo, rôle), jamais le hash.
 */
class Auth
{
    /** Démarre la session si ce n'est pas déjà fait (à appeler avant tout rendu). */
    public static function demarrer(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    /** Ouvre une session pour l'utilisateur (régénère l'id contre la fixation). */
    public static function connecter(array $utilisateur): void
    {
        session_regenerate_id(true);
        $_SESSION['utilisateur'] = [
            'id'     => (int) $utilisateur['id'],
            'pseudo' => $utilisateur['pseudo'],
            'role'   => $utilisateur['role'],
            'avatar' => $utilisateur['avatar'] ?? null,
        ];

        // Enregistre l'IP de connexion (utilisée pour le bannissement par IP depuis /admin).
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        if ($ip !== '') {
            try {
                Database::pdo()
                    ->prepare('UPDATE utilisateurs SET ip_derniere_connexion = ? WHERE id = ?')
                    ->execute([$ip, (int) $utilisateur['id']]);
            } catch (\Throwable) {
                // Colonne absente si migration pas encore appliquée : on ignore silencieusement.
            }
        }
    }

    /** Met à jour les infos de l'utilisateur en session (après édition du profil). */
    public static function rafraichir(array $champs): void
    {
        if (isset($_SESSION['utilisateur'])) {
            $_SESSION['utilisateur'] = array_merge($_SESSION['utilisateur'], $champs);
        }
    }

    /** Ferme la session et efface le cookie. */
    public static function deconnecter(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public static function estConnecte(): bool
    {
        return isset($_SESSION['utilisateur']);
    }

    /** Vrai si l'utilisateur connecté a le rôle administrateur. */
    public static function estAdmin(): bool
    {
        return isset($_SESSION['utilisateur'])
            && ($_SESSION['utilisateur']['role'] ?? '') === 'admin';
    }

    /** @return array<string,mixed>|null */
    public static function utilisateur(): ?array
    {
        return $_SESSION['utilisateur'] ?? null;
    }

    public static function id(): ?int
    {
        return isset($_SESSION['utilisateur']) ? (int) $_SESSION['utilisateur']['id'] : null;
    }

    /** Jeton CSRF de la session (généré une fois, réutilisé). */
    public static function jetonCsrf(): string
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf'];
    }

    /** Vérifie un jeton CSRF reçu (comparaison à temps constant). */
    public static function verifierCsrf(?string $jeton): bool
    {
        return is_string($jeton)
            && !empty($_SESSION['csrf'])
            && hash_equals($_SESSION['csrf'], $jeton);
    }
}
