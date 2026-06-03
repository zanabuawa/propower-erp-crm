<?php

namespace App\Traits;

/**
 * Lógica de tiers de descuento para formularios de venta.
 *
 * Tiers basados en cuánto del margen de utilidad puede descontar el vendedor:
 *   Tier 1 — 1/3 del margen  (permiso: apply discounts, nivel base)
 *   Tier 2 — 2/3 del margen  (permiso: discount level 2)
 *   Tier 3 — 100% del margen (permiso: discount level 3)
 *
 * El piso absoluto siempre es min_sale_price = costo / (1 - gastos_op%).
 * NUNCA se puede vender por debajo de este valor, ni con autorización.
 */
trait HandlesDiscountTier
{
    // ── Resolución de tier del usuario autenticado ───────────────────────────

    private function resolveUserTier(): int
    {
        $user = auth()->user();
        // Quien puede aprobar descuentos tiene acceso irrestricto al margen
        if ($user->hasPermissionTo('approve discounts') ||
            $user->hasPermissionTo('discount level 3')) {
            return 3;
        }
        if ($user->hasPermissionTo('discount level 2')) {
            return 2;
        }
        return 1;
    }

    private function tierFraction(int $tier): float
    {
        return match ($tier) {
            3       => 1.0,
            2       => 2 / 3,
            default => 1 / 3,
        };
    }

    /**
     * Precio mínimo que el tier permite para un ítem.
     * tier 3 → min_sale_price (piso absoluto)
     * tier 2 → min + 1/3 del margen restante
     * tier 1 → min + 2/3 del margen restante
     */
    private function itemTierFloor(float $unitPrice, float $minSalePrice, int $tier): float
    {
        $profitBand = max(0.0, $unitPrice - $minSalePrice);
        return $minSalePrice + $profitBand * (1.0 - $this->tierFraction($tier));
    }

    /**
     * Descuento máximo (%) de línea que el tier permite sin requerir autorización.
     */
    private function itemMaxDiscountPct(float $unitPrice, float $minSalePrice, int $tier): float
    {
        if ($unitPrice <= 0) return 0.0;
        $tierFloor = $this->itemTierFloor($unitPrice, $minSalePrice, $tier);
        return round(max(0.0, ($unitPrice - $tierFloor) / $unitPrice * 100), 4);
    }

    // ── Cap dinámico del descuento global ────────────────────────────────────

    /**
     * Descuento global máximo (%) que puede aplicarse dado el tier del usuario
     * y los descuentos de línea actuales, sin que ningún ítem cruce su tier-floor.
     *
     * Se recalcula en tiempo real porque depende de los descuentos de línea activos.
     */
    public function getMaxGlobalDiscountCapProperty(): float
    {
        if (empty($this->items)) return 100.0;

        $tier = $this->resolveUserTier();
        $caps = [];

        foreach ($this->items as $item) {
            $unitPrice = (float) ($item['unit_price']    ?? 0);
            $minPrice  = (float) ($item['min_sale_price'] ?? 0);
            $lineDsc   = (float) ($item['discount_pct']   ?? 0);

            if ($unitPrice <= 0) continue;

            $tierFloor      = $this->itemTierFloor($unitPrice, $minPrice, $tier);
            $priceAfterLine = $unitPrice * (1.0 - $lineDsc / 100.0);

            if ($priceAfterLine <= $tierFloor) {
                $caps[] = 0.0; // ya está en o por debajo del tier-floor con el descuento de línea
            } else {
                $caps[] = (1.0 - $tierFloor / $priceAfterLine) * 100.0;
            }
        }

        return empty($caps) ? 100.0 : round((float) min($caps), 4);
    }

    // ── Verificación de límites ──────────────────────────────────────────────

    /**
     * Verifica si algún ítem quedaría por debajo del piso absoluto (min_sale_price)
     * con los descuentos actuales. Esto es un bloqueo duro — no se puede guardar
     * ni con autorización del gerente.
     */
    private function isAbsoluteFloorBreached(): bool
    {
        $globalDisc = (float) $this->global_discount;

        foreach ($this->items as $item) {
            $unitPrice = (float) ($item['unit_price']    ?? 0);
            $minPrice  = (float) ($item['min_sale_price'] ?? 0);
            $lineDsc   = (float) ($item['discount_pct']   ?? 0);

            if ($unitPrice <= 0 || $minPrice <= 0) continue;

            $effectivePrice = $unitPrice * (1 - $lineDsc / 100) * (1 - $globalDisc / 100);
            if ($effectivePrice < $minPrice - 0.001) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verifica si algún descuento supera el tier-floor del usuario.
     * Retorna ['exceeds'=>bool, 'requested'=>float, 'max_allowed'=>float]
     *
     * Si exceeds=true el documento se puede guardar pero queda en estado
     * 'pending' esperando autorización del gerente.
     */
    private function checkDiscountLimits(): array
    {
        $globalDisc = (float) $this->global_discount;
        $tier       = $this->resolveUserTier();

        foreach ($this->items as $item) {
            $unitPrice  = (float) ($item['unit_price']    ?? 0);
            $minPrice   = (float) ($item['min_sale_price'] ?? 0);
            $lineDsc    = (float) ($item['discount_pct']   ?? 0);
            $maxLinePct = (float) ($item['max_discount_pct'] ?? 100);

            if ($unitPrice <= 0) continue;

            // 1. Descuento de línea supera el máximo del tier
            if ($lineDsc > $maxLinePct + 0.001) {
                $this->exceedingMaxPct = round($maxLinePct, 2);
                return ['exceeds' => true, 'requested' => round($lineDsc, 2), 'max_allowed' => round($maxLinePct, 2)];
            }

            // 2. Precio efectivo combinado (línea + global) cae bajo el tier-floor
            $tierFloor      = $this->itemTierFloor($unitPrice, $minPrice, $tier);
            $effectivePrice = $unitPrice * (1 - $lineDsc / 100) * (1 - $globalDisc / 100);

            if ($effectivePrice < $tierFloor - 0.001) {
                $effectivePct = round((1 - (1 - $lineDsc / 100) * (1 - $globalDisc / 100)) * 100, 2);
                $maxAllowed   = round((1 - $tierFloor / $unitPrice) * 100, 2);
                $this->exceedingMaxPct = $maxAllowed;
                return ['exceeds' => true, 'requested' => $effectivePct, 'max_allowed' => $maxAllowed];
            }
        }

        return ['exceeds' => false, 'requested' => 0.0, 'max_allowed' => 0.0];
    }

    // ── Helpers para addProduct() ────────────────────────────────────────────

    /**
     * Calcula los campos de descuento para un ítem recién agregado,
     * usando el tier del usuario autenticado.
     */
    private function resolveItemDiscountFields(float $price, float $cost, float $opCostsPct): array
    {
        $opDiv        = 1.0 - $opCostsPct / 100.0;
        $minSalePrice = $opDiv > 0 ? round($cost / $opDiv, 2) : 0.0;
        $tier         = $this->resolveUserTier();
        $maxDiscPct   = $this->itemMaxDiscountPct($price, $minSalePrice, $tier);

        return [
            'min_sale_price'   => $minSalePrice,
            'max_discount_pct' => $maxDiscPct,
        ];
    }
}
