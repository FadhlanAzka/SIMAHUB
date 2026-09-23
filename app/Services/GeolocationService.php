<?php

namespace App\Services;

class GeolocationService
{
    /**
     * Menghitung jarak antara dua koordinat latitude/longitude menggunakan rumus Haversine.
     * Mengembalikan jarak dalam satuan meter.
     */
    public static function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // Radius bumi dalam meter

        $lat1Rad = deg2rad($lat1);
        $lon1Rad = deg2rad($lon1);
        $lat2Rad = deg2rad($lat2);
        $lon2Rad = deg2rad($lon2);

        $deltaLat = $lat2Rad - $lat1Rad;
        $deltaLon = $lon2Rad - $lon1Rad;

        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
            cos($lat1Rad) * cos($lat2Rad) *
            sin($deltaLon / 2) * sin($deltaLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c);
    }

    /**
     * Memeriksa apakah lokasi pengguna berada dalam radius instansi.
     */
    public static function isWithinRadius(float $userLat, float $userLon, float $officeLat, float $officeLon, int $radiusMeters): bool
    {
        $distance = self::calculateDistance($userLat, $userLon, $officeLat, $officeLon);
        return $distance <= $radiusMeters;
    }
}
