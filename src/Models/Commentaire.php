<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/*
 * Commentaire : fil de discussion communautaire attaché à un lieu.
 */
class Commentaire
{
    /**
     * Commentaires d'un lieu, du plus récent au plus ancien, avec le pseudo de l'auteur.
     *
     * @return array<int,array<string,mixed>>
     */
    public static function parLieu(int $idLieu): array
    {
        $stmt = Database::pdo()->prepare(
            "SELECT c.id, c.texte, c.date_creation, u.pseudo
             FROM commentaires c
             LEFT JOIN utilisateurs u ON u.id = c.id_utilisateur
             WHERE c.id_lieu = :id
             ORDER BY c.date_creation DESC"
        );
        $stmt->execute(['id' => $idLieu]);

        return $stmt->fetchAll();
    }
}
