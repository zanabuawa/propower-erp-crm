<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Customer;
use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Models\PepsKardex;
use App\Models\Product;
use App\Models\ProductLot;
use App\Models\SaleDelivery;
use App\Models\SaleDeliveryItem;
use App\Models\SaleInvoice;
use App\Models\SaleInvoiceItem;
use App\Models\SaleOrder;
use App\Models\SaleOrderItem;
use App\Models\SalePayment;
use App\Models\SaleQuotation;
use App\Models\SaleQuotationItem;
use App\Models\Stock;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class SalesSeeder extends Seeder
{
    public function run(): void
    {
        $company  = Company::first();
        $branch   = Branch::where('code', 'MAT')->first();
        $almacen  = Warehouse::where('code', 'ALM-MAT')->first();
        $vendedor = User::where('email', 'vendedor@miempresa.com')->first();
        $banco    = FinanceAccount::where('code', 'BNK-BBVA')->first();
        $caja     = FinanceAccount::where('code', 'CAJ-001')->first();

        $cliente1 = Customer::where('rfc', 'CPH850215FFF')->first(); // Constructora Pérez
        $cliente2 = Customer::where('rfc', 'IMN920710GGG')->first(); // Industrias Metálicas
        $cliente3 = Customer::where('rfc', 'PIC981105JJJ')->first(); // Planta Industrial

        // ── Cotización 1: Aceptada → Orden pagada ───────────────────────────
        if (! SaleQuotation::where('folio', 'COT-000001')->exists()) {
            $this->createPaidOrder($company, $branch, $almacen, $vendedor, $cliente1, $banco);
        }

        // ── Cotización 2: Orden entregada, pendiente de factura ──────────────
        if (! SaleQuotation::where('folio', 'COT-000002')->exists()) {
            $this->createDeliveredOrder($company, $branch, $almacen, $vendedor, $cliente2);
        }

        // ── Cotización 3: En proceso (pendiente de aceptar) ──────────────────
        if (! SaleQuotation::where('folio', 'COT-000003')->exists()) {
            $this->createPendingQuotation($company, $branch, $vendedor, $cliente3);
        }
    }

    private function createPaidOrder($company, $branch, $almacen, $vendedor, $customer, $banco): void
    {
        $lines = [
            ['sku' => 'CAB-THW-12',  'qty' => 200, 'price' => 11.05, 'tax' => 16],
            ['sku' => 'TAB-20C',     'qty' =>   3, 'price' => 1147.50,'tax' => 16],
            ['sku' => 'INT-1P-15A',  'qty' =>  20, 'price' => 119.00, 'tax' => 16],
            ['sku' => 'INT-1P-20A',  'qty' =>  10, 'price' => 133.00, 'tax' => 16],
            ['sku' => 'SRV-INST-ELEC','qty'=> 8,  'price' => 450.00, 'tax' => 16],
        ];

        $subtotal = collect($lines)->sum(fn($l) => $l['qty'] * $l['price']);
        $tax      = collect($lines)->sum(fn($l) => $l['qty'] * $l['price'] * ($l['tax'] / 100));
        $total    = $subtotal + $tax;

        $quotation = SaleQuotation::create([
            'company_id'  => $company->id,
            'branch_id'   => $branch->id,
            'customer_id' => $customer->id,
            'created_by'  => $vendedor->id,
            'folio'       => 'COT-000001',
            'currency'    => 'MXN',
            'status'      => 'accepted',
            'subtotal'    => $subtotal,
            'tax'         => $tax,
            'total'       => $total,
            'valid_days'  => 15,
            'valid_until' => now()->subDays(20)->addDays(15),
            'notes'       => 'Incluye materiales e instalación para primera fase del proyecto.',
            'created_at'  => now()->subDays(35),
        ]);

        foreach ($lines as $line) {
            $product = Product::where('sku', $line['sku'])->first();
            if (! $product) continue;
            SaleQuotationItem::create([
                'sale_quotation_id' => $quotation->id,
                'product_id'        => $product->id,
                'description'       => $product->name,
                'quantity'          => $line['qty'],
                'unit_price'        => $line['price'],
                'tax_rate'          => $line['tax'],
                'subtotal'          => $line['qty'] * $line['price'],
            ]);
        }

        $order = SaleOrder::create([
            'company_id'       => $company->id,
            'branch_id'        => $branch->id,
            'customer_id'      => $customer->id,
            'sale_quotation_id'=> $quotation->id,
            'created_by'       => $vendedor->id,
            'folio'            => 'OV-000001',
            'currency'         => 'MXN',
            'status'           => 'invoiced',
            'payment_method'   => 'transfer',
            'payment_terms'    => 30,
            'subtotal'         => $subtotal,
            'tax'              => $tax,
            'total'            => $total,
            'created_at'       => now()->subDays(30),
        ]);

        $orderItems = []; // productId => orderItemId
        foreach ($lines as $line) {
            $product = Product::where('sku', $line['sku'])->first();
            if (! $product) continue;
            $oi = SaleOrderItem::create([
                'sale_order_id' => $order->id,
                'product_id'    => $product->id,
                'description'   => $product->name,
                'quantity'      => $line['qty'],
                'unit_price'    => $line['price'],
                'tax_rate'      => $line['tax'],
                'subtotal'      => $line['qty'] * $line['price'],
            ]);
            $orderItems[$product->id] = $oi->id;
        }

        // Entrega
        $delivery = SaleDelivery::create([
            'company_id'    => $company->id,
            'sale_order_id' => $order->id,
            'customer_id'   => $customer->id,
            'warehouse_id'  => $almacen->id,
            'created_by'    => $vendedor->id,
            'folio'         => 'REM-000001',
            'status'        => 'delivered',
            'delivered_at'  => now()->subDays(25),
            'created_at'    => now()->subDays(25),
        ]);

        foreach ($lines as $line) {
            $product = Product::where('sku', $line['sku'])->first();
            if (! $product || $product->type === 'service') continue;
            if (! isset($orderItems[$product->id])) continue;

            SaleDeliveryItem::create([
                'sale_delivery_id'  => $delivery->id,
                'sale_order_item_id'=> $orderItems[$product->id],
                'product_id'        => $product->id,
                'warehouse_id'      => $almacen->id,
                'quantity'          => $line['qty'],
            ]);

            // Descontar FIFO / PEPS
            $lot = ProductLot::where('product_id', $product->id)
                ->where('warehouse_id', $almacen->id)
                ->where('status', 'active')
                ->where('quantity', '>=', $line['qty'])
                ->first();

            if ($lot) {
                $lot->decrement('quantity', $line['qty']);
                Stock::where('product_id', $product->id)
                    ->where('warehouse_id', $almacen->id)
                    ->decrement('quantity', $line['qty']);

                $cost = $lot->unit_cost;
                PepsKardex::create([
                    'company_id'      => $company->id,
                    'product_id'      => $product->id,
                    'lot_id'          => $lot->id,
                    'warehouse_id'    => $almacen->id,
                    'movement_type'   => 'sale',
                    'direction'       => 'out',
                    'quantity'        => $line['qty'],
                    'unit_cost'       => $cost,
                    'total_cost'      => $line['qty'] * $cost,
                    'unit_price'      => $line['price'],
                    'total_revenue'   => $line['qty'] * $line['price'],
                    'profit'          => $line['qty'] * ($line['price'] - $cost),
                    'profit_pct'      => $cost > 0 ? round((($line['price'] - $cost) / $cost) * 100, 2) : 0,
                    'balance_quantity' => max(0, $lot->quantity),
                    'balance_value'   => max(0, $lot->quantity) * $cost,
                    'lot_number'      => $lot->lot_number,
                    'reference'       => $delivery->folio,
                    'moved_at'        => now()->subDays(25),
                ]);
            }
        }

        // Factura
        $invoice = SaleInvoice::create([
            'company_id'     => $company->id,
            'sale_order_id'  => $order->id,
            'customer_id'    => $customer->id,
            'created_by'     => $vendedor->id,
            'folio'          => 'FAC-000001',
            'type'           => 'internal',
            'currency'       => 'MXN',
            'status'         => 'paid',
            'payment_method' => 'transfer',
            'subtotal'       => $subtotal,
            'tax'            => $tax,
            'total'          => $total,
            'paid_amount'    => $total,
            'issued_at'      => now()->subDays(25),
            'due_at'         => now()->subDays(25)->addDays(30),
            'created_at'     => now()->subDays(25),
        ]);

        foreach ($lines as $line) {
            $product = Product::where('sku', $line['sku'])->first();
            if (! $product) continue;
            SaleInvoiceItem::create([
                'sale_invoice_id' => $invoice->id,
                'product_id'      => $product->id,
                'description'     => $product->name,
                'quantity'        => $line['qty'],
                'unit_price'      => $line['price'],
                'tax_rate'        => $line['tax'],
                'subtotal'        => $line['qty'] * $line['price'],
            ]);
        }

        // Pago
        $payFolio = 'PAG-000001';
        SalePayment::create([
            'company_id'         => $company->id,
            'sale_invoice_id'    => $invoice->id,
            'customer_id'        => $customer->id,
            'created_by'         => $vendedor->id,
            'finance_account_id' => $banco->id,
            'folio'              => $payFolio,
            'currency'           => 'MXN',
            'payment_method'     => 'transfer',
            'status'             => 'applied',
            'amount'             => $total,
            'paid_at'            => now()->subDays(20),
        ]);

        if ($banco) {
            FinanceTransaction::create([
                'account_id'       => $banco->id,
                'registered_by'    => $vendedor->id,
                'folio'            => 'TXN-' . $payFolio,
                'type'             => 'ingreso',
                'concept'          => 'Cobro: ' . $invoice->folio . ' — ' . $customer->name,
                'category'         => 'venta',
                'amount'           => $total,
                'currency'         => 'MXN',
                'exchange_rate'    => 1,
                'transaction_date' => now()->subDays(20)->toDateString(),
                'reference'        => $payFolio,
                'status'           => 'confirmado',
            ]);
        }
    }

    private function createDeliveredOrder($company, $branch, $almacen, $vendedor, $customer): void
    {
        $lines = [
            ['sku' => 'CON-EMT-34',  'qty' => 100, 'price' => 23.40, 'tax' => 16],
            ['sku' => 'CON-RGD-1',   'qty' =>  50, 'price' => 44.80, 'tax' => 16],
            ['sku' => 'EPP-CAS-AMAR','qty' =>  10, 'price' => 252.00,'tax' => 16],
            ['sku' => 'EPP-GUANTE-D','qty' =>   8, 'price' => 607.50,'tax' => 16],
        ];

        $subtotal = collect($lines)->sum(fn($l) => $l['qty'] * $l['price']);
        $tax      = collect($lines)->sum(fn($l) => $l['qty'] * $l['price'] * ($l['tax'] / 100));
        $total    = $subtotal + $tax;

        SaleQuotation::create([
            'company_id'  => $company->id,
            'branch_id'   => $branch->id,
            'customer_id' => $customer->id,
            'created_by'  => $vendedor->id,
            'folio'       => 'COT-000002',
            'currency'    => 'MXN',
            'status'      => 'accepted',
            'subtotal'    => $subtotal,
            'tax'         => $tax,
            'total'       => $total,
            'valid_days'  => 15,
            'valid_until' => now()->addDays(5),
            'created_at'  => now()->subDays(15),
        ]);

        $order = SaleOrder::create([
            'company_id'    => $company->id,
            'branch_id'     => $branch->id,
            'customer_id'   => $customer->id,
            'created_by'    => $vendedor->id,
            'folio'         => 'OV-000002',
            'currency'      => 'MXN',
            'status'        => 'delivered',
            'payment_method'=> 'transfer',
            'payment_terms' => 30,
            'subtotal'      => $subtotal,
            'tax'           => $tax,
            'total'         => $total,
            'created_at'    => now()->subDays(12),
        ]);

        $orderItems2 = []; // productId => orderItemId
        foreach ($lines as $line) {
            $product = Product::where('sku', $line['sku'])->first();
            if (! $product) continue;
            $oi = SaleOrderItem::create([
                'sale_order_id' => $order->id,
                'product_id'    => $product->id,
                'description'   => $product->name,
                'quantity'      => $line['qty'],
                'unit_price'    => $line['price'],
                'tax_rate'      => $line['tax'],
                'subtotal'      => $line['qty'] * $line['price'],
            ]);
            $orderItems2[$product->id] = $oi->id;
        }

        $delivery = SaleDelivery::create([
            'company_id'    => $company->id,
            'sale_order_id' => $order->id,
            'customer_id'   => $customer->id,
            'warehouse_id'  => $almacen->id,
            'created_by'    => $vendedor->id,
            'folio'         => 'REM-000002',
            'status'        => 'delivered',
            'delivered_at'  => now()->subDays(8),
            'created_at'    => now()->subDays(8),
        ]);

        foreach ($lines as $line) {
            $product = Product::where('sku', $line['sku'])->first();
            if (! $product) continue;
            if (! isset($orderItems2[$product->id])) continue;

            SaleDeliveryItem::create([
                'sale_delivery_id'  => $delivery->id,
                'sale_order_item_id'=> $orderItems2[$product->id],
                'product_id'        => $product->id,
                'warehouse_id'      => $almacen->id,
                'quantity'          => $line['qty'],
            ]);

            $lot = ProductLot::where('product_id', $product->id)
                ->where('warehouse_id', $almacen->id)
                ->where('status', 'active')
                ->where('quantity', '>=', $line['qty'])
                ->first();

            if ($lot) {
                $lot->decrement('quantity', $line['qty']);
                Stock::where('product_id', $product->id)
                    ->where('warehouse_id', $almacen->id)
                    ->decrement('quantity', $line['qty']);
            }
        }
    }

    private function createPendingQuotation($company, $branch, $vendedor, $customer): void
    {
        $lines = [
            ['sku' => 'CAB-THW-10',   'qty' => 500, 'price' => 16.00, 'tax' => 16],
            ['sku' => 'CAB-THHW-8',   'qty' => 100, 'price' => 35.00, 'tax' => 16],
            ['sku' => 'TAB-20C',      'qty' =>   8, 'price' => 1147.50,'tax' => 16],
            ['sku' => 'SRV-INST-ELEC','qty' =>  40, 'price' => 450.00, 'tax' => 16],
        ];

        $subtotal = collect($lines)->sum(fn($l) => $l['qty'] * $l['price']);
        $tax      = collect($lines)->sum(fn($l) => $l['qty'] * $l['price'] * ($l['tax'] / 100));

        $quotation = SaleQuotation::create([
            'company_id'  => $company->id,
            'branch_id'   => $branch->id,
            'customer_id' => $customer->id,
            'created_by'  => $vendedor->id,
            'folio'       => 'COT-000003',
            'currency'    => 'MXN',
            'status'      => 'sent',
            'subtotal'    => $subtotal,
            'tax'         => $tax,
            'total'       => $subtotal + $tax,
            'valid_days'  => 15,
            'valid_until' => now()->addDays(10),
            'notes'       => 'Cotización para instalación eléctrica completa de planta de manufactura. Segunda fase.',
            'created_at'  => now()->subDays(3),
        ]);

        foreach ($lines as $line) {
            $product = Product::where('sku', $line['sku'])->first();
            if (! $product) continue;
            SaleQuotationItem::create([
                'sale_quotation_id' => $quotation->id,
                'product_id'        => $product->id,
                'description'       => $product->name,
                'quantity'          => $line['qty'],
                'unit_price'        => $line['price'],
                'tax_rate'          => $line['tax'],
                'subtotal'          => $line['qty'] * $line['price'],
            ]);
        }
    }
}
