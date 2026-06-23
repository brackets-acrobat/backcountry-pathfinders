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
     * Enregistre (ou met à jour) le seul commentaire libre du pilote sur un lieu.
     * Une chaîne vide efface le commentaire (NULL). N'affecte ni la note ni la
     * difficulté ; crée la ligne notes si elle n'existe pas encore.
     */
    public static function enregistrerCommentaire(int $idLieu, int $idUtilisateur, ?string $commentaire): void
    {
        $commentaire = $commentaire !== null ? trim($commentaire) : '';
        $valeur = $commentaire !== '' ? $commentaire : null;

        $stmt = Database::pdo()->prepare(
            "INSERT INTO notes (id_lieu, id_utilisateur, commentaire)
             VALUES (:lieu, :user, :commentaire)
             ON DUPLICATE KEY UPDATE commentaire = VALUES(commentaire)"
        );
        $stmt->execute(['lieu' => $idLieu, 'user' => $idUtilisateur, 'commentaire' => $valeur]);
    }

    /**
     * Commentaires des pilotes sur un lieu (« Commentaire de {pseudo} »), avec
     * leur note et difficulté éventuelles + pseudo/avatar de l'auteur.
     * Seules les lignes portant un commentaire non vide sont renvoyées.
     *
     * @return array<int,array<string,mixed>>
     */
    public static function commentairesPourLieu(int $idLieu): array
    {
        $stmt = Database::pdo()->prepare(
            "SELECT n.note, n.difficulte, n.commentaire, n.date_creation,
                    u.pseudo, u.avatar
             FROM notes n
             JOIN utilisateurs u ON u.id = n.id_utilisateur
             WHERE n.id_lieu = :lieu AND n.commentaire IS NOT NULL AND n.commentaire <> ''
             ORDER BY n.date_creation DESC"
        );
        $stmt->execute(['lieu' => $idLieu]);

        return $stmt->fetchAll();
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
