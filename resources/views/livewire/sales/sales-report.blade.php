<div class="max-w-7xl mx-auto space-y-5">

    {{-- ── Header ──────────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between px-1">
        <div>
            <h1 class="text-xl font-medium text-gray-900">Reporte de Ventas</h1>
            <p class="text-sm text-gray-500 mt-0.5">Análisis detallado por período, cliente, producto y vendedor</p>
        </div>
        <a wire:navigate href="{{ route('sales.dashboard') }}"
            class="flex items-center gap-1.5 text-sm text-indigo-600 hover:text-indigo-800 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Dashboard
        </a>
    </div>

    {{-- ── Filtros ──────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-4">

        {{-- Tipo de reporte --}}
        <div class="flex flex-wrap gap-2">
            @foreach([
                'invoices'   => 'Facturas',
                'orders'     => 'Órdenes de venta',
                'quotations' => 'Cotizaciones',
                'products'   => 'Por producto',
            ] as $val => $label)
                <button type="button" wire:click="$set('reportType', '{{ $val }}')"
                    class="px-3 py-1.5 text-sm rounded-lg font-medium border transition
                        {{ $reportType === $val
                            ? 'bg-indigo-600 text-white border-indigo-600'
                            : 'border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            {{-- Período rápido --}}
            <div class="col-span-2 sm:col-span-3 lg:col-span-2 flex gap-1">
                <button type="button" wire:click="setThisMonth"
                    class="flex-1 px-2 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-gray-50 transition text-gray-600">
                    Este mes
                </button>
                <button type="button" wire:click="setThisQuarter"
                    class="flex-1 px-2 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-gray-50 transition text-gray-600">
                    Trimestre
                </button>
                <button type="button" wire:click="setThisYear"
                    class="flex-1 px-2 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-gray-50 transition text-gray-600">
                    Este año
                </button>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Desde</label>
                <input wire:model.live="dateFrom" type="date"
                    class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-indigo-300">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Hasta</label>
                <input wire:model.live="dateTo" type="date"
                    class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-indigo-300">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Cliente</label>
                <select wire:model.live="customerId"
                    class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-indigo-300">
                    <option value="">Todos</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Vendedor</label>
                <select wire:model.live="vendorId"
                    class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-indigo-300">
                    <option value="">Todos</option>
                    @foreach($vendors as $v)
                        <option value="{{ $v->id }}">{{ $v->name }}</option>
                    @endforeach
                </select>
            </div>
            @if($reportType !== 'products')
            <div>
                <label class="block text-xs text-gray-500 mb-1">Estatus</label>
                <select wire:model.live="status"
                    class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-indigo-300">
                    <option value="">Todos</option>
                    @foreach($statusOptions as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @endif
        </div>
    </div>

    {{-- ── Totales ──────────────────────────────────────────────────────── --}}
    @php $t = $this->totals; @endphp
    @if(!empty($t))
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        @if($reportType === 'products')
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
                <p class="text-xs text-emerald-700 font-medium">Ingreso total</p>
                <p class="text-xl font-bold text-emerald-800 mt-1">${{ number_format($t['total_revenue'], 2) }}</p>
            </div>
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                <p class="text-xs text-blue-700 font-medium">Unidades vendidas</p>
                <p class="text-xl font-bold text-blue-800 mt-1">{{ number_format($t['total_qty'], 0) }}</p>
            </div>
        @else
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
                <p class="text-xs text-emerald-700 font-medium">Total</p>
                <p class="text-xl font-bold text-emerald-800 mt-1">${{ number_format($t['total'], 2) }}</p>
            </div>
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-600 font-medium">Documentos</p>
                <p class="text-xl font-bold text-gray-800 mt-1">{{ number_format($t['qty'], 0) }}</p>
            </div>
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                <p class="text-xs text-blue-700 font-medium">IVA</p>
                <p class="text-xl font-bold text-blue-800 mt-1">${{ number_format($t['tax'], 2) }}</p>
            </div>
            <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                <p class="text-xs text-red-600 font-medium">Descuentos</p>
                <p class="text-xl font-bold text-red-700 mt-1">${{ number_format($t['discount'], 2) }}</p>
            </div>
        @endif
    </div>
    @endif

    {{-- ── Tabla de resultados ─────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

        {{-- Cabecera de columnas --}}
        @if($reportType === 'products')
            {{-- Tabla por producto --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left px-4 py-3 text-xs font-medium text-gray-500">Producto / Descripción</th>
                            <th class="text-right px-4 py-3 text-xs font-medium text-gray-500 cursor-pointer hover:text-indigo-600"
                                wire:click="sort('qty')">
                                Cant. vendida
                                @if($sortBy === 'qty')
                                    <span>{{ $sortDir === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </th>
                            <th class="text-right px-4 py-3 text-xs font-medium text-gray-500">Clientes</th>
                            <th class="text-right px-4 py-3 text-xs font-medium text-gray-500">Facturas</th>
                            <th class="text-right px-4 py-3 text-xs font-medium text-gray-500 cursor-pointer hover:text-indigo-600"
                                wire:click="sort('total')">
                                Ingreso
                                @if($sortBy === 'total')
                                    <span>{{ $sortDir === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($this->rows as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $row->description }}</td>
                                <td class="px-4 py-3 text-right text-gray-700">{{ number_format($row->total_qty, 2) }}</td>
                                <td class="px-4 py-3 text-right text-gray-500">{{ $row->customer_count }}</td>
                                <td class="px-4 py-3 text-right text-gray-500">{{ $row->invoice_count }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-emerald-700">${{ number_format($row->total_revenue, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-10 text-center text-gray-400 italic">Sin resultados</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            {{-- Tabla general --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 cursor-pointer hover:text-indigo-600"
                                wire:click="sort('date')">
                                Fecha {{ $sortBy === 'date' ? ($sortDir === 'asc' ? '↑' : '↓') : '' }}
                            </th>
                            <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 cursor-pointer hover:text-indigo-600"
                                wire:click="sort('folio')">
                                Folio {{ $sortBy === 'folio' ? ($sortDir === 'asc' ? '↑' : '↓') : '' }}
                            </th>
                            <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 cursor-pointer hover:text-indigo-600"
                                wire:click="sort('customer')">
                                Cliente {{ $sortBy === 'customer' ? ($sortDir === 'asc' ? '↑' : '↓') : '' }}
                            </th>
                            <th class="text-left px-4 py-3 text-xs font-medium text-gray-500">Vendedor</th>
                            <th class="text-center px-4 py-3 text-xs font-medium text-gray-500 cursor-pointer hover:text-indigo-600"
                                wire:click="sort('status')">
                                Estatus {{ $sortBy === 'status' ? ($sortDir === 'asc' ? '↑' : '↓') : '' }}
                            </th>
                            <th class="text-right px-4 py-3 text-xs font-medium text-gray-500">Subtotal</th>
                            <th class="text-right px-4 py-3 text-xs font-medium text-gray-500">Desc.</th>
                            <th class="text-right px-4 py-3 text-xs font-medium text-gray-500 cursor-pointer hover:text-indigo-600"
                                wire:click="sort('total')">
                                Total {{ $sortBy === 'total' ? ($sortDir === 'asc' ? '↑' : '↓') : '' }}
                            </th>
                            @if($reportType === 'invoices')
                                <th class="text-right px-4 py-3 text-xs font-medium text-gray-500">Pagado</th>
                                <th class="text-right px-4 py-3 text-xs font-medium text-gray-500">Saldo</th>
                            @endif
                            <th class="w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($this->rows as $row)
                            @php
                                $statusColors = match($reportType) {
                                    'invoices'   => \App\Models\SaleInvoice::STATUS_COLORS,
                                    'orders'     => \App\Models\SaleOrder::STATUS_COLORS,
                                    'quotations' => \App\Models\SaleQuotation::STATUS_COLORS,
                                    default      => [],
                                };
                                $statusLabels = match($reportType) {
                                    'invoices'   => \App\Models\SaleInvoice::STATUS,
                                    'orders'     => \App\Models\SaleOrder::STATUS,
                                    'quotations' => \App\Models\SaleQuotation::STATUS,
                                    default      => [],
                                };
                                $date = $reportType === 'invoices' ? $row->issued_at : $row->created_at;
                                $detailRoute = match($reportType) {
                                    'invoices'   => route('sales.invoices.show', $row->id),
                                    'orders'     => route('sales.orders.show', $row->id),
                                    'quotations' => route('sales.quotations.show', $row->id),
                                    default      => null,
                                };
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $row->folio }}</td>
                                <td class="px-4 py-3 text-gray-700 max-w-[180px] truncate">{{ $row->customer_name }}</td>
                                <td class="px-4 py-3 text-gray-500 text-xs">{{ $row->vendor_name ?? '—' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $statusColors[$row->status] ?? 'bg-gray-100 text-gray-600' }}">
                                        {{ $statusLabels[$row->status] ?? $row->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-gray-600">${{ number_format($row->subtotal, 2) }}</td>
                                <td class="px-4 py-3 text-right text-red-500">
                                    {{ $row->discount_amount > 0 ? '-$'.number_format($row->discount_amount, 2) : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-900">${{ number_format($row->total, 2) }}</td>
                                @if($reportType === 'invoices')
                                    <td class="px-4 py-3 text-right text-emerald-600">${{ number_format($row->paid_amount ?? 0, 2) }}</td>
                                    <td class="px-4 py-3 text-right {{ ($row->total - ($row->paid_amount ?? 0)) > 0 ? 'text-amber-600 font-medium' : 'text-gray-400' }}">
                                        ${{ number_format($row->total - ($row->paid_amount ?? 0), 2) }}
                                    </td>
                                @endif
                                <td class="px-4 py-3 text-center">
                                    @if($detailRoute)
                                        <a wire:navigate href="{{ $detailRoute }}" class="text-indigo-400 hover:text-indigo-600 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                            </svg>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="px-4 py-10 text-center text-gray-400 italic">
                                    Sin resultados para los filtros seleccionados
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Paginación --}}
        @if($this->rows->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $this->rows->links() }}
            </div>
        @endif
    </div>

</div>
