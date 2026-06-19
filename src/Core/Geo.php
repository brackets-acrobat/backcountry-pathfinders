<?php

declare(strict_types=1);

namespace App\Core;

/*
 * Utilitaires géographiques.
 * Sert à la déduplication des lieux : MariaDB 10.4 n'a pas ST_Distance_Sphere,
 * donc on pré-filtre par boîte englobante (indexable) puis on affine en haversine.
 */
class Geo
{
    /** Rayon moyen de la Terre, en mètres. */
    public const RAYON_TERRE_M = 6371000.0;

    /** Distance haversine entre deux points (en mètres). */
    public static function distanceM(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return 2 * self::RAYON_TERRE_M * asin(min(1.0, sqrt($a)));
    }

    /**
     * Boîte englobante (carré approximatif) autour d'un point pour un rayon donné.
     * Sert de pré-filtre rapide sur l'index (latitude, longitude).
     *
     * @return array{latMin: float, latMax: float, lonMin: float, lonMax: float}
     */
    public static function boiteEnglobante(float $lat, float $lon, float $rayonM): array
    {
        $deltaLat = $rayonM / 111320.0;                              // 1° de latitude ≈ 111,32 km
        $cos = cos(deg2rad($lat));
        $deltaLon = $rayonM / (111320.0 * ($cos !== 0.0 ? $cos : 1e-9)); // resserré vers les pôles

        return [
            'latMin' => $lat - $deltaLat,
            'latMax' => $lat + $deltaLat,
            'lonMin' => $lon - $deltaLon,
            'lonMax' => $lon + $deltaLon,
        ];
    }
}
