<?php

namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;

class OrderPrintController extends Controller
{
    public function __invoke(PurchaseOrder $order)
    {
        $order->load([
            'items.product',
            'supplier.phones',
            'supplier.emails',
            'supplier.contacts',
            'branch',
            'createdBy',
            'company',
            'requisition.branch',
            'requisition.finalQuotation.approvals.user',
        ]);

        return view('purchases.order-print', compact('order'));
    }
}
