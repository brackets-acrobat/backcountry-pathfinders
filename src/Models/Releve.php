<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/*
 * Relevé : une capture de données par un pilote sur un lieu
 * (= l'unité de contribution). Crée ou rejoint un lieu par déduplication.
 */
class Releve
{
    /** Colonnes acceptées en plus de id_lieu (id_lieu est calculé par dédup). */
    private const CHAMPS = [
        'vol_id', 'id_utilisateur', 'date_releve', 'latitude', 'longitude', 'altitude_m',
        'type_surface', 'etat_surface', 'vitesse_toucher_kt', 'distance_roulage_m', 'friction',
        'pente_max_pct', 'denivele_m', 'cap_moyen_deg', 'profil_relief', 'aeronef', 'capture', 'commentaire',
    ];

    /**
     * Enregistre un relevé isolé (déduplique le lieu, ouvre sa propre
     * transaction). Conservé pour usage hors envoi groupé.
     *
     * @param array<string,mixed> $d  doit contenir au moins latitude, longitude, date_releve, vol_id
     * @return array{id_releve:int, id_lieu:int, nouveau_lieu:bool}
     */
    public static function enregistrer(array $d): array
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $res = self::inserer($pdo, $d);
            $pdo->commit();
            return $res;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Insère un relevé DANS une transaction déjà ouverte (utilisé par l'envoi
     * groupé d'un vol : un seul beginTransaction englobe le vol + ses posers).
     * Déduplique le lieu (le rejoint ou le crée).
     *
     * @param array<string,mixed> $d  doit contenir latitude, longitude, date_releve, vol_id
     * @return array{id_releve:int, id_lieu:int, nouveau_lieu:bool}
     */
    public static function inserer(\PDO $pdo, array $d): array
    {
        foreach (['latitude', 'longitude', 'date_releve', 'vol_id'] as $requis) {
            if (!isset($d[$requis])) {
                throw new \InvalidArgumentException("Champ requis manquant : {$requis}");
            }
        }

        $lat = (float) $d['latitude'];
        $lon = (float) $d['longitude'];
        $idUtilisateur = isset($d['id_utilisateur']) ? (int) $d['id_utilisateur'] : null;

        $lieuExistant = Lieu::trouverProche($lat, $lon);
        $nouveauLieu = $lieuExistant === null;
        $idLieu = $nouveauLieu
            ? Lieu::creer($lat, $lon, $idUtilisateur, isset($d['altitude_m']) ? (int) $d['altitude_m'] : null)
            : (int) $lieuExistant['id'];

        // Le profil de relief peut arriver en tableau → on le sérialise en JSON.
        if (isset($d['profil_relief']) && is_array($d['profil_relief'])) {
            $d['profil_relief'] = json_encode($d['profil_relief'], JSON_UNESCAPED_UNICODE);
        }

        $colonnes = ['id_lieu'];
        $valeurs  = ['id_lieu' => $idLieu];
        foreach (self::CHAMPS as $champ) {
            if (array_key_exists($champ, $d)) {
                $colonnes[] = $champ;
                $valeurs[$champ] = $d[$champ];
            }
        }

        $placeholders = array_map(static fn ($c) => ':' . $c, $colonnes);
        $sql = 'INSERT INTO releves (' . implode(', ', $colonnes) . ') VALUES (' . implode(', ', $placeholders) . ')';
        $pdo->prepare($sql)->execute($valeurs);

        return ['id_releve' => (int) $pdo->lastInsertId(), 'id_lieu' => $idLieu, 'nouveau_lieu' => $nouveauLieu];
    }

    /**
     * Tous les relevés d'un lieu (le profil_relief reste en JSON brut).
     *
     * @return array<int,array<string,mixed>>
     */
    public static function parLieu(int $idLieu): array
    {
        $stmt = Database::pdo()->prepare(
            "SELECT * FROM releves WHERE id_lieu = :id ORDER BY date_releve DESC"
        );
        $stmt->execute(['id' => $idLieu]);

        return $stmt->fetchAll();
    }

    /**
     * Relevés d'un lieu enrichis du pseudo du contributeur (pour la fiche détail).
     *
     * @return array<int,array<string,mixed>>
     */
    public static function parLieuAvecAuteur(int $idLieu): array
    {
        $stmt = Database::pdo()->prepare(
            "SELECT r.*, u.pseudo
             FROM releves r
             LEFT JOIN utilisateurs u ON u.id = r.id_utilisateur
             WHERE r.id_lieu = :id
             ORDER BY r.date_releve DESC"
        );
        $stmt->execute(['id' => $idLieu]);

        return $stmt->fetchAll();
    }
}
