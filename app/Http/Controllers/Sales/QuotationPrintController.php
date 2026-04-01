<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\SaleQuotation;

class QuotationPrintController extends Controller
{
    public function __invoke(SaleQuotation $quotation)
    {
        $quotation->load([
            'company',
            'branch',
            'customer.phones',
            'customer.emails',
            'createdBy',
            'items.product',
        ]);

        return view('sales.quotation-print', compact('quotation'));
    }
}
