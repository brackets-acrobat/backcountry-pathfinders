<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDOException;

/*
 * Compte utilisateur de la communauté.
 * Les mots de passe sont hachés avec password_hash (bcrypt) — jamais en clair.
 */
class Utilisateur
{
    /**
     * Crée un compte et renvoie son id.
     *
     * @throws PDOException si le pseudo ou l'email existe déjà (clés uniques)
     */
    public static function creer(string $pseudo, string $email, string $motDePasse): int
    {
        $stmt = Database::pdo()->prepare(
            "INSERT INTO utilisateurs (pseudo, email, mot_de_passe)
             VALUES (:pseudo, :email, :hash)"
        );
        $stmt->execute([
            'pseudo' => $pseudo,
            'email'  => $email,
            'hash'   => password_hash($motDePasse, PASSWORD_DEFAULT),
        ]);

        return (int) Database::pdo()->lastInsertId();
    }

    /** @return array<string,mixed>|null */
    public static function parEmail(string $email): ?array
    {
        $stmt = Database::pdo()->prepare("SELECT * FROM utilisateurs WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $u = $stmt->fetch();

        return $u !== false ? $u : null;
    }

    /** @return array<string,mixed>|null */
    public static function parId(int $id): ?array
    {
        $stmt = Database::pdo()->prepare("SELECT * FROM utilisateurs WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $u = $stmt->fetch();

        return $u !== false ? $u : null;
    }

    /** Enregistre le secret TOTP (double authentification), 2FA encore inactive. */
    public static function definirSecretTotp(int $id, string $secret): void
    {
        $stmt = Database::pdo()->prepare(
            "UPDATE utilisateurs SET totp_secret = :s, totp_actif = 0 WHERE id = :id"
        );
        $stmt->execute(['s' => $secret, 'id' => $id]);
    }

    /** Active la 2FA après confirmation d'un premier code valide. */
    public static function activerTotp(int $id): void
    {
        Database::pdo()->prepare("UPDATE utilisateurs SET totp_actif = 1 WHERE id = :id")
            ->execute(['id' => $id]);
    }

    /** Désactive la 2FA et efface le secret (réinitialisation). */
    public static function desactiverTotp(int $id): void
    {
        Database::pdo()->prepare("UPDATE utilisateurs SET totp_actif = 0, totp_secret = NULL WHERE id = :id")
            ->execute(['id' => $id]);
    }

    /**
     * Vérifie l'identifiant + mot de passe. Renvoie l'utilisateur si OK, sinon null.
     *
     * @return array<string,mixed>|null
     */
    public static function verifierConnexion(string $email, string $motDePasse): ?array
    {
        $u = self::parEmail($email);
        if ($u !== null && password_verify($motDePasse, $u['mot_de_passe'])) {
            return $u;
        }

        return null;
    }

    /**
     * Règle de robustesse du mot de passe (inscription ET changement) :
     * 8 caractères minimum, au moins une minuscule, une majuscule, un chiffre
     * et un caractère spécial.
     */
    public static function motDePasseValide(string $mdp): bool
    {
        return mb_strlen($mdp) >= 8
            && preg_match('/[a-z]/', $mdp) === 1
            && preg_match('/[A-Z]/', $mdp) === 1
            && preg_match('/[0-9]/', $mdp) === 1
            && preg_match('/[^A-Za-z0-9]/', $mdp) === 1;
    }

    /** Vrai si ce pseudo est déjà pris par un AUTRE utilisateur. */
    public static function pseudoPris(string $pseudo, int $exceptId): bool
    {
        $stmt = Database::pdo()->prepare(
            "SELECT 1 FROM utilisateurs WHERE pseudo = :p AND id <> :id LIMIT 1"
        );
        $stmt->execute(['p' => $pseudo, 'id' => $exceptId]);

        return $stmt->fetchColumn() !== false;
    }

    /** Vrai si cet e-mail est déjà pris par un AUTRE utilisateur. */
    public static function emailPris(string $email, int $exceptId): bool
    {
        $stmt = Database::pdo()->prepare(
            "SELECT 1 FROM utilisateurs WHERE email = :e AND id <> :id LIMIT 1"
        );
        $stmt->execute(['e' => $email, 'id' => $exceptId]);

        return $stmt->fetchColumn() !== false;
    }

    /**
     * Liste des pilotes (membres) avec leurs statistiques de contribution :
     * nombre de relevés, de lieux distincts visités et de pays distincts. Les
     * plus actifs d'abord, puis par pseudo.
     *
     * @return array<int,array<string,mixed>>
     */
    public static function tousAvecStats(): array
    {
        $stmt = Database::pdo()->query(
            "SELECT u.id, u.pseudo, u.avatar, u.date_inscription,
                    COUNT(r.id) AS nb_releves,
                    COUNT(DISTINCT r.id_lieu) AS nb_lieux,
                    COUNT(DISTINCT l.pays) AS nb_pays,
                    (SELECT COUNT(*) FROM vols v WHERE v.id_utilisateur = u.id) AS nb_vols,
                    (SELECT COALESCE(SUM(v.duree_sec), 0) FROM vols v WHERE v.id_utilisateur = u.id) AS total_sec
             FROM utilisateurs u
             LEFT JOIN releves r ON r.id_utilisateur = u.id
             LEFT JOIN lieux l  ON l.id = r.id_lieu
             GROUP BY u.id, u.pseudo, u.avatar, u.date_inscription
             ORDER BY u.pseudo ASC"
        );

        return $stmt->fetchAll();
    }

    /**
     * Liste complète des comptes pour l'espace d'administration : inclut des
     * informations sensibles (e-mail, rôle, 2FA) réservées aux admins. Les plus
     * récents d'abord.
     *
     * @return array<int,array<string,mixed>>
     */
    public static function tousPourAdmin(): array
    {
        return Database::pdo()->query(
            "SELECT id, pseudo, email, avatar, role, totp_actif, date_inscription,
                    (ip_derniere_connexion IS NOT NULL AND ip_derniere_connexion != '') AS a_ip
             FROM utilisateurs
             ORDER BY date_inscription DESC, id DESC"
        )->fetchAll();
    }

    /** Met à jour le pseudo + l'e-mail. Le pseudo se répercute partout (jointures). */
    public static function majProfil(int $id, string $pseudo, string $email): void
    {
        $stmt = Database::pdo()->prepare(
            "UPDATE utilisateurs SET pseudo = :p, email = :e WHERE id = :id"
        );
        $stmt->execute(['p' => $pseudo, 'e' => $email, 'id' => $id]);
    }

    /** Met à jour le mot de passe (haché). */
    public static function majMotDePasse(int $id, string $motDePasse): void
    {
        $stmt = Database::pdo()->prepare(
            "UPDATE utilisateurs SET mot_de_passe = :h WHERE id = :id"
        );
        $stmt->execute(['h' => password_hash($motDePasse, PASSWORD_DEFAULT), 'id' => $id]);
    }

    /** Met à jour le nom de fichier de l'avatar (ou null pour aucun). */
    public static function majAvatar(int $id, ?string $fichier): void
    {
        $stmt = Database::pdo()->prepare(
            "UPDATE utilisateurs SET avatar = :a WHERE id = :id"
        );
        $stmt->execute(['a' => $fichier, 'id' => $id]);
    }
}
