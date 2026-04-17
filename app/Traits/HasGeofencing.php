<?php

namespace App\Traits;

trait HasGeofencing
{
    /**
     * Calcula la distancia en metros entre dos puntos usando la fórmula de Haversine.
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // Radio de la Tierra en metros

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Verifica si el usuario está dentro del radio permitido de una sucursal o ubicación.
     */
    public function isWithinAllowedRadius(float $userLat, float $userLon, float $targetLat, float $targetLon, int $allowedRadius): bool
    {
        $distance = $this->calculateDistance($userLat, $userLon, $targetLat, $targetLon);
        return $distance <= $allowedRadius;
    }
}
