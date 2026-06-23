<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Geo;
use App\Core\Geocodage;
use App\Core\Lang;

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

    /**
     * Crée un lieu et renvoie son id.
     * Le pays et la région (géocodage inverse) sont résolus en ligne ; un
     * échec reste silencieux (champs laissés à null).
     */
    public static function creer(
        float $lat,
        float $lon,
        ?int $idCreateur = null,
        ?int $altitudeM = null,
        ?string $nom = null
    ): int {
        $geo = Geocodage::inverse($lat, $lon);

        $stmt = Database::pdo()->prepare(
            "INSERT INTO lieux
                (nom, latitude, longitude, altitude_m,
                 pays, region_code, pays_fr, pays_en, region_fr, region_en, id_createur)
             VALUES
                (:nom, :lat, :lon, :alt,
                 :pays, :region_code, :pays_fr, :pays_en, :region_fr, :region_en, :createur)"
        );
        $stmt->execute([
            'nom'         => $nom,
            'lat'         => $lat,
            'lon'         => $lon,
            'alt'         => $altitudeM,
            'pays'        => $geo['pays'],
            'region_code' => $geo['region_code'],
            'pays_fr'     => $geo['pays_fr'],
            'pays_en'     => $geo['pays_en'],
            'region_fr'   => $geo['region_fr'],
            'region_en'   => $geo['region_en'],
            'createur'    => $idCreateur,
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

    /**
     * Lieux actifs enrichis pour l'affichage carte : nombre de relevés,
     * note/difficulté moyennes et surface dominante (la plus fréquente).
     * Coordonnées et agrégats typés (float/int) pour une sortie JSON propre.
     *
     * @return array<int,array<string,mixed>>
     */
    public static function tousPourCarte(int $limite = 5000): array
    {
        $stmt = Database::pdo()->prepare(
            "SELECT l.id, l.nom, l.latitude, l.longitude, l.altitude_m,
                    l.pays, l.pays_fr, l.pays_en,
                    (SELECT COUNT(*) FROM releves r WHERE r.id_lieu = l.id) AS nb_releves,
                    (SELECT AVG(n.note)       FROM notes n WHERE n.id_lieu = l.id) AS note_moyenne,
                    (SELECT AVG(n.difficulte) FROM notes n WHERE n.id_lieu = l.id) AS difficulte_moyenne,
                    (SELECT r.type_surface FROM releves r
                       WHERE r.id_lieu = l.id AND r.type_surface IS NOT NULL AND r.type_surface <> ''
                       GROUP BY r.type_surface ORDER BY COUNT(*) DESC LIMIT 1) AS surface
             FROM lieux l
             WHERE l.statut = 'actif'
             ORDER BY l.date_creation DESC
             LIMIT :limite"
        );
        $stmt->bindValue('limite', $limite, \PDO::PARAM_INT);
        $stmt->execute();

        $langue = Lang::actuelle();

        return array_map(static function (array $l) use ($langue): array {
            return [
                'id'                 => (int) $l['id'],
                'nom'                => $l['nom'] !== null ? (string) $l['nom'] : null,
                'lat'                => (float) $l['latitude'],
                'lon'                => (float) $l['longitude'],
                'altitude_m'         => $l['altitude_m'] !== null ? (int) $l['altitude_m'] : null,
                'pays'               => self::nomPays($l, $langue),
                'nb_releves'         => (int) $l['nb_releves'],
                'note_moyenne'       => $l['note_moyenne'] !== null ? round((float) $l['note_moyenne'], 1) : null,
                'difficulte_moyenne' => $l['difficulte_moyenne'] !== null ? round((float) $l['difficulte_moyenne'], 1) : null,
                'surface'            => $l['surface'] !== null ? (string) $l['surface'] : null,
            ];
        }, $stmt->fetchAll());
    }

    /**
     * Nom du pays d'un lieu dans la langue donnée, avec repli sur l'autre
     * langue puis sur le code ISO. Null si rien n'est connu.
     *
     * @param array<string,mixed> $l  ligne contenant pays/pays_fr/pays_en
     */
    private static function nomPays(array $l, string $langue): ?string
    {
        $autre = $langue === 'fr' ? 'en' : 'fr';
        foreach (['pays_' . $langue, 'pays_' . $autre, 'pays'] as $col) {
            if (($l[$col] ?? '') !== '') {
                return (string) $l[$col];
            }
        }
        return null;
    }

    /**
     * Lieux où l'utilisateur a posé un relevé (« Mes lieux visités »),
     * avec le nombre de SES relevés et la date de sa dernière visite.
     *
     * @return array<int,array<string,mixed>>
     */
    public static function visitesParUtilisateur(int $idUtilisateur): array
    {
        $stmt = Database::pdo()->prepare(
            "SELECT l.id, l.nom, l.latitude, l.longitude, l.altitude_m,
                    COUNT(r.id) AS nb_releves,
                    MAX(r.date_releve) AS derniere_visite,
                    n.commentaire AS mon_commentaire
             FROM lieux l
             JOIN releves r ON r.id_lieu = l.id AND r.id_utilisateur = :u
             LEFT JOIN notes n ON n.id_lieu = l.id AND n.id_utilisateur = :u2
             WHERE l.statut = 'actif'
             GROUP BY l.id, l.nom, l.latitude, l.longitude, l.altitude_m, n.commentaire
             ORDER BY derniere_visite DESC"
        );
        $stmt->execute(['u' => $idUtilisateur, 'u2' => $idUtilisateur]);

        return $stmt->fetchAll();
    }

    /**
     * Renomme un lieu, à condition que l'utilisateur y ait posé un relevé
     * (l'un de « ses lieux visités »). Un nom vide repasse le lieu en
     * « sans nom » (NULL). Renvoie false si l'utilisateur n'est pas autorisé.
     */
    public static function renommer(int $id, ?string $nom, int $idUtilisateur): bool
    {
        if (!self::visitePar($id, $idUtilisateur)) {
            return false;
        }

        $nom = $nom !== null ? trim($nom) : '';
        $valeur = $nom !== '' ? mb_substr($nom, 0, 120) : null;

        $stmt = Database::pdo()->prepare("UPDATE lieux SET nom = :nom WHERE id = :id");
        $stmt->execute(['nom' => $valeur, 'id' => $id]);

        return true;
    }

    /** Vrai si l'utilisateur a posé au moins un relevé sur ce lieu. */
    private static function visitePar(int $id, int $idUtilisateur): bool
    {
        $stmt = Database::pdo()->prepare(
            "SELECT 1 FROM releves WHERE id_lieu = :id AND id_utilisateur = :u LIMIT 1"
        );
        $stmt->execute(['id' => $id, 'u' => $idUtilisateur]);

        return $stmt->fetchColumn() !== false;
    }

    /**
     * Agrégats d'un lieu pour sa fiche détail.
     *
     * @return array{nb_releves:int, note_moyenne:?float, difficulte_moyenne:?float, nb_notes:int}
     */
    public static function agregats(int $id): array
    {
        $stmt = Database::pdo()->prepare(
            "SELECT (SELECT COUNT(*) FROM releves r WHERE r.id_lieu = :id1) AS nb_releves,
                    (SELECT COUNT(*) FROM notes   n WHERE n.id_lieu = :id2) AS nb_notes,
                    (SELECT AVG(n.note)       FROM notes n WHERE n.id_lieu = :id3) AS note_moyenne,
                    (SELECT AVG(n.difficulte) FROM notes n WHERE n.id_lieu = :id4) AS difficulte_moyenne"
        );
        $stmt->execute(['id1' => $id, 'id2' => $id, 'id3' => $id, 'id4' => $id]);
        $a = $stmt->fetch() ?: [];

        return [
            'nb_releves'         => (int) ($a['nb_releves'] ?? 0),
            'nb_notes'           => (int) ($a['nb_notes'] ?? 0),
            'note_moyenne'       => isset($a['note_moyenne']) && $a['note_moyenne'] !== null ? round((float) $a['note_moyenne'], 1) : null,
            'difficulte_moyenne' => isset($a['difficulte_moyenne']) && $a['difficulte_moyenne'] !== null ? round((float) $a['difficulte_moyenne'], 1) : null,
        ];
    }
}
