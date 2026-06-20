<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/*
 * Note d'un lieu : appréciation + difficulté, une seule par utilisateur et
 * par lieu (contrainte unique uq_notes_lieu_user). On « upsert » donc :
 * une nouvelle note du même utilisateur écrase la précédente.
 */
class Note
{
    /**
     * Enregistre (ou met à jour) la note d'un utilisateur pour un lieu.
     * note et difficulte valent 1..5 ou null.
     */
    public static function enregistrer(int $idLieu, int $idUtilisateur, ?int $note, ?int $difficulte): void
    {
        $stmt = Database::pdo()->prepare(
            "INSERT INTO notes (id_lieu, id_utilisateur, note, difficulte)
             VALUES (:lieu, :user, :note, :diff)
             ON DUPLICATE KEY UPDATE note = VALUES(note), difficulte = VALUES(difficulte)"
        );
        $stmt->execute([
            'lieu' => $idLieu,
            'user' => $idUtilisateur,
            'note' => $note,
            'diff' => $difficulte,
        ]);
    }

    /**
     * Note existante d'un utilisateur sur un lieu (pour pré-remplir le formulaire), ou null.
     *
     * @return array{note:?int, difficulte:?int}|null
     */
    public static function pourUtilisateur(int $idLieu, int $idUtilisateur): ?array
    {
        $stmt = Database::pdo()->prepare(
            "SELECT note, difficulte FROM notes WHERE id_lieu = :lieu AND id_utilisateur = :user"
        );
        $stmt->execute(['lieu' => $idLieu, 'user' => $idUtilisateur]);
        $n = $stmt->fetch();

        if ($n === false) {
            return null;
        }

        return [
            'note'       => $n['note'] !== null ? (int) $n['note'] : null,
            'difficulte' => $n['difficulte'] !== null ? (int) $n['difficulte'] : null,
        ];
    }
}
