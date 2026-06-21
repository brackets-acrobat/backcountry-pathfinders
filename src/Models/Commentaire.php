<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/*
 * Commentaire : fil de discussion communautaire attaché à un lieu.
 */
class Commentaire
{
    /** Ajoute un commentaire à un lieu et renvoie son id. */
    public static function ajouter(int $idLieu, int $idUtilisateur, string $texte): int
    {
        $stmt = Database::pdo()->prepare(
            "INSERT INTO commentaires (id_lieu, id_utilisateur, texte)
             VALUES (:lieu, :user, :texte)"
        );
        $stmt->execute([
            'lieu'  => $idLieu,
            'user'  => $idUtilisateur,
            'texte' => $texte,
        ]);

        return (int) Database::pdo()->lastInsertId();
    }

    /**
     * Commentaires d'un lieu, du plus récent au plus ancien, avec le pseudo de l'auteur.
     *
     * @return array<int,array<string,mixed>>
     */
    public static function parLieu(int $idLieu): array
    {
        $stmt = Database::pdo()->prepare(
            "SELECT c.id, c.texte, c.date_creation, u.pseudo, u.avatar
             FROM commentaires c
             LEFT JOIN utilisateurs u ON u.id = c.id_utilisateur
             WHERE c.id_lieu = :id
             ORDER BY c.date_creation DESC"
        );
        $stmt->execute(['id' => $idLieu]);

        return $stmt->fetchAll();
    }
}
