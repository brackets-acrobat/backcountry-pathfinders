<?php

declare(strict_types=1);

namespace App\Api;

use App\Core\Api;
use App\Models\Releve;
use Throwable;

/*
 * Endpoint POST /api/releve : reçoit un relevé de l'appli desktop (JSON),
 * authentifié par clé API, et l'enregistre (avec déduplication du lieu).
 *
 * Corps JSON attendu (minimum latitude + longitude) :
 *   { "latitude": 45.5, "longitude": 3.1, "date_releve": "2026-06-20 14:30:00",
 *     "altitude_m": 1100, "type_surface": "Grass", "etat_surface": "Normal",
 *     "friction": 0.42, "longueur_utile_m": 240, "pente_max_pct": 3.5,
 *     "denivele_m": 4, "profil_relief": [...], "aeronef": "Kitfox",
 *     "capture": "fichier.jpg", "commentaire": "..." }
 */
class ReleveController
{
    public function store(): void
    {
        $cle = Api::authentifier();                       // 401 si clé absente/invalide
        $idUtilisateur = (int) $cle['id_utilisateur'];

        $d = Api::corpsJson();

        // Validation des coordonnées (obligatoires).
        $invalides = [];
        if (!isset($d['latitude']) || !is_numeric($d['latitude']) || $d['latitude'] < -90 || $d['latitude'] > 90) {
            $invalides[] = 'latitude';
        }
        if (!isset($d['longitude']) || !is_numeric($d['longitude']) || $d['longitude'] < -180 || $d['longitude'] > 180) {
            $invalides[] = 'longitude';
        }
        if ($invalides !== []) {
            Api::erreur('Champs invalides ou manquants : ' . implode(', ', $invalides), 422);
        }

        // Champs imposés par le serveur.
        $d['id_utilisateur'] = $idUtilisateur;
        $d['date_releve'] = isset($d['date_releve']) && $d['date_releve'] !== ''
            ? (string) $d['date_releve']
            : date('Y-m-d H:i:s');

        try {
            $res = Releve::enregistrer($d);
        } catch (Throwable $e) {
            Api::erreur('Erreur serveur lors de l\'enregistrement du relevé.', 500);
        }

        Api::repondre([
            'ok'           => true,
            'id_releve'    => $res['id_releve'],
            'id_lieu'      => $res['id_lieu'],
            'nouveau_lieu' => $res['nouveau_lieu'],
        ], 201);
    }
}
