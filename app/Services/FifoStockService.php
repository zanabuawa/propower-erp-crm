<?php

namespace App\Services;

use App\Models\PepsKardex;
use App\Models\ProductLot;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;

class FifoStockService
{
    // ── PEPS: sugerencia de consumo ──────────────────────────────────────────

    /**
     * Sugiere cómo consumir lotes en orden PEPS (más antiguo primero)
     * para cubrir la cantidad solicitada.
     *
     * @return array  [['lot_id', 'lot_number', 'barcode', 'entry_date', 'available', 'quantity', 'unit_cost'], ...]
     */
    public function suggestAllocations(int $productId, int $warehouseId, float $qtyNeeded): array
    {
        $lots = ProductLot::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->where('status', 'active')
            ->where('quantity', '>', 0)
            ->orderBy('entry_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $allocations = [];
        $remaining   = $qtyNeeded;

        foreach ($lots as $lot) {
            if ($remaining <= 0) break;

            $toUse = min((float) $lot->quantity, $remaining);
            $allocations[] = [
                'lot_id'      => $lot->id,
                'lot_number'  => $lot->lot_number,
                'barcode'     => $lot->barcode,
                'entry_date'  => $lot->entry_date?->format('Y-m-d'),
                'expiry_date' => $lot->expiry_date?->format('Y-m-d'),
                'available'   => (float) $lot->quantity,
                'quantity'    => round($toUse, 4),
                'unit_cost'   => (float) $lot->unit_cost,
            ];
            $remaining -= $toUse;
        }

        return $allocations;
    }

    /**
     * Cantidad total disponible en lotes activos para un producto en un almacén.
     */
    public function availableInLots(int $productId, int $warehouseId): float
    {
        return (float) ProductLot::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->where('status', 'active')
            ->sum('quantity');
    }

    // ── Lotes: creación (entradas) ───────────────────────────────────────────

    /**
     * Crea un lote nuevo y registra el asiento PEPS de entrada en el kardex.
     */
    public function createLot(
        int     $companyId,
        int     $productId,
        int     $warehouseId,
        float   $quantity,
        float   $unitCost,
        string  $reference    = '',
        ?string $expiryDate   = null,
        string  $notes        = '',
        string  $movementType = 'purchase',  // purchase | return_sale | adjustment_in | transfer_in
        ?\DateTime $movedAt   = null
    ): ProductLot {
        $lotNumber = ProductLot::generateLotNumber($companyId, $productId);
        $barcode   = ProductLot::generateBarcode($companyId, $productId);
        [$balanceQty, $balanceVal] = $this->lotBalance($productId, $warehouseId);

        $lot = ProductLot::create([
            'company_id'       => $companyId,
            'product_id'       => $productId,
            'warehouse_id'     => $warehouseId,
            'lot_number'       => $lotNumber,
            'barcode'          => $barcode,
            'initial_quantity' => $quantity,
            'quantity'         => $quantity,
            'unit_cost'        => $unitCost,
            'entry_date'       => today(),
            'expiry_date'      => $expiryDate ?: null,
            'reference'        => $reference ?: null,
            'status'           => 'active',
            'notes'            => $notes ?: null,
        ]);

        $this->recordKardex(
            companyId:     $companyId,
            productId:     $productId,
            lotId:         $lot->id,
            warehouseId:   $warehouseId,
            movementType:  $movementType,
            direction:     'in',
            quantity:      $quantity,
            unitCost:      $unitCost,
            unitPrice:     null,
            reference:     $reference,
            lotNumber:     $lotNumber,
            balanceQty:    $balanceQty,
            balanceVal:    $balanceVal,
            notes:         $notes,
            movedAt:       $movedAt ?? now(),
        );

        return $lot;
    }

    // ── Lotes: consumo (salidas) ─────────────────────────────────────────────

    /**
     * Consume lotes en orden PEPS y registra asientos de salida en el kardex.
     *
     * @param array  $allocations  [['lot_id', 'lot_number', 'quantity', 'unit_cost'], ...]
     * @param float  $unitSalePrice  Precio de venta unitario (0 si no es venta)
     * @param string $movementType  sale | return_purchase | transfer_out | internal_use | scrap | adjustment_out | other
     * @param string $reference     Folio del documento origen
     * @param int    $companyId
     * @param int    $warehouseId
     * @param \DateTime|null $movedAt
     * @param string $notes
     */
    public function consumeLots(
        array      $allocations,
        float      $unitSalePrice = 0,
        string     $movementType  = 'sale',
        string     $reference     = '',
        int        $companyId     = 0,
        int        $warehouseId   = 0,
        ?\DateTime $movedAt       = null,
        string     $notes         = ''
    ): void {
        foreach ($allocations as $alloc) {
            $qty = (float) ($alloc['quantity'] ?? 0);
            if ($qty <= 0) continue;

            $lot = ProductLot::find($alloc['lot_id']);
            if (!$lot) continue;

            [$balanceQty, $balanceVal] = $this->lotBalance($lot->product_id, $warehouseId);

            // Decrementar cantidad del lote
            $lot->quantity = max(0, (float) $lot->quantity - $qty);
            $lot->save();
            $lot->checkAndMarkDepleted();

            // Si tenemos contexto de empresa/almacén, registrar kardex
            if ($companyId && $warehouseId) {
                $unitCost   = (float) ($alloc['unit_cost'] ?? $lot->unit_cost);
                $totalCost  = round($qty * $unitCost, 2);
                $totalRev   = $unitSalePrice > 0 ? round($qty * $unitSalePrice, 2) : null;
                $profit     = ($totalRev !== null) ? round($totalRev - $totalCost, 2) : null;
                $profitPct  = ($profit !== null && $totalCost > 0) ? round($profit / $totalCost * 100, 4) : null;

                $this->recordKardex(
                    companyId:    $companyId,
                    productId:    $lot->product_id,
                    lotId:        $lot->id,
                    warehouseId:  $warehouseId,
                    movementType: $movementType,
                    direction:    'out',
                    quantity:     $qty,
                    unitCost:     $unitCost,
                    unitPrice:    $unitSalePrice > 0 ? $unitSalePrice : null,
                    reference:    $reference,
                    lotNumber:    $alloc['lot_number'] ?? $lot->lot_number,
                    balanceQty:   $balanceQty,
                    balanceVal:   $balanceVal,
                    notes:        $notes,
                    movedAt:      $movedAt ?? now(),
                    profit:       $profit,
                    profitPct:    $profitPct,
                    totalCost:    $totalCost,
                    totalRevenue: $totalRev,
                );
            }
        }
    }

    // ── Kardex: acceso rápido ────────────────────────────────────────────────

    /**
     * Saldo actual (cantidad y valor) de un producto en un almacén,
     * calculado a partir del kardex registrado.
     * Si no hay kardex, devuelve el saldo de la tabla stocks.
     *
     * @return [float $qty, float $value]
     */
    public function currentBalance(int $productId, int $warehouseId): array
    {
        $last = PepsKardex::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->orderBy('moved_at', 'desc')
            ->orderBy('id', 'desc')
            ->first(['balance_quantity', 'balance_value']);

        if ($last) {
            return [(float) $last->balance_quantity, (float) $last->balance_value];
        }

        // Fallback: leer de stocks
        $stock = Stock::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        $qty = $stock ? (float) $stock->quantity : 0;
        return [$qty, 0.0];
    }

    // ── Lotes: transferencia entre almacenes ────────────────────────────────────

    /**
     * Mueve lotes PEPS de un almacén origen a uno destino preservando toda la
     * información del lote original. Si el número de lote ya existe en destino
     * se le agrega sufijo "T" (o "T1", "T2"...) para indicar que proviene de
     * una transferencia y evitar colisiones.
     *
     * @param string $movementTypeOut  Tipo kardex para la salida  (p.ej. 'transfer_out')
     * @param string $movementTypeIn   Tipo kardex para la entrada (p.ej. 'transfer_in')
     */
    public function moveLotsBetweenWarehouses(
        int        $companyId,
        int        $productId,
        int        $originWarehouseId,
        int        $destinationWarehouseId,
        float      $quantity,
        string     $reference        = '',
        string     $movementTypeOut  = 'transfer_out',
        string     $movementTypeIn   = 'transfer_in',
        string     $notes            = '',
        ?\DateTime $movedAt          = null
    ): void {
        $allocations = $this->suggestAllocations($productId, $originWarehouseId, $quantity);

        if (empty($allocations)) {
            return;
        }

        $this->consumeLots(
            allocations:    $allocations,
            unitSalePrice:  0,
            movementType:   $movementTypeOut,
            reference:      $reference,
            companyId:      $companyId,
            warehouseId:    $originWarehouseId,
            movedAt:        $movedAt ?? now(),
            notes:          $notes,
        );

        foreach ($allocations as $alloc) {
            $qty = (float) ($alloc['quantity'] ?? 0);
            if ($qty <= 0) continue;

            $sourceLot = ProductLot::find($alloc['lot_id']);
            $unitCost  = (float) ($alloc['unit_cost'] ?? ($sourceLot?->unit_cost ?? 0));

            $lotNumber = $this->resolveTransferLotNumber(
                $alloc['lot_number'],
                $companyId,
                $destinationWarehouseId
            );
            $barcode = ProductLot::generateBarcode($companyId, $productId);
            [$balanceQty, $balanceVal] = $this->lotBalance($productId, $destinationWarehouseId);

            $newLot = ProductLot::create([
                'company_id'       => $companyId,
                'product_id'       => $productId,
                'warehouse_id'     => $destinationWarehouseId,
                'lot_number'       => $lotNumber,
                'barcode'          => $barcode,
                'initial_quantity' => $qty,
                'quantity'         => $qty,
                'unit_cost'        => $unitCost,
                'entry_date'       => today(),
                'expiry_date'      => $sourceLot?->expiry_date,
                'reference'        => $reference ?: null,
                'status'           => 'active',
                'notes'            => $notes ?: null,
            ]);

            $this->recordKardex(
                companyId:    $companyId,
                productId:    $productId,
                lotId:        $newLot->id,
                warehouseId:  $destinationWarehouseId,
                movementType: $movementTypeIn,
                direction:    'in',
                quantity:     $qty,
                unitCost:     $unitCost,
                unitPrice:    null,
                reference:    $reference,
                lotNumber:    $lotNumber,
                balanceQty:   $balanceQty,
                balanceVal:   $balanceVal,
                notes:        $notes,
                movedAt:      $movedAt ?? now(),
            );
        }
    }

    /**
     * Resuelve el número de lote para destino evitando colisiones.
     * Primero intenta el número original; si existe, agrega "T",
     * y si también existe, prueba "T1", "T2", etc.
     */
    private function resolveTransferLotNumber(
        string $baseLotNumber,
        int    $companyId,
        int    $warehouseId
    ): string {
        $exists = fn(string $n) => ProductLot::where('company_id', $companyId)
            ->where('warehouse_id', $warehouseId)
            ->where('lot_number', $n)
            ->exists();

        // Lotes por transferencia siempre llevan sufijo T
        $candidate = $baseLotNumber . 'T';
        if (!$exists($candidate)) {
            return $candidate;
        }

        $i = 1;
        while ($exists($baseLotNumber . 'T' . $i)) {
            $i++;
        }

        return $baseLotNumber . 'T' . $i;
    }

    // ── Interno: crear asiento en kardex ─────────────────────────────────────

    private function lotBalance(int $productId, int $warehouseId): array
    {
        $lots = ProductLot::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->where('quantity', '>', 0)
            ->get(['quantity', 'unit_cost']);

        $quantity = 0.0;
        $value = 0.0;

        foreach ($lots as $lot) {
            $lotQuantity = (float) $lot->quantity;
            $quantity += $lotQuantity;
            $value += $lotQuantity * (float) $lot->unit_cost;
        }

        return [round($quantity, 4), round($value, 2)];
    }

    private function recordKardex(
        int        $companyId,
        int        $productId,
        ?int       $lotId,
        int        $warehouseId,
        string     $movementType,
        string     $direction,
        float      $quantity,
        float      $unitCost,
        ?float     $unitPrice,
        string     $reference,
        string     $lotNumber,
        float      $balanceQty,
        float      $balanceVal,
        string     $notes,
        \DateTime  $movedAt,
        ?float     $profit      = null,
        ?float     $profitPct   = null,
        ?float     $totalCost   = null,
        ?float     $totalRevenue = null
    ): void {
        $tc  = $totalCost   ?? round($quantity * $unitCost, 2);
        $tr  = $totalRevenue ?? ($unitPrice ? round($quantity * $unitPrice, 2) : null);
        $prf = $profit ?? ($tr !== null ? round($tr - $tc, 2) : null);
        $pct = $profitPct ?? ($prf !== null && $tc > 0 ? round($prf / $tc * 100, 4) : null);

        // Actualizar saldo corrido
        if ($direction === 'in') {
            $newQty = $balanceQty + $quantity;
            $newVal = $balanceVal + $tc;
        } else {
            $newQty = $balanceQty - $quantity;
            $newVal = $balanceVal - $tc;
        }

        PepsKardex::create([
            'company_id'       => $companyId,
            'product_id'       => $productId,
            'lot_id'           => $lotId,
            'warehouse_id'     => $warehouseId,
            'movement_type'    => $movementType,
            'direction'        => $direction,
            'quantity'         => $quantity,
            'unit_cost'        => $unitCost,
            'total_cost'       => $tc,
            'unit_price'       => $unitPrice,
            'total_revenue'    => $tr,
            'profit'           => $prf,
            'profit_pct'       => $pct,
            'balance_quantity' => max(0, $newQty),
            'balance_value'    => max(0, $newVal),
            'reference'        => $reference ?: null,
            'lot_number'       => $lotNumber ?: null,
            'moved_at'         => $movedAt,
            'notes'            => $notes ?: null,
        ]);
    }
}
