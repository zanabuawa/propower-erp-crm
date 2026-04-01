<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\SaleInvoice;
use App\Services\FacturamaService;

class InvoiceDownloadController extends Controller
{
    public function __invoke(SaleInvoice $invoice, string $type, FacturamaService $facturama)
    {
        abort_unless(
            in_array($type, ['pdf', 'xml']) && $invoice->cfdi_uuid,
            404
        );

        abort_unless(
            auth()->user()->company_id === $invoice->company_id,
            403
        );

        $fileType = $type === 'pdf'
            ? \Facturama\Client::FILE_TYPE_PDF
            : \Facturama\Client::FILE_TYPE_XML;

        $result  = $facturama->client()->get("cfdi/{$fileType}/issued/{$invoice->cfdi_uuid}");
        $content = base64_decode($result->Content ?? (is_array($result) ? end($result) : ''));

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
