<?php

declare(strict_types=1);

namespace App\Api;

use App\Core\Api;
use App\Core\Upload;
use App\Models\Vol;
use Throwable;

/*
 * Endpoint POST /api/vol : reçoit un VOL entier de l'appli desktop (temps de
 * vol + tous ses atterrissages) en une seule requête, authentifié par clé API, et
 * l'enregistre dans une transaction unique (vol + relevés, déduplication des
 * lieux).
 *
 * Format multipart/form-data :
 *   - champ `vol`  : JSON
 *       { date_debut, date_fin, duree_sec, aeronef, depart_icao, arrivee_icao,
 *         landings: [ { uid, latitude, longitude, date_releve, type_surface,
 *                       etat_surface, vitesse_toucher_kt, distance_roulage_m,
 *                       cap_moyen_deg, denivele_m, pente_max_pct, altitude_m,
 *                       aeronef, profil_relief, commentaire }, … ] }
 *   - fichiers `capture_<uid>` : une photo par atterrissage (la règle exige qu'au
 *     moins un atterrissage ait une photo ; ceux sans photo sont ignorés).
 */
class VolController
{
    public function store(): void
    {
        $cle = Api::authentifier();                       // 401 si clé absente/invalide
        $idUtilisateur = (int) $cle['id_utilisateur'];

        $vol = $this->lireVolJson();
        $landingsBruts = $vol['landings'] ?? null;
        if (!is_array($landingsBruts) || $landingsBruts === []) {
            Api::erreur('Aucun atterrissage dans le vol.', 422);
        }

        // Construit la liste des atterrissages à enregistrer : on ne garde que
        // ceux qui ont une photo valide (règle : un vol est envoyé si ≥1
        // atterrissage photographié ; les atterrissages sans photo sont ignorés).
        $landings = [];
        foreach ($landingsBruts as $i => $l) {
            if (!is_array($l)) {
                continue;
            }
            if (!$this->coordsValides($l)) {
                Api::erreur('Coordonnées invalides pour l\'atterrissage #' . $i . '.', 422);
            }

            $uid = isset($l['uid']) ? (string) $l['uid'] : '';
            $champFichier = 'capture_' . $uid;
            $aPhoto = $uid !== '' && isset($_FILES[$champFichier])
                && ($_FILES[$champFichier]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
            if (!$aPhoto) {
                continue;   // atterrissage sans photo → ignoré
            }

            try {
                $l['capture'] = Upload::enregistrerCapture($_FILES[$champFichier], $uid, $idUtilisateur);
            } catch (Throwable $e) {
                Api::erreur('Capture refusée (atterrissage #' . $i . ') : ' . $e->getMessage(), 422);
            }

            unset($l['uid']);   // pas une colonne
            $l['date_releve'] = isset($l['date_releve']) && $l['date_releve'] !== ''
                ? (string) $l['date_releve']
                : date('Y-m-d H:i:s');
            $landings[] = $l;
        }

        if ($landings === []) {
            Api::erreur('Aucun atterrissage avec photo : rien à enregistrer.', 422);
        }

        $meta = $this->meta($vol, $landings);

        try {
            $res = Vol::enregistrer($idUtilisateur, $meta, $landings);
        } catch (Throwable $e) {
            Api::erreur('Erreur serveur lors de l\'enregistrement du vol.', 500);
        }

        Api::repondre([
            'ok'     => true,
            'id_vol' => $res['id_vol'],
            'nb'     => $res['nb'],
        ], 201);
    }

    /** Décode le champ `vol` (JSON) de la requête multipart. */
    private function lireVolJson(): array
    {
        $brut = $_POST['vol'] ?? '';
        if (!is_string($brut) || $brut === '') {
            Api::erreur('Champ « vol » manquant.', 422);
        }
        $data = json_decode($brut, true);
        if (!is_array($data)) {
            Api::erreur('Champ « vol » : JSON invalide.', 422);
        }
        return $data;
    }

    /** @param array<string,mixed> $l */
    private function coordsValides(array $l): bool
    {
        return isset($l['latitude'], $l['longitude'])
            && is_numeric($l['latitude']) && $l['latitude'] >= -90 && $l['latitude'] <= 90
            && is_numeric($l['longitude']) && $l['longitude'] >= -180 && $l['longitude'] <= 180;
    }

    /**
     * Métadonnées du vol nettoyées et bornées. date_debut (NOT NULL) se replie
     * sur la date du 1er atterrissage, sinon sur maintenant.
     *
     * @param array<string,mixed>            $vol
     * @param array<int,array<string,mixed>> $landings
     * @return array<string,mixed>
     */
    private function meta(array $vol, array $landings): array
    {
        $dateDebut = isset($vol['date_debut']) && $vol['date_debut'] !== ''
            ? (string) $vol['date_debut']
            : ((string) ($landings[0]['date_releve'] ?? '') ?: date('Y-m-d H:i:s'));

        $duree = isset($vol['duree_sec']) && is_numeric($vol['duree_sec'])
            ? max(0, (int) $vol['duree_sec'])
            : null;

        $icao = static function ($v): ?string {
            $v = strtoupper(trim((string) $v));
            return $v !== '' ? mb_substr($v, 0, 8) : null;
        };
        $texte = static function ($v, int $max): ?string {
            $v = trim((string) $v);
            return $v !== '' ? mb_substr($v, 0, $max) : null;
        };

        return [
            'date_debut'   => $dateDebut,
            'date_fin'     => isset($vol['date_fin']) && $vol['date_fin'] !== '' ? (string) $vol['date_fin'] : null,
            'duree_sec'    => $duree,
            'aeronef'      => isset($vol['aeronef']) ? $texte($vol['aeronef'], 80) : null,
            'depart_icao'  => isset($vol['depart_icao']) ? $icao($vol['depart_icao']) : null,
            'arrivee_icao' => isset($vol['arrivee_icao']) ? $icao($vol['arrivee_icao']) : null,
        ];
    }
}
