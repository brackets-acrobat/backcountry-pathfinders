<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/*
 * Jetons de réinitialisation de mot de passe (à usage unique, courte durée).
 *
 * On ne stocke que le HASH (sha256) du jeton ; le jeton en clair n'existe que
 * dans le lien envoyé par e-mail. Un seul jeton actif par utilisateur : créer
 * un nouveau jeton invalide les précédents.
 */
class PasswordReset
{
    /** Durée de validité d'un jeton, en secondes (1 heure). */
    private const DUREE = 3600;

    /**
     * Émet un jeton pour l'utilisateur (en invalidant les précédents) et
     * renvoie le jeton EN CLAIR (64 caractères hex) à mettre dans le lien.
     */
    public static function creer(int $idUtilisateur): string
    {
        self::nettoyer();

        // Invalide tout jeton existant pour cet utilisateur.
        Database::pdo()
            ->prepare('DELETE FROM password_resets WHERE id_utilisateur = ?')
            ->execute([$idUtilisateur]);

        $jeton  = bin2hex(random_bytes(32));   // 64 caractères
        $expire = date('Y-m-d H:i:s', time() + self::DUREE);

        Database::pdo()->prepare(
            "INSERT INTO password_resets (id_utilisateur, jeton_hash, expire_le)
             VALUES (:u, :h, :e)"
        )->execute([
            'u' => $idUtilisateur,
            'h' => hash('sha256', $jeton),
            'e' => $expire,
        ]);

        return $jeton;
    }

    /**
     * Renvoie l'id de l'utilisateur si le jeton est valide (existant et non
     * expiré), sinon null. Ne consomme pas le jeton.
     */
    public static function validerUtilisateur(string $jeton): ?int
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $jeton)) {
            return null;
        }

        $stmt = Database::pdo()->prepare(
            "SELECT id_utilisateur FROM password_resets
             WHERE jeton_hash = :h AND expire_le > NOW() LIMIT 1"
        );
        $stmt->execute(['h' => hash('sha256', $jeton)]);
        $id = $stmt->fetchColumn();

        return $id !== false ? (int) $id : null;
    }

    /** Consomme (supprime) le jeton après une réinitialisation réussie. */
    public static function consommer(string $jeton): void
    {
        Database::pdo()
            ->prepare('DELETE FROM password_resets WHERE jeton_hash = ?')
            ->execute([hash('sha256', $jeton)]);
    }

    /** Purge les jetons expirés. */
    public static function nettoyer(): void
    {
        Database::pdo()->exec('DELETE FROM password_resets WHERE expire_le < NOW()');
    }
}
