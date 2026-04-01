<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-medium text-gray-900">Cotizaciones</h1>
            <p class="text-sm text-gray-500 mt-0.5">Gestiona tus cotizaciones y órdenes de venta</p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a wire:navigate href="{{ route('sales.orders.index') }}"
                class="text-sm border border-gray-200 hover:bg-gray-50 px-3 py-2 rounded-lg transition text-gray-600">
                Órdenes de venta
            </a>
            <a wire:navigate href="{{ route('sales.invoices.index') }}"
                class="text-sm border border-gray-200 hover:bg-gray-50 px-3 py-2 rounded-lg transition text-gray-600">
                Facturas
            </a>
            <a wire:navigate href="{{ route('sales.quotations.create') }}"
                class="inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                + Nueva cotización
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
        <input wire:model.live.debounce.300ms="search" type="text"
            placeholder="Buscar por folio o cliente..."
            class="col-span-1 sm:col-span-2 border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
        <select wire:model.live="filterStatus"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
            <option value="">Todos los estados</option>
            @foreach(\App\Models\SaleQuotation::STATUS as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[580px]">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Folio</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Cliente</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-gray-500 hidden md:table-cell">Productos</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Total</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-gray-500 hidden sm:table-cell">Válida hasta</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Estado</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($quotations as $quotation)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-3">
                                <span class="font-mono text-xs font-medium text-gray-900">{{ $quotation->folio }}</span>
                                <p class="text-xs text-gray-400 sm:hidden">{{ $quotation->customer->name }}</p>
                            </td>
                            <td class="px-5 py-3 text-gray-700">{{ $quotation->customer->name }}</td>
                            <td class="px-5 py-3 text-gray-600 hidden md:table-cell">{{ $quotation->items_count }}</td>
                            <td class="px-5 py-3 font-medium text-gray-900">
                                {{ $quotation->currency }} ${{ number_format($quotation->total, 2) }}
                            </td>
                            <td class="px-5 py-3 text-gray-600 hidden sm:table-cell">{{ $quotation->valid_until?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ \App\Models\SaleQuotation::STATUS_COLORS[$quotation->status] ?? '' }}">
                                    {{ \App\Models\SaleQuotation::STATUS[$quotation->status] ?? $quotation->status }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a wire:navigate href="{{ route('sales.quotations.show', $quotation) }}"
                                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Ver detalle</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-gray-400 text-sm">
                                No se encontraron cotizaciones.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($quotations->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">{{ $quotations->links() }}</div>
        @endif
    </div>
</div>
