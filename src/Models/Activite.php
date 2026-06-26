<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/*
 * Flux d'activité récente de la communauté, pour la page d'administration :
 * fusion (UNION) des derniers événements de chaque table (inscriptions, vols,
 * lieux, commentaires, notes), triés du plus récent au plus ancien.
 */
class Activite
{
    /**
     * Derniers événements, tous types confondus.
     *
     * Chaque ligne : type, quand (datetime), acteur (pseudo), acteur_id (id du
     * pilote ou null), libelle (cible), ref (id pour le lien selon le type),
     * id_entite (id réel de la ligne pour les opérations admin : = ref pour
     * membre/vol/lieu, = id propre pour commentaire/note).
     *
     * @param string|null $type Filtre par type d'événement (membre|vol|lieu|
     *                          commentaire|note) ; null = tous.
     * @return array<int,array<string,mixed>>
     */
    public static function recentes(int $limite = 60, ?string $type = null): array
    {
        $limite = max(1, min(200, $limite));

        $branches = [
            'membre' =>
                "(SELECT 'membre' AS type, u.date_inscription AS quand,
                         u.pseudo AS acteur, u.id AS acteur_id, NULL AS libelle,
                         u.id AS ref, u.id AS id_entite
                  FROM utilisateurs u)",
            'vol' =>
                "(SELECT 'vol' AS type, v.date_creation AS quand,
                         us.pseudo AS acteur, us.id AS acteur_id,
                         CONCAT(COALESCE(v.depart_icao, '????'), ' \xe2\x86\x92 ', COALESCE(v.arrivee_icao, '????')) AS libelle,
                         v.id AS ref, v.id AS id_entite
                  FROM vols v JOIN utilisateurs us ON us.id = v.id_utilisateur)",
            'lieu' =>
                "(SELECT 'lieu' AS type, l.date_creation AS quand,
                         cr.pseudo AS acteur, cr.id AS acteur_id, l.nom AS libelle,
                         l.id AS ref, l.id AS id_entite
                  FROM lieux l LEFT JOIN utilisateurs cr ON cr.id = l.id_createur)",
            'commentaire' =>
                "(SELECT 'commentaire' AS type, c.date_creation AS quand,
                         uc.pseudo AS acteur, uc.id AS acteur_id, lc.nom AS libelle,
                         c.id_lieu AS ref, c.id AS id_entite
                  FROM commentaires c
                  LEFT JOIN utilisateurs uc ON uc.id = c.id_utilisateur
                  JOIN lieux lc ON lc.id = c.id_lieu)",
            'note' =>
                "(SELECT 'note' AS type, n.date_creation AS quand,
                         un.pseudo AS acteur, un.id AS acteur_id, ln.nom AS libelle,
                         n.id_lieu AS ref, n.id AS id_entite
                  FROM notes n
                  JOIN utilisateurs un ON un.id = n.id_utilisateur
                  JOIN lieux ln ON ln.id = n.id_lieu)",
        ];

        $corps = ($type !== null && isset($branches[$type]))
            ? $branches[$type]
            : implode("\n UNION ALL \n", $branches);

        $sql = $corps . "\n ORDER BY quand DESC LIMIT " . (int) $limite;

        return Database::pdo()->query($sql)->fetchAll();
    }
}
