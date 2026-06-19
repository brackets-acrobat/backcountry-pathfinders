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
        'id_utilisateur', 'date_releve', 'latitude', 'longitude', 'altitude_m',
        'type_surface', 'etat_surface', 'friction', 'longueur_utile_m',
        'pente_max_pct', 'denivele_m', 'profil_relief', 'aeronef', 'capture', 'commentaire',
    ];

    /**
     * Enregistre un relevé : déduplique le lieu puis insère le relevé.
     *
     * @param array<string,mixed> $d  doit contenir au moins latitude, longitude, date_releve
     * @return array{id_releve:int, id_lieu:int, nouveau_lieu:bool}
     */
    public static function enregistrer(array $d): array
    {
        foreach (['latitude', 'longitude', 'date_releve'] as $requis) {
            if (!isset($d[$requis])) {
                throw new \InvalidArgumentException("Champ requis manquant : {$requis}");
            }
        }

        $lat = (float) $d['latitude'];
        $lon = (float) $d['longitude'];
        $idUtilisateur = isset($d['id_utilisateur']) ? (int) $d['id_utilisateur'] : null;

        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
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

            $idReleve = (int) $pdo->lastInsertId();
            $pdo->commit();

            return ['id_releve' => $idReleve, 'id_lieu' => $idLieu, 'nouveau_lieu' => $nouveauLieu];
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
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
}
