<?php

namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Models\PurchaseInvoice;
use Illuminate\Support\Facades\Storage;

class PurchaseInvoiceDownloadController extends Controller
{
    public function __invoke(PurchaseInvoice $invoice, string $type)
    {
        abort_unless(in_array($type, ['pdf', 'xml']), 404);
        abort_unless(auth()->user()->company_id === $invoice->company_id, 403);

        $path = $type === 'pdf' ? $invoice->pdf_path : $invoice->xml_path;
        abort_if(! $path || ! Storage::disk('local')->exists($path), 404);

        $mimeType = $type === 'pdf' ? 'application/pdf' : 'application/xml';
        $filename = $invoice->folio . '_' . $invoice->supplier_invoice_number . '.' . $type;

        return response()->stream(function () use ($path) {
            echo Storage::disk('local')->get($path);
        }, 200, [
            'Content-Type'        => $mimeType,
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
