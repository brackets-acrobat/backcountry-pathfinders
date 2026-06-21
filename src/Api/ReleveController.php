<?php

declare(strict_types=1);

namespace App\Api;

use App\Core\Api;
use App\Models\Releve;
use Throwable;

/*
 * Endpoint POST /api/releve : reçoit un relevé de l'appli desktop, authentifié
 * par clé API, et l'enregistre (avec déduplication du lieu).
 *
 * Deux formats acceptés :
 *  - application/json  : corps JSON (latitude/longitude obligatoires).
 *  - multipart/form-data : mêmes champs en form-fields + un fichier image
 *    optionnel `capture` (la photo du spot) + un champ `uid` (id du poser,
 *    sert à nommer le fichier). profil_relief est alors une chaîne JSON.
 *
 * Champs : latitude, longitude, date_releve, altitude_m, type_surface,
 *   etat_surface, friction, longueur_utile_m, pente_max_pct, denivele_m,
 *   cap_moyen_deg, profil_relief, aeronef, capture, commentaire.
 */
class ReleveController
{
    private const UPLOAD_DIR = __DIR__ . '/../../storage/uploads';
    private const MAX_BYTES = 8 * 1024 * 1024;   // 8 Mo
    private const MIMES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    private const WEBP_QUALITY = 82;             // toutes les captures sont stockées en WebP

    public function store(): void
    {
        $cle = Api::authentifier();                       // 401 si clé absente/invalide
        $idUtilisateur = (int) $cle['id_utilisateur'];

        $multipart = str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data');
        $d = $multipart ? $this->champsMultipart() : Api::corpsJson();

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

        // Image éventuelle (multipart) → enregistrée et référencée dans `capture`.
        if ($multipart && isset($_FILES['capture']) && ($_FILES['capture']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            try {
                $d['capture'] = $this->enregistrerCapture($_FILES['capture'], (string) ($d['uid'] ?? ''), $idUtilisateur);
            } catch (Throwable $e) {
                Api::erreur('Capture refusée : ' . $e->getMessage(), 422);
            }
        }
        unset($d['uid']);   // pas une colonne

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
            'capture'      => $d['capture'] ?? null,
        ], 201);
    }

    /** Champs d'une requête multipart ($_POST), profil_relief décodé en tableau. */
    private function champsMultipart(): array
    {
        $d = $_POST;
        if (isset($d['profil_relief']) && is_string($d['profil_relief'])) {
            $arr = json_decode($d['profil_relief'], true);
            if (is_array($arr)) {
                $d['profil_relief'] = $arr;
            }
        }
        return $d;
    }

    /**
     * Valide et enregistre la photo. Retourne le nom de fichier stocké.
     * @param array<string,mixed> $file  entrée $_FILES['capture']
     */
    private function enregistrerCapture(array $file, string $uid, int $idUser): string
    {
        if (($file['error'] ?? 1) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('échec de transfert');
        }
        if (($file['size'] ?? 0) <= 0 || $file['size'] > self::MAX_BYTES) {
            throw new \RuntimeException('taille invalide (max 8 Mo)');
        }
        if (!is_uploaded_file($file['tmp_name'])) {
            throw new \RuntimeException('fichier invalide');
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!isset(self::MIMES[$mime])) {
            throw new \RuntimeException('type non autorisé (JPEG/PNG/WebP)');
        }

        // Nom = uid du poser assaini (sinon repli unique). Jamais le nom client.
        $base = preg_replace('/[^a-zA-Z0-9_-]/', '', $uid);
        if ($base === '' || $base === null) {
            $base = 'bcp_' . $idUser . '_' . bin2hex(random_bytes(6));
        }
        $name = $base . '.webp';

        if (!is_dir(self::UPLOAD_DIR)) {
            mkdir(self::UPLOAD_DIR, 0775, true);
        }

        // Conversion en WebP (GD) — quel que soit le format source.
        $data = file_get_contents($file['tmp_name']);
        $im = ($data !== false) ? @imagecreatefromstring($data) : false;
        if ($im === false) {
            throw new \RuntimeException('image illisible');
        }
        $ok = imagewebp($im, self::UPLOAD_DIR . '/' . $name, self::WEBP_QUALITY);
        imagedestroy($im);
        if (!$ok) {
            throw new \RuntimeException('conversion WebP impossible');
        }
        return $name;
    }
}
