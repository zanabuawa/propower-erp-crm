<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Models\PepsKardex;
use App\Models\Product;
use App\Models\ProductLot;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseQuotation;
use App\Models\PurchaseQuotationApproval;
use App\Models\PurchaseQuotationItem;
use App\Models\PurchaseReceipt;
use App\Models\PurchaseReceiptItem;
use App\Models\PurchaseRequisition;
use App\Models\PurchaseRequisitionItem;
use App\Models\PurchaseSetting;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\StockMovementItem;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class PurchaseSeeder extends Seeder
{
    public function run(): void
    {
        $company   = Company::first();
        $branch    = Branch::where('code', 'MAT')->first();
        $almacen   = Warehouse::where('code', 'ALM-MAT')->first();
        $comprador = User::where('email', 'comprador@miempresa.com')->first();
        $gerente   = User::where('email', 'gerente@miempresa.com')->first();
        $empleado  = User::where('email', 'empleado@miempresa.com')->first();
        $proveedor = Supplier::where('internal_code', 'PROV-001')->first();
        $prov2     = Supplier::where('internal_code', 'PROV-002')->first();
        $banco     = FinanceAccount::where('code', 'BNK-BBVA')->first();

        // Configuración de autorización si no existe
        PurchaseSetting::firstOrCreate(
            ['company_id' => $company->id],
            ['currency' => 'MXN', 'level1_amount' => 5000, 'level2_amount' => 50000]
        );

        // ── Orden de compra 1: Completada (materiales eléctricos) ────────────
        if (! PurchaseOrder::where('folio', 'OC-000001')->exists()) {
            $this->createCompletedOrder(
                $company, $branch, $almacen, $comprador, $proveedor, $banco,
                'OC-000001',
                [
                    ['sku' => 'CAB-THW-12',  'qty' => 500, 'price' => 8.00,  'tax' => 0],
                    ['sku' => 'CAB-THW-10',  'qty' => 300, 'price' => 11.80, 'tax' => 0],
                    ['sku' => 'INT-1P-15A',  'qty' =>  30, 'price' => 80.00, 'tax' => 16],
                    ['sku' => 'INT-1P-20A',  'qty' =>  20, 'price' => 90.00, 'tax' => 16],
                    ['sku' => 'TAB-20C',     'qty' =>   5, 'price' => 800.00,'tax' => 16],
                ],
                now()->subDays(45)
            );
        }

        // ── Orden de compra 2: Completada (herramientas y EPP) ───────────────
        if (! PurchaseOrder::where('folio', 'OC-000002')->exists()) {
            $this->createCompletedOrder(
                $company, $branch, $almacen, $comprador, $prov2, $banco,
                'OC-000002',
                [
                    ['sku' => 'HER-DEST-PH8', 'qty' => 10, 'price' => 80.00,  'tax' => 16],
                    ['sku' => 'MED-MULT-DIG',  'qty' =>  3, 'price' => 620.00, 'tax' => 16],
                ],
                now()->subDays(30)
            );
        }

        // ── Orden de compra 3: Enviada (pendiente de recepción) ──────────────
        if (! PurchaseOrder::where('folio', 'OC-000003')->exists()) {
            $this->createSentOrder(
                $company, $branch, $comprador, $proveedor,
                'OC-000003',
                [
                    ['sku' => 'CON-EMT-34', 'qty' => 300, 'price' => 17.50, 'tax' => 0],
                    ['sku' => 'CON-RGD-1',  'qty' => 150, 'price' => 33.00, 'tax' => 0],
                    ['sku' => 'TAB-12C',    'qty' =>   8, 'price' => 550.00,'tax' => 16],
                ],
                now()->subDays(5)
            );
        }

        // ── Requisición con flujo completo de autorización ───────────────────
        if (! PurchaseRequisition::where('folio', 'REQ-000001')->exists()) {
            $this->createAuthorizedRequisition($company, $branch, $empleado, $comprador, $gerente, $proveedor);
        }
    }

    private function createCompletedOrder(
        $company, $branch, $almacen, $comprador, $supplier, $banco,
        string $folio, array $lines, $date
    ): void {
        $subtotal = collect($lines)->sum(fn($l) => $l['qty'] * $l['price']);
        $tax      = collect($lines)->sum(fn($l) => $l['qty'] * $l['price'] * ($l['tax'] / 100));
        $total    = $subtotal + $tax;

        $order = PurchaseOrder::create([
            'company_id'   => $company->id,
            'branch_id'    => $branch->id,
            'supplier_id'  => $supplier->id,
            'created_by'   => $comprador->id,
            'folio'        => $folio,
            'currency'     => 'MXN',
            'status'       => 'received',
            'subtotal'     => $subtotal,
            'tax'          => $tax,
            'total'        => $total,
            'payment_terms'=> 30,
            'expected_at'  => $date->copy()->addDays(7),
            'created_at'   => $date,
            'updated_at'   => $date,
        ]);

        foreach ($lines as $line) {
            $product = Product::where('sku', $line['sku'])->first();
            if (! $product) continue;

            PurchaseOrderItem::create([
                'purchase_order_id'  => $order->id,
                'product_id'         => $product->id,
                'supplier_id'        => $supplier->id,
                'description'        => $product->name,
                'quantity'           => $line['qty'],
                'quantity_received'  => $line['qty'],
                'unit_price'         => $line['price'],
                'tax_rate'           => $line['tax'],
                'subtotal'           => $line['qty'] * $line['price'],
            ]);
        }

        // Recepción
        $recFolio = 'REC-' . str_pad(PurchaseReceipt::count() + 1, 6, '0', STR_PAD_LEFT);
        $receipt = PurchaseReceipt::create([
            'company_id'        => $company->id,
            'purchase_order_id' => $order->id,
            'received_by'       => $comprador->id,
            'warehouse_id'      => $almacen->id,
            'folio'             => $recFolio,
            'status'            => 'completed',
            'reception_type'    => 'purchase',
            'received_at'       => $date->copy()->addDays(7),
            'created_at'        => $date->copy()->addDays(7),
        ]);

        $movement = StockMovement::create([
            'company_id'   => $company->id,
            'warehouse_id' => $almacen->id,
            'user_id'      => $comprador->id,
            'type'         => 'entry',
            'folio'        => $recFolio,
            'status'       => 'confirmed',
            'reference'    => $order->folio,
            'moved_at'     => $date->copy()->addDays(7),
        ]);

        foreach ($lines as $line) {
            $product = Product::where('sku', $line['sku'])->first();
            if (! $product) continue;

            PurchaseReceiptItem::create([
                'purchase_receipt_id' => $receipt->id,
                'product_id'          => $product->id,
                'warehouse_id'        => $almacen->id,
                'quantity_received'   => $line['qty'],
            ]);

            $stock = Stock::firstOrCreate(
                ['product_id' => $product->id, 'warehouse_id' => $almacen->id],
                ['quantity' => 0]
            );

            $qBefore = (float) $stock->quantity;
            $stock->increment('quantity', $line['qty']);

            $lotNumber = 'LOTE-' . $product->sku . '-REC-' . substr($recFolio, -3);
            $lot = ProductLot::create([
                'company_id'       => $company->id,
                'product_id'       => $product->id,
                'warehouse_id'     => $almacen->id,
                'lot_number'       => $lotNumber,
                'initial_quantity' => $line['qty'],
                'quantity'         => $line['qty'],
                'unit_cost'        => $line['price'],
                'entry_date'       => $date->copy()->addDays(7),
                'reference'        => $recFolio,
                'status'           => 'active',
            ]);

            PepsKardex::create([
                'company_id'      => $company->id,
                'product_id'      => $product->id,
                'lot_id'          => $lot->id,
                'warehouse_id'    => $almacen->id,
                'movement_type'   => 'purchase',
                'direction'       => 'in',
                'quantity'        => $line['qty'],
                'unit_cost'       => $line['price'],
                'total_cost'      => $line['qty'] * $line['price'],
                'balance_quantity' => $qBefore + $line['qty'],
                'balance_value'   => ($qBefore + $line['qty']) * $line['price'],
                'lot_number'      => $lotNumber,
                'reference'       => $recFolio,
                'moved_at'        => $date->copy()->addDays(7),
            ]);

            StockMovementItem::create([
                'stock_movement_id' => $movement->id,
                'product_id'        => $product->id,
                'warehouse_id'      => $almacen->id,
                'quantity'          => $line['qty'],
                'unit_price'        => $line['price'],
                'quantity_before'   => $qBefore,
                'quantity_after'    => $qBefore + $line['qty'],
            ]);
        }

        // Transacción financiera
        if ($banco) {
            FinanceTransaction::create([
                'account_id'       => $banco->id,
                'registered_by'    => $comprador->id,
                'folio'            => 'TXN-' . $recFolio,
                'type'             => 'egreso',
                'concept'          => 'Compra: ' . $recFolio . ' — OC ' . $folio,
                'category'         => 'compra',
                'amount'           => $total,
                'currency'         => 'MXN',
                'exchange_rate'    => 1,
                'transaction_date' => $date->copy()->addDays(7)->toDateString(),
                'reference'        => $recFolio,
                'status'           => 'confirmado',
            ]);
        }
    }

    private function createSentOrder(
        $company, $branch, $comprador, $supplier,
        string $folio, array $lines, $date
    ): void {
        $subtotal = collect($lines)->sum(fn($l) => $l['qty'] * $l['price']);
        $tax      = collect($lines)->sum(fn($l) => $l['qty'] * $l['price'] * ($l['tax'] / 100));

        $order = PurchaseOrder::create([
            'company_id'   => $company->id,
            'branch_id'    => $branch->id,
            'supplier_id'  => $supplier->id,
            'created_by'   => $comprador->id,
            'folio'        => $folio,
            'currency'     => 'MXN',
            'status'       => 'sent',
            'subtotal'     => $subtotal,
            'tax'          => $tax,
            'total'        => $subtotal + $tax,
            'payment_terms'=> 30,
            'expected_at'  => $date->copy()->addDays(10),
            'created_at'   => $date,
        ]);

        foreach ($lines as $line) {
            $product = Product::where('sku', $line['sku'])->first();
            if (! $product) continue;

            PurchaseOrderItem::create([
                'purchase_order_id' => $order->id,
                'product_id'        => $product->id,
                'supplier_id'       => $supplier->id,
                'description'       => $product->name,
                'quantity'          => $line['qty'],
                'quantity_received' => 0,
                'unit_price'        => $line['price'],
                'tax_rate'          => $line['tax'],
                'subtotal'          => $line['qty'] * $line['price'],
            ]);
        }
    }

    private function createAuthorizedRequisition(
        $company, $branch, $empleado, $comprador, $gerente, $supplier
    ): void {
        $req = PurchaseRequisition::create([
            'company_id'   => $company->id,
            'branch_id'    => $branch->id,
            'requested_by' => $empleado->id,
            'reviewed_by'  => $comprador->id,
            'folio'        => 'REQ-000001',
            'currency'     => 'MXN',
            'status'       => 'authorized',
            'justification'=> 'Reposición de materiales eléctricos para proyecto planta industrial.',
            'needed_by'    => now()->addDays(15),
            'submitted_at' => now()->subDays(10),
            'confirmed_at' => now()->subDays(8),
        ]);

        $items = [
            ['sku' => 'CAB-THW-12', 'qty' => 200, 'price' => 8.50, 'tax' => 0],
            ['sku' => 'CAB-THHW-8', 'qty' =>  50, 'price' => 28.00,'tax' => 0],
            ['sku' => 'INT-2P-60A', 'qty' =>   5, 'price' => 420.00,'tax' => 16],
        ];

        foreach ($items as $line) {
            $product = Product::where('sku', $line['sku'])->first();
            if (! $product) continue;

            PurchaseRequisitionItem::create([
                'purchase_requisition_id' => $req->id,
                'product_id'              => $product->id,
                'description'             => $product->name,
                'quantity'                => $line['qty'],
                'unit'                    => $product->unitOfMeasure?->abbreviation ?? 'H87',
                'unit_price'              => $line['price'],
            ]);
        }

        $subtotal = collect($items)->sum(fn($l) => $l['qty'] * $l['price']);
        $tax      = collect($items)->sum(fn($l) => $l['qty'] * $l['price'] * ($l['tax'] / 100));
        $total    = $subtotal + $tax;

        // Cotización final autorizada
        $quotation = PurchaseQuotation::create([
            'company_id'              => $company->id,
            'purchase_requisition_id' => $req->id,
            'type'                    => 'final',
            'status'                  => 'authorized',
            'subtotal'                => $subtotal,
            'tax'                     => $tax,
            'total'                   => $total,
            'created_by'              => $comprador->id,
            'notes'                   => 'Cotización final aprobada por gerencia.',
        ]);

        foreach ($items as $line) {
            $product = Product::where('sku', $line['sku'])->first();
            if (! $product) continue;

            PurchaseQuotationItem::create([
                'purchase_quotation_id' => $quotation->id,
                'product_id'            => $product->id,
                'description'           => $product->name,
                'quantity'              => $line['qty'],
                'unit'                  => $product->unitOfMeasure?->abbreviation ?? 'H87',
                'unit_price'            => $line['price'],
                'tax_rate'              => $line['tax'],
                'subtotal'              => $line['qty'] * $line['price'],
            ]);
        }

        PurchaseQuotationApproval::create([
            'purchase_quotation_id' => $quotation->id,
            'user_id'               => $gerente->id,
            'role'                  => 'gerente',
            'level'                 => 1,
            'status'                => 'approved',
            'comments'              => 'Aprobado. Proceder con la compra.',
            'decided_at'            => now()->subDays(7),
        ]);
    }
}
