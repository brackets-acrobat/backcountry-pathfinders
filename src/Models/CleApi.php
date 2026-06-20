<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/*
 * Clés d'API utilisées par l'appli desktop pour envoyer des relevés.
 * La clé en clair n'est connue qu'à sa création ; en base on ne garde que
 * son hash SHA-256 (comme un mot de passe).
 */
class CleApi
{
    /** Crée une clé pour un utilisateur et renvoie la clé EN CLAIR (à montrer une seule fois). */
    public static function creer(int $idUtilisateur, ?string $libelle = null): string
    {
        $cleClaire = bin2hex(random_bytes(24));   // 48 caractères hexadécimaux

        Database::pdo()->prepare(
            "INSERT INTO cles_api (id_utilisateur, cle_hash, libelle)
             VALUES (:u, :h, :l)"
        )->execute([
            'u' => $idUtilisateur,
            'h' => hash('sha256', $cleClaire),
            'l' => $libelle,
        ]);

        return $cleClaire;
    }

    /** Authentifie une clé en clair : renvoie la ligne (active) correspondante, ou null. */
    public static function authentifier(string $cleClaire): ?array
    {
        $stmt = Database::pdo()->prepare(
            "SELECT * FROM cles_api WHERE cle_hash = :h AND active = 1"
        );
        $stmt->execute(['h' => hash('sha256', $cleClaire)]);
        $cle = $stmt->fetch();

        return $cle !== false ? $cle : null;
    }

    /** Met à jour l'horodatage de dernière utilisation. */
    public static function toucher(int $id): void
    {
        Database::pdo()->prepare(
            "UPDATE cles_api SET derniere_utilisation = NOW() WHERE id = :id"
        )->execute(['id' => $id]);
    }

    /**
     * Clés d'un utilisateur (sans le hash).
     *
     * @return array<int,array<string,mixed>>
     */
    public static function parUtilisateur(int $idUtilisateur): array
    {
        $stmt = Database::pdo()->prepare(
            "SELECT id, libelle, active, date_creation, derniere_utilisation
             FROM cles_api WHERE id_utilisateur = :u
             ORDER BY date_creation DESC"
        );
        $stmt->execute(['u' => $idUtilisateur]);

        return $stmt->fetchAll();
    }

    /** Supprime une clé, à condition qu'elle appartienne à l'utilisateur. */
    public static function supprimer(int $id, int $idUtilisateur): void
    {
        Database::pdo()->prepare(
            "DELETE FROM cles_api WHERE id = :id AND id_utilisateur = :u"
        )->execute(['id' => $id, 'u' => $idUtilisateur]);
    }
}
