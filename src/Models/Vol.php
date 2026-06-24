<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/*
 * Vol : une sortie pilotée qui regroupe les posers d'un même vol.
 * Enregistré en bloc (transaction unique vol + ses relevés) ; consulté sur le
 * site. Supprimer un vol retire ses relevés (CASCADE) puis nettoie les lieux
 * devenus vides — un lieu encore porteur du relevé d'un autre pilote est gardé.
 */
class Vol
{
    /**
     * Enregistre un vol et tous ses posers dans UNE transaction.
     *
     * @param array<string,mixed>              $meta     date_debut (requis), date_fin, duree_sec, aeronef, depart_icao, arrivee_icao
     * @param array<int,array<string,mixed>>   $landings relevés (chacun avec latitude/longitude/date_releve ; capture éventuelle déjà résolue)
     * @return array{id_vol:int, nb:int}
     */
    public static function enregistrer(int $idUtilisateur, array $meta, array $landings): array
    {
        if ($landings === []) {
            throw new \InvalidArgumentException('Aucun atterrissage à enregistrer.');
        }

        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO vols
                    (id_utilisateur, date_debut, date_fin, duree_sec, aeronef, depart_icao, arrivee_icao, nb_atterrissages)
                 VALUES
                    (:u, :debut, :fin, :duree, :aeronef, :dep, :arr, :nb)"
            );
            $stmt->execute([
                'u'       => $idUtilisateur,
                'debut'   => $meta['date_debut'],
                'fin'     => $meta['date_fin'] ?? null,
                'duree'   => $meta['duree_sec'] ?? null,
                'aeronef' => $meta['aeronef'] ?? null,
                'dep'     => $meta['depart_icao'] ?? null,
                'arr'     => $meta['arrivee_icao'] ?? null,
                'nb'      => count($landings),
            ]);
            $idVol = (int) $pdo->lastInsertId();

            foreach ($landings as $l) {
                $l['vol_id'] = $idVol;
                $l['id_utilisateur'] = $idUtilisateur;
                Releve::inserer($pdo, $l);
            }

            $pdo->commit();

            return ['id_vol' => $idVol, 'nb' => count($landings)];
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Vols d'un pilote (résumés, du plus récent au plus ancien).
     *
     * @return array<int,array<string,mixed>>
     */
    public static function parUtilisateur(int $idUtilisateur): array
    {
        $stmt = Database::pdo()->prepare(
            "SELECT id, date_debut, date_fin, duree_sec, aeronef, depart_icao, arrivee_icao, nb_atterrissages
             FROM vols WHERE id_utilisateur = :u
             ORDER BY date_debut DESC, id DESC"
        );
        $stmt->execute(['u' => $idUtilisateur]);

        return $stmt->fetchAll();
    }

    /** @return array<string,mixed>|null */
    public static function parId(int $id): ?array
    {
        $stmt = Database::pdo()->prepare("SELECT * FROM vols WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $vol = $stmt->fetch();

        return $vol !== false ? $vol : null;
    }

    /**
     * Vol enrichi du pilote (pseudo + avatar), pour la fiche détail.
     *
     * @return array<string,mixed>|null
     */
    public static function parIdAvecPilote(int $id): ?array
    {
        $stmt = Database::pdo()->prepare(
            "SELECT v.*, u.pseudo, u.avatar
             FROM vols v
             LEFT JOIN utilisateurs u ON u.id = v.id_utilisateur
             WHERE v.id = :id"
        );
        $stmt->execute(['id' => $id]);
        $vol = $stmt->fetch();

        return $vol !== false ? $vol : null;
    }

    /**
     * Posers d'un vol, joints à leur lieu (pour le lien « voir le lieu » et le nom).
     *
     * @return array<int,array<string,mixed>>
     */
    public static function atterrissages(int $idVol): array
    {
        $stmt = Database::pdo()->prepare(
            "SELECT r.*, l.nom AS lieu_nom, l.statut AS lieu_statut,
                    l.pays_fr, l.pays_en, l.region_fr, l.region_en
             FROM releves r
             JOIN lieux l ON l.id = r.id_lieu
             WHERE r.vol_id = :v
             ORDER BY r.date_releve ASC, r.id ASC"
        );
        $stmt->execute(['v' => $idVol]);

        return $stmt->fetchAll();
    }

    /**
     * Statistiques de vol d'un pilote (nombre de vols + temps de vol cumulé).
     *
     * @return array{nb_vols:int, total_sec:int}
     */
    public static function statsParUtilisateur(int $idUtilisateur): array
    {
        $stmt = Database::pdo()->prepare(
            "SELECT COUNT(*) AS nb_vols, COALESCE(SUM(duree_sec), 0) AS total_sec
             FROM vols WHERE id_utilisateur = :u"
        );
        $stmt->execute(['u' => $idUtilisateur]);
        $a = $stmt->fetch() ?: [];

        return [
            'nb_vols'   => (int) ($a['nb_vols'] ?? 0),
            'total_sec' => (int) ($a['total_sec'] ?? 0),
        ];
    }

    /**
     * Supprime un vol appartenant à $idUtilisateur. Retire ses relevés (CASCADE)
     * puis efface les lieux devenus vides (sans relevé restant) — préserve un
     * lieu partagé encore porteur du relevé d'un autre pilote.
     * Renvoie false si le vol n'existe pas ou n'appartient pas à l'utilisateur.
     */
    public static function supprimer(int $idVol, int $idUtilisateur): bool
    {
        $pdo = Database::pdo();

        $vol = self::parId($idVol);
        if ($vol === null || (int) $vol['id_utilisateur'] !== $idUtilisateur) {
            return false;
        }

        $pdo->beginTransaction();
        try {
            // Lieux touchés par ce vol (avant suppression des relevés).
            $stmt = $pdo->prepare("SELECT DISTINCT id_lieu FROM releves WHERE vol_id = :v");
            $stmt->execute(['v' => $idVol]);
            $lieuIds = $stmt->fetchAll(\PDO::FETCH_COLUMN);

            // Supprime le vol → CASCADE retire ses relevés.
            $pdo->prepare("DELETE FROM vols WHERE id = :id")->execute(['id' => $idVol]);

            // Nettoyage : un lieu sans aucun relevé restant est orphelin → effacé
            // (CASCADE emporte ses notes/commentaires). Sinon on le garde.
            if ($lieuIds !== []) {
                $check = $pdo->prepare("SELECT COUNT(*) FROM releves WHERE id_lieu = :l");
                $del   = $pdo->prepare("DELETE FROM lieux WHERE id = :l");
                foreach ($lieuIds as $lid) {
                    $check->execute(['l' => $lid]);
                    if ((int) $check->fetchColumn() === 0) {
                        $del->execute(['l' => $lid]);
                    }
                }
            }

            $pdo->commit();
            return true;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
