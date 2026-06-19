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
}
