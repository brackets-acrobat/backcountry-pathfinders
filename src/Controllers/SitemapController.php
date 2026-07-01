<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Actualite;
use App\Models\Lieu;
use App\Models\Utilisateur;

/*
 * Plan du site (sitemap.xml) généré dynamiquement : pages statiques publiques
 * + fiches de lieux, actualités publiées et profils pilotes réels.
 * Servi sur /sitemap.xml (aucun fichier statique de ce nom, sinon il masquerait
 * cette route). Les URL sont absolues (SITE_URL, qui inclut déjà BASE_URL).
 */
class SitemapController
{
    public function xml(): void
    {
        $base = defined('SITE_URL') ? SITE_URL : '';
        $urls = [];

        // Pages statiques publiques (priorité/fréquence indicatives).
        $urls[] = ['loc' => $base . '/',                'priority' => '1.0', 'freq' => 'daily'];
        $urls[] = ['loc' => $base . '/carte',           'priority' => '0.9', 'freq' => 'daily'];
        $urls[] = ['loc' => $base . '/pilotes',         'priority' => '0.7', 'freq' => 'weekly'];
        $urls[] = ['loc' => $base . '/actualites',      'priority' => '0.7', 'freq' => 'weekly'];
        $urls[] = ['loc' => $base . '/presentation',    'priority' => '0.5', 'freq' => 'monthly'];
        $urls[] = ['loc' => $base . '/confidentialite', 'priority' => '0.3', 'freq' => 'yearly'];
        $urls[] = ['loc' => $base . '/mentions-legales','priority' => '0.3', 'freq' => 'yearly'];

        // Contenus dynamiques (tolérant aux erreurs : une table absente ne casse
        // pas tout le sitemap).
        try {
            foreach (Lieu::tous(5000) as $lieu) {
                $urls[] = [
                    'loc'      => $base . '/lieu/' . (int) $lieu['id'],
                    'lastmod'  => self::jour($lieu['date_creation'] ?? null),
                    'priority' => '0.8',
                    'freq'     => 'weekly',
                ];
            }
        } catch (\Throwable) {
        }

        try {
            foreach (Actualite::pagePubliees(0, 1000) as $actu) {
                $urls[] = [
                    'loc'      => $base . '/actualite/' . (int) $actu['id'],
                    'lastmod'  => self::jour($actu['date_creation'] ?? null),
                    'priority' => '0.6',
                    'freq'     => 'monthly',
                ];
            }
        } catch (\Throwable) {
        }

        try {
            foreach (Utilisateur::tousAvecStats() as $pilote) {
                $urls[] = [
                    'loc'      => $base . '/pilote/' . (int) $pilote['id'],
                    'priority' => '0.5',
                    'freq'     => 'weekly',
                ];
            }
        } catch (\Throwable) {
        }

        header('Content-Type: application/xml; charset=UTF-8');

        $out = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $out .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            $out .= '  <url>' . "\n";
            $out .= '    <loc>' . self::esc($u['loc']) . '</loc>' . "\n";
            if (!empty($u['lastmod'])) {
                $out .= '    <lastmod>' . self::esc($u['lastmod']) . '</lastmod>' . "\n";
            }
            $out .= '    <changefreq>' . $u['freq'] . '</changefreq>' . "\n";
            $out .= '    <priority>' . $u['priority'] . '</priority>' . "\n";
            $out .= '  </url>' . "\n";
        }
        $out .= '</urlset>' . "\n";

        echo $out;
    }

    /** Date au format W3C (AAAA-MM-JJ), ou null si non exploitable. */
    private static function jour(mixed $valeur): ?string
    {
        if (!is_string($valeur) || $valeur === '') {
            return null;
        }
        $ts = strtotime($valeur);
        return $ts !== false ? date('Y-m-d', $ts) : null;
    }

    /** Échappement des entités XML dans une URL. */
    private static function esc(string $valeur): string
    {
        return htmlspecialchars($valeur, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
