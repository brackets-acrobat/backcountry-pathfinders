<?php

declare(strict_types=1);

namespace App\Controllers;

/*
 * Sert les images de spots stockées HORS docroot (storage/uploads), via la
 * route GET /uploads/{fichier}. Validation stricte du nom (anti-traversal) et
 * type d'image uniquement.
 */
class UploadController
{
    private const DIR = __DIR__ . '/../../storage/uploads';
    private const TYPES = [
        'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
        'png' => 'image/png', 'webp' => 'image/webp',
    ];

    public function serve(string $name): void
    {
        $name = basename($name);   // neutralise tout ../
        if (!preg_match('/^[a-zA-Z0-9_-]+\.(jpe?g|png|webp)$/', $name)) {
            http_response_code(404);
            echo 'Not found';
            return;
        }
        $path = self::DIR . '/' . $name;
        if (!is_file($path)) {
            http_response_code(404);
            echo 'Not found';
            return;
        }
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        header('Content-Type: ' . (self::TYPES[$ext] ?? 'application/octet-stream'));
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: public, max-age=86400');
        readfile($path);
    }
}
