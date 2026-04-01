<?php

namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Models\PurchaseRequisition;

class RequisitionPrintController extends Controller
{
    public function __invoke(PurchaseRequisition $requisition)
    {
        $requisition->load([
            'company',
            'branch',
            'requestedBy',
            'reviewedBy',
            'items.product',
            'finalQuotation.items',
            'finalQuotation.approvals.user',
        ]);

        return view('purchases.requisition-print', compact('requisition'));
    }
}
