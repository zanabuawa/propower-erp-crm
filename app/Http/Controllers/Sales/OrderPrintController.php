<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\SaleOrder;

class OrderPrintController extends Controller
{
    public function __invoke(SaleOrder $order)
    {
        $order->load([
            'company',
            'branch',
            'customer.phones',
            'customer.emails',
            'createdBy',
            'items.product',
            'quotation',
        ]);

        return view('sales.order-print', compact('order'));
    }
}
