<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/*
 * Actualités (« News ») rédigées par les administrateurs.
 * Le HTML (chapo, contenu) est déjà assaini par le contrôleur avant insertion.
 */
class Actualite
{
    /**
     * Crée une actualité et renvoie son identifiant.
     */
    public static function creer(
        int $idAuteur,
        string $titre,
        string $contenu,
        string $statut = 'publie'
    ): int {
        $stmt = Database::pdo()->prepare(
            "INSERT INTO actualites (id_auteur, titre, contenu, statut)
             VALUES (:a, :t, :co, :st)"
        );
        $stmt->execute([
            'a'  => $idAuteur,
            't'  => $titre,
            'co' => $contenu,
            'st' => $statut,
        ]);

        return (int) Database::pdo()->lastInsertId();
    }

    /**
     * Toutes les actualités, les plus récentes d'abord (sans le corps,
     * pour la liste « Gérer les actualités »).
     *
     * @return array<int,array<string,mixed>>
     */
    public static function tous(): array
    {
        return Database::pdo()->query(
            "SELECT id, titre, statut, date_creation, date_maj
             FROM actualites ORDER BY date_creation DESC, id DESC"
        )->fetchAll();
    }

    /**
     * Les $limite dernières actualités PUBLIÉES (pour la page d'accueil).
     *
     * @return array<int,array<string,mixed>>
     */
    public static function dernieres(int $limite = 5): array
    {
        $limite = max(1, $limite);
        return Database::pdo()->query(
            "SELECT id, titre, contenu, date_creation
             FROM actualites WHERE statut = 'publie'
             ORDER BY date_creation DESC, id DESC LIMIT $limite"
        )->fetchAll();
    }

    /**
     * Une actualité PUBLIÉE pour l'affichage public (avec le pseudo de l'auteur),
     * ou null si introuvable / non publiée.
     */
    public static function detailPublie(int $id): ?array
    {
        $stmt = Database::pdo()->prepare(
            "SELECT a.*, u.pseudo AS auteur
             FROM actualites a
             LEFT JOIN utilisateurs u ON u.id = a.id_auteur
             WHERE a.id = :id AND a.statut = 'publie'"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    /** Une actualité complète, ou null si introuvable. */
    public static function parId(int $id): ?array
    {
        $stmt = Database::pdo()->prepare("SELECT * FROM actualites WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    /** Met à jour le titre et le corps (et la date de modification). */
    public static function modifier(int $id, string $titre, string $contenu): void
    {
        $stmt = Database::pdo()->prepare(
            "UPDATE actualites SET titre = :t, contenu = :co, date_maj = NOW() WHERE id = :id"
        );
        $stmt->execute(['t' => $titre, 'co' => $contenu, 'id' => $id]);
    }

    /** Supprime une actualité. Renvoie vrai si une ligne a été supprimée. */
    public static function supprimer(int $id): bool
    {
        $stmt = Database::pdo()->prepare("DELETE FROM actualites WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Longueur du texte visible d'un fragment HTML (sans les balises ni les
     * entités), pour appliquer une limite de caractères côté serveur.
     */
    public static function longueurTexte(string $html): int
    {
        $texte = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $texte = preg_replace('/\s+/u', ' ', $texte) ?? $texte;
        return mb_strlen(trim($texte));
    }
}
