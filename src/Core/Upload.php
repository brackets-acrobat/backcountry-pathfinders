<?php

declare(strict_types=1);

namespace App\Core;

/*
 * Enregistrement des captures d'écran de spots envoyées par l'appli desktop.
 * Toute image (JPEG/PNG/WebP) est validée puis convertie en WebP dans
 * storage/uploads. Partagé par les endpoints qui reçoivent des photos.
 */
class Upload
{
    private const UPLOAD_DIR = __DIR__ . '/../../storage/uploads';
    private const MAX_BYTES = 8 * 1024 * 1024;   // 8 Mo
    private const MIMES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    private const WEBP_QUALITY = 82;

    /**
     * Valide et enregistre une capture. Retourne le nom de fichier WebP stocké.
     *
     * @param array<string,mixed> $file  entrée $_FILES[...] (un seul fichier)
     * @param string $uid   identifiant du poser (sert à nommer le fichier)
     * @param int    $idUser propriétaire (repli pour le nom de fichier)
     * @throws \RuntimeException si l'image est absente/invalide/refusée
     */
    public static function enregistrerCapture(array $file, string $uid, int $idUser): string
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
