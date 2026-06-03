<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrAttendanceLocation extends Model
{
    use BelongsToCompany;

    protected $table = 'hr_attendance_locations';

    protected $fillable = [
        'company_id', 'branch_id', 'name', 'address',
        'latitude', 'longitude', 'radius_meters', 'is_active', 'notes',
    ];

    protected $casts = [
        'latitude'      => 'decimal:7',
        'longitude'     => 'decimal:7',
        'radius_meters' => 'integer',
        'is_active'     => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(HrAttendance::class, 'location_id');
    }

    /**
     * Calcula distancia en metros usando fórmula de Haversine.
     */
    public function distanceTo(float $lat, float $lng): float
    {
        $earthRadius = 6371000; // metros

        $latFrom = deg2rad((float) $this->latitude);
        $latTo   = deg2rad($lat);
        $dLat    = deg2rad($lat - (float) $this->latitude);
        $dLng    = deg2rad($lng - (float) $this->longitude);

        $a = sin($dLat / 2) ** 2
           + cos($latFrom) * cos($latTo) * sin($dLng / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * Verifica si unas coordenadas están dentro del radio permitido.
     */
    public function contains(float $lat, float $lng, float $toleranceMeters = 0): bool
    {
        return $this->distanceTo($lat, $lng) <= ($this->radius_meters + max(0, $toleranceMeters));
    }

    /**
     * Busca la primera zona activa de la empresa que contenga las coordenadas.
     */
    public static function findContaining(int $companyId, float $lat, float $lng, float $toleranceMeters = 0): ?self
    {
        return self::where('company_id', $companyId)
            ->where('is_active', true)
            ->get()
            ->first(fn($loc) => $loc->contains($lat, $lng, $toleranceMeters));
    }
}
