<div class="space-y-6">

    {{-- Encabezado --}}
    <div class="flex items-start gap-3">
        <a wire:navigate href="{{ route('inventory.lots.index') }}"
           class="text-gray-400 hover:text-gray-600 transition-colors duration-200 mt-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-xl font-bold text-gray-900 font-mono">{{ $lot->lot_number }}</h1>
                @php
                    $statusColors = [
                        'active'   => 'bg-emerald-100 text-emerald-700 border border-emerald-200',
                        'depleted' => 'bg-gray-100 text-gray-500 border border-gray-200',
                        'expired'  => 'bg-red-100 text-red-600 border border-red-200',
                    ];
                @endphp
                <span class="text-xs px-2.5 py-1 rounded-lg font-medium {{ $statusColors[$lot->status] ?? 'bg-gray-100 text-gray-500' }}">
                    {{ \App\Models\ProductLot::STATUSES[$lot->status] ?? $lot->status }}
                </span>
            </div>
            <p class="text-sm text-gray-500 mt-0.5">{{ $lot->product->name }} — {{ $lot->warehouse->name }}</p>
        </div>
        <button onclick="window.print()"
            class="hidden sm:inline-flex items-center gap-2 px-4 py-2 text-sm border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors text-gray-600 cursor-pointer flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Imprimir etiqueta
        </button>
    </div>

    {{-- Info del lote + QR --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Info del lote --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2 border-b border-gray-100 pb-4 mb-5">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Datos del lote
            </h2>

            <dl class="grid grid-cols-2 gap-x-8 gap-y-5 text-sm">
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Producto</dt>
                    <dd class="font-semibold text-gray-900">{{ $lot->product->name }}</dd>
                    <dd class="text-xs text-gray-400 font-mono mt-0.5">{{ $lot->product->sku }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Almacén</dt>
                    <dd class="font-semibold text-gray-900">{{ $lot->warehouse->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Fecha de ingreso</dt>
                    <dd class="font-semibold text-gray-900">{{ $lot->entry_date->format('d/m/Y') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Fecha de vencimiento</dt>
                    <dd class="font-semibold {{ $lot->expiry_date ? ($lot->expiry_date->isPast() ? 'text-red-600' : ($lot->expiry_date->diffInDays() < 30 ? 'text-amber-600' : 'text-gray-900')) : 'text-gray-300' }}">
                        {{ $lot->expiry_date ? $lot->expiry_date->format('d/m/Y') : '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Cantidad inicial</dt>
                    <dd class="font-semibold text-gray-900">{{ number_format($lot->initial_quantity, 4) }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Cantidad disponible</dt>
                    <dd class="font-bold text-xl {{ (float)$lot->quantity > 0 ? 'text-gray-900' : 'text-red-500' }}">
                        {{ number_format($lot->quantity, 4) }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Costo unitario</dt>
                    <dd class="font-semibold text-gray-900">${{ number_format($lot->unit_cost, 4) }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Valor actual en lote</dt>
                    <dd class="font-bold text-indigo-700">
                        ${{ number_format((float)$lot->quantity * (float)$lot->unit_cost, 2) }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Referencia origen</dt>
                    <dd class="font-mono text-sm text-gray-700">{{ $lot->reference ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Notas</dt>
                    <dd class="text-gray-600">{{ $lot->notes ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        {{-- QR / Código de barras imprimible --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm flex flex-col items-center justify-center gap-4 print:border-2 print:border-gray-800">
            <div id="qrcode" class="w-40 h-40"></div>
            <div class="text-center space-y-0.5">
                <p class="font-mono font-bold text-sm text-gray-800">{{ $lot->lot_number }}</p>
                <p class="font-mono text-xs text-gray-500 break-all">{{ $lot->barcode }}</p>
                <p class="text-xs text-gray-500 font-medium mt-2">{{ $lot->product->name }}</p>
                <p class="text-xs text-gray-400">{{ $lot->warehouse->name }}</p>
                <p class="text-xs text-gray-400">Ingreso: {{ $lot->entry_date->format('d/m/Y') }}</p>
                <p class="text-sm font-bold text-gray-800 mt-2">Disponible: {{ number_format($lot->quantity, 2) }}</p>
            </div>
        </div>
    </div>

    {{-- Historial de movimientos --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h2 class="text-sm font-semibold text-gray-700">Historial de movimientos del lote</h2>
        </div>

        @if($lot->movementItems->isEmpty() && $lot->deliveryItems->isEmpty())
            <div class="py-12 text-center">
                <x-empty-state message="Sin movimientos registrados." />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-gray-600 uppercase tracking-wider">Tipo</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-gray-600 uppercase tracking-wider">Folio</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-gray-600 uppercase tracking-wider">Referencia</th>
                            <th class="text-right px-6 py-3.5 text-xs font-semibold text-gray-600 uppercase tracking-wider">Cantidad</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-gray-600 uppercase tracking-wider">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        {{-- Movimientos de almacén --}}
                        @foreach($lot->movementItems as $mi)
                            @php
                                $typeLabel = [
                                    'entry'      => ['Entrada',        'bg-emerald-100 text-emerald-700'],
                                    'exit'       => ['Salida',         'bg-red-100 text-red-600'],
                                    'adjustment' => ['Ajuste',         'bg-blue-100 text-blue-700'],
                                    'transfer'   => ['Transferencia',  'bg-violet-100 text-violet-700'],
                                    'return'     => ['Devolución',     'bg-amber-100 text-amber-700'],
                                ][$mi->movement->type] ?? [$mi->movement->type, 'bg-gray-100 text-gray-500'];
                                $isIn = in_array($mi->movement->type, ['entry', 'return']);
                            @endphp
                            <tr class="hover:bg-gray-50/70 transition-colors duration-150">
                                <td class="px-6 py-3.5">
                                    <span class="text-xs px-2.5 py-1 rounded-lg font-medium {{ $typeLabel[1] }}">{{ $typeLabel[0] }}</span>
                                </td>
                                <td class="px-6 py-3.5 font-mono text-gray-700 font-medium">{{ $mi->movement->folio }}</td>
                                <td class="px-6 py-3.5 text-gray-500">{{ $mi->movement->reference ?? '—' }}</td>
                                <td class="px-6 py-3.5 text-right font-bold {{ $isIn ? 'text-emerald-700' : 'text-red-600' }}">
                                    {{ $isIn ? '+' : '-' }}{{ number_format($mi->quantity, 4) }}
                                </td>
                                <td class="px-6 py-3.5 text-gray-500 text-xs whitespace-nowrap">{{ $mi->movement->moved_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach

                        {{-- Remisiones --}}
                        @foreach($lot->deliveryItems as $di)
                            <tr class="hover:bg-gray-50/70 transition-colors duration-150">
                                <td class="px-6 py-3.5">
                                    <span class="text-xs px-2.5 py-1 rounded-lg font-medium bg-teal-100 text-teal-700">Remisión</span>
                                </td>
                                <td class="px-6 py-3.5 font-mono text-gray-700 font-medium">{{ $di->delivery->folio }}</td>
                                <td class="px-6 py-3.5 text-gray-500">{{ $di->delivery->order?->folio ?? '—' }}</td>
                                <td class="px-6 py-3.5 text-right font-bold text-red-600">
                                    -{{ number_format($di->quantity, 4) }}
                                </td>
                                <td class="px-6 py-3.5 text-gray-500 text-xs whitespace-nowrap">{{ $di->delivery->delivered_at?->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>

{{-- QR con qrcode.js desde CDN --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" defer></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const el = document.getElementById('qrcode');
        if (el && typeof QRCode !== 'undefined') {
            new QRCode(el, {
                text: '{{ $lot->barcode }}',
                width: 160,
                height: 160,
                colorDark: '#1e293b',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.H,
            });
        }
    });
</script>

<style>
    @media print {
        body > * { display: none !important; }
        #qrcode,
        [id="qrcode"] ~ * { display: block !important; }
        .max-w-4xl > *:not(:nth-child(2)) { display: none !important; }
        .max-w-4xl > *:nth-child(2) { display: grid !important; }
        .lg\\:col-span-2 { display: none !important; }
    }
</style>
