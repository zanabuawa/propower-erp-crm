<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\SaleInvoice;
use App\Services\FacturapiService;

class InvoiceDownloadController extends Controller
{
    public function __invoke(SaleInvoice $invoice, string $type, FacturapiService $facturapi)
    {
        abort_unless(
            in_array($type, ['pdf', 'xml']) && $invoice->cfdi_uuid && $invoice->facturapi_id,
            404
        );

        abort_unless(
            auth()->user()->company_id === $invoice->company_id,
            403
        );

        $content = $type === 'pdf'
            ? $facturapi->downloadPdf($invoice->facturapi_id)
            : $facturapi->downloadXml($invoice->facturapi_id);

        $mimeType    = $type === 'pdf' ? 'application/pdf' : 'application/xml';
        $filename    = $invoice->folio . '.' . $type;
        $disposition = ($type === 'pdf' && request()->boolean('inline'))
            ? 'inline'
            : 'attachment';

        return response($content, 200, [
            'Content-Type'        => $mimeType,
            'Content-Disposition' => "{$disposition}; filename=\"{$filename}\"",
        ]);
    }
}
