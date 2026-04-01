<?php

namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Models\PurchaseReceipt;

class ReceiptPrintController extends Controller
{
    public function __invoke(PurchaseReceipt $receipt)
    {
        $receipt->load([
            'items.product',
            'items.warehouse',
            'order.supplier',
            'order.branch',
            'order.items',
            'receivedBy',
            'company',
        ]);

        return view('purchases.receipt-print', compact('receipt'));
    }
}
