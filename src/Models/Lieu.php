<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Geo;

/*
 * Lieu de poser, dédupliqué par coordonnées.
 *
 * Déduplication : avant de créer un lieu pour un nouveau relevé, on cherche
 * un lieu existant dans un rayon (RAYON_DEDUP_M). S'il existe, le relevé s'y
 * rattache ; sinon on crée un nouveau lieu.
 */
class Lieu
{
    /** Rayon de regroupement : deux posers à moins de cette distance = le même lieu. */
    public const RAYON_DEDUP_M = 120.0;

    /**
     * Renvoie le lieu actif le plus proche dans le rayon donné, ou null.
     *
     * @return array<string,mixed>|null
     */
    public static function trouverProche(float $lat, float $lon, float $rayonM = self::RAYON_DEDUP_M): ?array
    {
        $b = Geo::boiteEnglobante($lat, $lon, $rayonM);

        $stmt = Database::pdo()->prepare(
            "SELECT * FROM lieux
             WHERE statut = 'actif'
               AND latitude  BETWEEN :latMin AND :latMax
               AND longitude BETWEEN :lonMin AND :lonMax"
        );
        $stmt->execute($b);
        $candidats = $stmt->fetchAll();

        $meilleur = null;
        $meilleureDist = $rayonM;
        foreach ($candidats as $c) {
            $d = Geo::distanceM($lat, $lon, (float) $c['latitude'], (float) $c['longitude']);
            if ($d <= $meilleureDist) {
                $meilleureDist = $d;
                $meilleur = $c;
            }
        }

        return $meilleur;
    }

    /** Crée un lieu et renvoie son id. */
    public static function creer(
        float $lat,
        float $lon,
        ?int $idCreateur = null,
        ?int $altitudeM = null,
        ?string $nom = null
    ): int {
        $stmt = Database::pdo()->prepare(
            "INSERT INTO lieux (nom, latitude, longitude, altitude_m, id_createur)
             VALUES (:nom, :lat, :lon, :alt, :createur)"
        );
        $stmt->execute([
            'nom'      => $nom,
            'lat'      => $lat,
            'lon'      => $lon,
            'alt'      => $altitudeM,
            'createur' => $idCreateur,
        ]);

        return (int) Database::pdo()->lastInsertId();
    }

    /**
     * Rattache à un lieu existant proche, ou en crée un nouveau.
     * Renvoie l'id du lieu (existant ou créé).
     */
    public static function rattacherOuCreer(
        float $lat,
        float $lon,
        ?int $idCreateur = null,
        ?int $altitudeM = null
    ): int {
        $existant = self::trouverProche($lat, $lon);
        if ($existant !== null) {
            return (int) $existant['id'];
        }

        return self::creer($lat, $lon, $idCreateur, $altitudeM);
    }

    /** @return array<string,mixed>|null */
    public static function parId(int $id): ?array
    {
        $stmt = Database::pdo()->prepare("SELECT * FROM lieux WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $lieu = $stmt->fetch();

        return $lieu !== false ? $lieu : null;
    }

    /**
     * Liste des lieux actifs (pour la carte).
     *
     * @return array<int,array<string,mixed>>
     */
    public static function tous(int $limite = 1000): array
    {
        $stmt = Database::pdo()->prepare(
            "SELECT id, nom, latitude, longitude, altitude_m, date_creation
             FROM lieux WHERE statut = 'actif'
             ORDER BY date_creation DESC
             LIMIT :limite"
        );
        $stmt->bindValue('limite', $limite, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
