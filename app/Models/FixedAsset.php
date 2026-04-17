<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class FixedAsset extends Model
{
    protected $fillable = [
        'company_id', 'branch_id', 'warehouse_id', 'assigned_to',
        'folio', 'name', 'category', 'brand', 'model', 'serial_number',
        'description', 'acquisition_date', 'acquisition_cost',
        'status', 'notes', 'is_active',
        'depreciation_method', 'useful_life_years', 'salvage_value',
        'fiscal_rate', 'accumulated_depreciation', 'current_book_value',
        'last_depreciation_date',
    ];

    protected $casts = [
        'acquisition_date'        => 'date',
        'acquisition_cost'        => 'decimal:2',
        'salvage_value'           => 'decimal:2',
        'fiscal_rate'             => 'decimal:4',
        'accumulated_depreciation'=> 'decimal:2',
        'current_book_value'      => 'decimal:2',
        'last_depreciation_date'  => 'date',
        'is_active'               => 'boolean',
    ];

    // Tasas fiscales SAT por categoría (LISR Art. 34)
    const FISCAL_RATES = [
        'computadora'  => 0.30,
        'vehiculo'     => 0.25,
        'mobiliario'   => 0.10,
        'maquinaria'   => 0.10,
        'herramienta'  => 0.35,
        'comunicacion' => 0.25,
        'otro'         => 0.10,
    ];

    const CATEGORIES = [
        'computadora'   => 'Computadora / Electrónico',
        'vehiculo'      => 'Vehículo',
        'mobiliario'    => 'Mobiliario y equipo de oficina',
        'maquinaria'    => 'Maquinaria e industria',
        'herramienta'   => 'Herramienta',
        'comunicacion'  => 'Equipo de comunicación',
        'otro'          => 'Otro',
    ];

    const STATUSES = [
        'active'          => 'Activo',
        'in_maintenance'  => 'En mantenimiento',
        'transferred'     => 'Transferido',
        'retired'         => 'Dado de baja',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(AssetTransfer::class, 'asset_id');
    }

    public function depreciations(): HasMany
    {
        return $this->hasMany(AssetDepreciation::class, 'fixed_asset_id');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(AssetLoan::class, 'fixed_asset_id');
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(AssetMaintenance::class, 'fixed_asset_id');
    }

    public function nextMaintenance()
    {
        return $this->hasOne(AssetMaintenance::class, 'fixed_asset_id')
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->orderBy('scheduled_date');
    }

    public function activeLoans(): HasMany
    {
        return $this->hasMany(AssetLoan::class, 'fixed_asset_id')->where('status', 'active');
    }

    public static function generateFolio(int $companyId): string
    {
        $count = self::where('company_id', $companyId)->count() + 1;
        return 'ACT-' . str_pad($count, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Calcula y registra la depreciación mensual del activo.
     * Devuelve el registro creado o null si no aplica.
     */
    public function runMonthlyDepreciation(int $year, int $month): ?AssetDepreciation
    {
        if (! $this->depreciation_method || ! $this->acquisition_cost || ! $this->useful_life_years) {
            return null;
        }

        $bookValue = $this->current_book_value ?? ($this->acquisition_cost - $this->salvage_value);
        if ($bookValue <= 0) {
            return null;
        }

        // Evitar duplicados
        if (AssetDepreciation::where('fixed_asset_id', $this->id)
            ->where('year', $year)->where('month', $month)->where('is_fiscal', false)->exists()) {
            return null;
        }

        $amount = $this->calculateMonthlyAmount($bookValue);
        $amount = min($amount, $bookValue); // no depreciar más del valor residual

        $accumulated = ($this->accumulated_depreciation ?? 0) + $amount;

        $record = AssetDepreciation::create([
            'company_id'               => $this->company_id,
            'fixed_asset_id'           => $this->id,
            'year'                     => $year,
            'month'                    => $month,
            'method'                   => $this->depreciation_method,
            'book_value_start'         => $bookValue,
            'depreciation_amount'      => $amount,
            'accumulated_depreciation' => $accumulated,
            'book_value_end'           => $bookValue - $amount,
            'is_fiscal'                => false,
        ]);

        $this->update([
            'accumulated_depreciation' => $accumulated,
            'current_book_value'       => $bookValue - $amount,
            'last_depreciation_date'   => Carbon::create($year, $month)->endOfMonth(),
        ]);

        return $record;
    }

    /**
     * Calcula y registra la depreciación fiscal mensual (tasa SAT).
     */
    public function runMonthlyFiscalDepreciation(int $year, int $month): ?AssetDepreciation
    {
        $rate = $this->fiscal_rate ?? (self::FISCAL_RATES[$this->category] ?? null);
        if (! $rate || ! $this->acquisition_cost) {
            return null;
        }

        if (AssetDepreciation::where('fixed_asset_id', $this->id)
            ->where('year', $year)->where('month', $month)->where('is_fiscal', true)->exists()) {
            return null;
        }

        $prevFiscalAccumulated = AssetDepreciation::where('fixed_asset_id', $this->id)
            ->where('is_fiscal', true)->sum('depreciation_amount');

        $monthlyAmount = ($this->acquisition_cost * $rate) / 12;
        $maxDepreciable = $this->acquisition_cost - ($this->salvage_value ?? 0);
        $remaining = $maxDepreciable - $prevFiscalAccumulated;

        if ($remaining <= 0) {
            return null;
        }

        $amount = min($monthlyAmount, $remaining);
        $accumulated = $prevFiscalAccumulated + $amount;
        $bookValueStart = $this->acquisition_cost - $prevFiscalAccumulated;

        return AssetDepreciation::create([
            'company_id'               => $this->company_id,
            'fixed_asset_id'           => $this->id,
            'year'                     => $year,
            'month'                    => $month,
            'method'                   => 'fiscal',
            'book_value_start'         => $bookValueStart,
            'depreciation_amount'      => $amount,
            'accumulated_depreciation' => $accumulated,
            'book_value_end'           => $bookValueStart - $amount,
            'is_fiscal'                => true,
        ]);
    }

    public function calculateMonthlyAmountPublic(float $bookValue): float
    {
        return $this->calculateMonthlyAmount($bookValue);
    }

    private function calculateMonthlyAmount(float $bookValue): float
    {
        $depreciableBase = $this->acquisition_cost - $this->salvage_value;
        $lifeMonths = $this->useful_life_years * 12;

        return match ($this->depreciation_method) {
            'linea_recta'  => $depreciableBase / $lifeMonths,
            'doble_saldo'  => $bookValue * (2 / ($this->useful_life_years * 12)),
            'suma_digitos' => $this->sumaDigitosMonthlyAmount($depreciableBase),
            default        => $depreciableBase / $lifeMonths,
        };
    }

    private function sumaDigitosMonthlyAmount(float $depreciableBase): float
    {
        $n = $this->useful_life_years * 12;
        $sumOfDigits = ($n * ($n + 1)) / 2;
        $monthsPassed = AssetDepreciation::where('fixed_asset_id', $this->id)
            ->where('is_fiscal', false)->count();
        $remaining = $n - $monthsPassed;
        return $remaining > 0 ? ($depreciableBase * $remaining) / $sumOfDigits : 0;
    }
}
