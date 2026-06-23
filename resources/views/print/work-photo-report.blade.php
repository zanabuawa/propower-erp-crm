<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte fotografico - {{ $project->name }}</title>
    <style>
        @page { size: letter; margin: 12mm; }
        html, body { margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; color: #111827; font-size: 12px; }
        .print-btn { position: fixed; top: 16px; right: 16px; background: #4f46e5; color: #fff; border: 0; padding: 8px 18px; border-radius: 8px; font-weight: 700; cursor: pointer; }
        @media print { .print-btn { display: none; } }
        .print-page { position: relative; height: 255mm; padding-bottom: 16mm; box-sizing: border-box; overflow: hidden; page-break-after: always; break-after: page; page-break-inside: avoid; break-inside: avoid; }
        .print-page.last-page { page-break-after: auto; break-after: auto; }
        .page-content { text-align: center; }
        .page-content .section,
        .page-content .custom-body { text-align: left; }
        .client-table { width: 100%; border-collapse: collapse; margin-bottom: 22px; table-layout: fixed; }
        .client-table td, .client-table th { border: 1px solid #111827; padding: 8px 10px; vertical-align: middle; }
        .client-logo-cell { width: 240px; text-align: center; }
        .client-logo { min-height: 140px; display: flex; align-items: center; justify-content: center; }
        .client-logo img { max-height: 130px; max-width: 215px; object-fit: contain; }
        .placeholder { width: 104px; height: 104px; border-radius: 10px; background: #eef2ff; color: #4f46e5; display: inline-flex; align-items: center; justify-content: center; font-size: 36px; font-weight: 800; }
        .table-title { text-align: center; font-size: 16px; font-weight: 800; text-transform: uppercase; letter-spacing: .08em; background: #f3f4f6; }
        .label { width: 115px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .08em; color: #374151; background: #f9fafb; }
        .value { font-size: 13px; font-weight: 600; color: #111827; }
        .section { margin-top: 14px; }
        .section h2 { font-size: 12px; text-transform: uppercase; letter-spacing: .08em; color: #374151; border-bottom: 1px solid #e5e7eb; padding-bottom: 6px; }
        .text { white-space: pre-line; line-height: 1.5; color: #1f2937; }
        .custom-body { white-space: normal; line-height: 1.55; color: #1f2937; }
        .custom-body h1 { margin: 20px 0 12px; font-size: 20px; font-weight: 800; text-transform: uppercase; letter-spacing: .08em; color: #111827; }
        .custom-body h2 { margin: 18px 0 10px; padding-bottom: 6px; border-bottom: 1px solid #e5e7eb; font-size: 15px; font-weight: 800; text-transform: uppercase; letter-spacing: .1em; color: #1f2937; }
        .custom-body h3 { margin: 14px 0 8px; font-size: 13px; font-weight: 700; color: #1f2937; }
        .custom-body p { margin: 0 0 10px; }
        .custom-body ul { margin: 0 0 12px; padding-left: 24px; list-style: disc; }
        .custom-body ol { margin: 0 0 12px; padding-left: 24px; list-style: decimal; }
        .custom-body li { margin-bottom: 5px; display: list-item; }
        .custom-body blockquote { margin: 12px 0; padding-left: 14px; border-left: 4px solid #c7d2fe; color: #4b5563; font-style: italic; }
        .details { margin-bottom: 16px; color: #374151; line-height: 1.45; }
        .photos { display: block; text-align: center; }
        .photo-row { text-align: center; margin-bottom: 14px; break-inside: avoid; page-break-inside: avoid; }
        .photo { display: inline-block; vertical-align: top; margin: 0 6px 12px; border: 0; padding: 0; background: transparent; break-inside: avoid; page-break-inside: avoid; }
        .photo img { width: 100%; object-fit: contain; display: block; }
        .page-footer { position: absolute; left: 0; right: 0; bottom: 0; height: 14mm; display: flex; align-items: center; justify-content: center; border-top: 1px solid #e5e7eb; }
        .footer-logo { position: absolute; left: 50%; top: 54%; transform: translate(-50%, -50%); line-height: 1; }
        .footer-logo img { max-height: 42px; max-width: 170px; object-fit: contain; }
        .page-number { position: absolute; right: 0; top: 54%; transform: translateY(-50%); width: 110px; text-align: right; font-size: 10px; font-weight: 800; color: #4b5563; text-transform: uppercase; letter-spacing: .08em; white-space: nowrap; }
        .footer-placeholder { display: inline-block; font-weight: 800; color: #4b5563; letter-spacing: .08em; text-transform: uppercase; }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">Imprimir / Guardar PDF</button>

    @php
        $companyLogo = $company?->print_logo ?? $company?->logo;
        $customerLogo = $customer?->image;
        $place = $report->location
            ?: collect([$customer?->address, $customer?->city, $customer?->state])->filter()->join(', ');
        $weekStart = $report->week_start ?: $report->report_date?->copy()->startOfWeek();
        $weekEnd = $report->week_end ?: $report->report_date?->copy()->endOfWeek();
        $weekLabel = $weekStart && $weekEnd
            ? $weekStart->format('d/m/Y') . ' - ' . $weekEnd->format('d/m/Y')
            : null;
        $urlForPhoto = function ($path) {
                if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                    return $path;
                }

                if (str_starts_with($path, '/storage/')) {
                    return asset(ltrim($path, '/'));
                }

                return asset('storage/' . ltrim($path, '/'));
            };
        $layout = collect($report->photo_layout ?? [])
            ->filter(fn ($item) => is_array($item) && isset($item['path']))
            ->keyBy('path');
        $photoItems = collect($report->photos ?? [])
            ->map(function ($path) use ($layout, $urlForPhoto, $report) {
                $item = $layout->get($path, []);
                $scale = max(25, min(100, (int) ($item['scale'] ?? $item['width'] ?? 50)));

                return [
                    'path' => $path,
                    'scale' => $scale,
                    'height' => (int) round(560 * ($scale / 100)),
                    'hidden' => false,
                    'url' => $urlForPhoto($path),
                    'page' => max(1, (int) ($item['page'] ?? 1)),
                    'page_date' => $item['page_date'] ?? null,
                    'order' => array_search($path, array_column($report->photo_layout ?? [], 'path'), true),
                ];
            })
            ->sortBy(fn ($item) => str_pad((string) $item['page'], 5, '0', STR_PAD_LEFT) . '-' . str_pad((string) ($item['order'] === false ? PHP_INT_MAX : $item['order']), 12, '0', STR_PAD_LEFT))
            ->filter(fn ($item) => ! $item['hidden'])
            ->all();

        $pages = [];
        $formatPageDate = fn ($date) => $date
            ? \Carbon\Carbon::parse($date)->format('d/m/Y')
            : ($report->report_date?->format('d/m/Y') ?? 'N/A');

        $maxPage = max(1, collect($photoItems)->max('page') ?? 1);
        for ($pageNumber = 1; $pageNumber <= $maxPage; $pageNumber++) {
            $pagePhotos = collect($photoItems)
                ->filter(fn ($photo) => (int) $photo['page'] === $pageNumber)
                ->values();

            $rows = [];
            $row = [];
            $rowWidth = 0;
            $pageDate = $pagePhotos->first()['page_date'] ?? null;

            foreach ($pagePhotos as $photo) {
                $width = min(100, (int) $photo['scale']);
                if ($row && $rowWidth + $width > 100) {
                    $rows[] = ['photos' => $row];
                    $row = [];
                    $rowWidth = 0;
                }

                $pageDate ??= $photo['page_date'] ?? null;
                $row[] = $photo;
                $rowWidth += $width;
            }

            if ($row) {
                $rows[] = ['photos' => $row];
            }

            $pages[] = [
                'intro' => $pageNumber === 1,
                'rows' => $rows,
                'date' => $formatPageDate($pageDate),
            ];
        }
    @endphp

    @foreach($pages as $page)
        <div class="print-page {{ $loop->last ? 'last-page' : '' }}">
            <table class="client-table">
                <tr>
                    <td class="client-logo-cell" rowspan="6">
                        <div class="client-logo">
                            @if($customerLogo)
                                <img src="{{ asset('storage/' . $customerLogo) }}" alt="{{ $customer?->name }}">
                            @else
                                <div class="placeholder">{{ mb_strtoupper(mb_substr($customer?->name ?? 'C', 0, 1)) }}</div>
                            @endif
                        </div>
                    </td>
                    <th class="table-title" colspan="2">Reporte fotografico</th>
                </tr>
                <tr><td class="label">Cliente</td><td class="value">{{ $customer?->name ?? 'Sin cliente' }}</td></tr>
                <tr><td class="label">Lugar</td><td class="value">{{ $place ?: 'N/A' }}</td></tr>
                <tr><td class="label">Fecha</td><td class="value">{{ $page['date'] }}</td></tr>
                <tr><td class="label">Obra</td><td class="value">{{ $project->name }}</td></tr>
                <tr><td class="label">Concepto</td><td class="value">{{ $report->title }}</td></tr>
            </table>

            <div class="page-content">
                @if($page['intro'])
                    @if($report->custom_body)
                        <div class="section custom-body">{!! $report->custom_body !!}</div>
                    @elseif($report->description)
                        <div class="section">
                            <h2>Descripcion</h2>
                            <div class="text">{{ $report->description }}</div>
                        </div>
                    @endif

                    <div class="section">
                        <h2>Evidencia fotografica</h2>
                    </div>
                @endif

                <div class="section">
                    <div class="photos">
                        @forelse($page['rows'] as $row)
                            <div class="photo-row">
                                @foreach($row['photos'] as $photo)
                                    <div class="photo" style="width: calc({{ $photo['scale'] }}% - 12px);">
                                        <img src="{{ $photo['url'] }}" alt="Evidencia fotografica" style="height: {{ $photo['height'] }}px;">
                                    </div>
                                @endforeach
                            </div>
                        @empty
                            <p>Sin fotografias cargadas.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="page-footer">
                <div class="footer-logo">
                    @if($companyLogo)
                        <img src="{{ asset('storage/' . $companyLogo) }}" alt="{{ $company?->name }}">
                    @else
                        <span class="footer-placeholder">{{ $company?->name ?? 'ProPower' }}</span>
                    @endif
                </div>
                <div class="page-number">Pagina {{ $loop->iteration }}</div>
            </div>
        </div>
    @endforeach

    <script>
        window.addEventListener('load', async () => {
            const images = Array.from(document.images);

            await Promise.all(images.map(async (image) => {
                if (! image.complete) {
                    await new Promise((resolve) => {
                        image.addEventListener('load', resolve, { once: true });
                        image.addEventListener('error', resolve, { once: true });
                    });
                }

                if (image.decode) {
                    try {
                        await image.decode();
                    } catch (error) {
                        return;
                    }
                }
            }));

            setTimeout(() => window.print(), 250);
        });
    </script>
</body>
</html>
