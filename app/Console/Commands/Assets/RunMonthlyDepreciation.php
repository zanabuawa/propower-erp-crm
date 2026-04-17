<?php

namespace App\Console\Commands\Assets;

use App\Models\FixedAsset;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RunMonthlyDepreciation extends Command
{
    protected $signature = 'assets:depreciate
                            {--year= : Año (por defecto el actual)}
                            {--month= : Mes 1-12 (por defecto el anterior)}
                            {--fiscal : Calcular también depreciación fiscal SAT}
                            {--dry-run : Mostrar resultados sin guardar}';

    protected $description = 'Calcula y registra la depreciación mensual de activos fijos';

    public function handle(): int
    {
        $now   = Carbon::now();
        $year  = (int) ($this->option('year')  ?? $now->year);
        $month = (int) ($this->option('month') ?? $now->subMonth()->month);
        $fiscal  = $this->option('fiscal');
        $dryRun  = $this->option('dry-run');

        $this->info("Depreciación contable — período: {$month}/{$year}" . ($dryRun ? ' [DRY RUN]' : ''));

        $assets = FixedAsset::whereNotNull('depreciation_method')
            ->whereNotNull('useful_life_years')
            ->whereNotNull('acquisition_cost')
            ->where('is_active', true)
            ->where('status', 'active')
            ->get();

        if ($assets->isEmpty()) {
            $this->warn('No hay activos con configuración de depreciación.');
            return 0;
        }

        $this->table(
            ['Folio', 'Nombre', 'Método', 'Monto', 'Valor libro final'],
            $assets->map(function (FixedAsset $asset) use ($year, $month, $dryRun) {
                $bookValue = $asset->current_book_value ?? ($asset->acquisition_cost - $asset->salvage_value);
                $preview   = $asset->calculateMonthlyAmountPublic($bookValue);
                $preview   = min($preview, $bookValue);

                if (! $dryRun) {
                    $asset->runMonthlyDepreciation($year, $month);
                }

                return [
                    $asset->folio,
                    str($asset->name)->limit(30),
                    $asset->depreciation_method,
                    number_format($preview, 2),
                    number_format($bookValue - $preview, 2),
                ];
            })->toArray()
        );

        if ($fiscal) {
            $this->info("Depreciación fiscal SAT — período: {$month}/{$year}");
            $assets->each(function (FixedAsset $asset) use ($year, $month, $dryRun) {
                if (! $dryRun) {
                    $asset->runMonthlyFiscalDepreciation($year, $month);
                }
            });
            $this->info('Depreciación fiscal registrada.');
        }

        if ($dryRun) {
            $this->warn('Dry run — ningún cambio fue guardado.');
        } else {
            $this->info("Depreciación registrada para {$assets->count()} activo(s).");
        }

        return 0;
    }
}
