<div class="space-y-5">

    {{-- ── Header ──────────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between px-1">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Reporte de Ventas</h1>
            <p class="text-sm text-slate-500 mt-0.5">Análisis detallado por período, cliente, producto y vendedor</p>
        </div>
    </div>

    {{-- ── Filtros ──────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-4">

        {{-- Tipo de reporte --}}
        <div class="flex flex-wrap gap-2">
            @foreach([
                'invoices'   => 'Facturas',
                'orders'     => 'Órdenes de venta',
                'quotations' => 'Cotizaciones',
                'products'   => 'Por producto',
            ] as $val => $label)
                <button type="button" wire:click="$set('reportType', '{{ $val }}')"
                    class="px-4 py-1.5 text-sm rounded-lg font-semibold border transition
                        {{ $reportType === $val
                            ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm'
                            : 'border-slate-200 text-slate-600 hover:bg-slate-50 hover:border-slate-300' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            {{-- Período rápido --}}
            <div class="col-span-2 sm:col-span-3 lg:col-span-2 flex gap-1.5">
                <button type="button" wire:click="setThisMonth"
                    class="flex-1 px-2 py-1.5 text-xs border border-slate-200 rounded-lg hover:bg-indigo-50 hover:border-indigo-200 hover:text-indigo-700 transition text-slate-600 font-medium cursor-pointer">
                    Este mes
                </button>
                <button type="button" wire:click="setThisQuarter"
                    class="flex-1 px-2 py-1.5 text-xs border border-slate-200 rounded-lg hover:bg-indigo-50 hover:border-indigo-200 hover:text-indigo-700 transition text-slate-600 font-medium cursor-pointer">
                    Trimestre
                </button>
                <button type="button" wire:click="setThisYear"
                    class="flex-1 px-2 py-1.5 text-xs border border-slate-200 rounded-lg hover:bg-indigo-50 hover:border-indigo-200 hover:text-indigo-700 transition text-slate-600 font-medium cursor-pointer">
                    Este año
                </button>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Desde</label>
                <input wire:model.live="dateFrom" type="date"
                    class="w-full border border-slate-200 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 transition">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Hasta</label>
                <input wire:model.live="dateTo" type="date"
                    class="w-full border border-slate-200 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 transition">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Cliente</label>
                <select wire:model.live="customerId"
                    class="w-full border border-slate-200 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 transition">
                    <option value="">Todos</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Vendedor</label>
                <select wire:model.live="vendorId"
                    class="w-full border border-slate-200 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 transition">
                    <option value="">Todos</option>
                    @foreach($vendors as $v)
                        <option value="{{ $v->id }}">{{ $v->name }}</option>
                    @endforeach
                </select>
            </div>
            @if($reportType !== 'products')
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Estatus</label>
                <select wire:model.live="status"
                    class="w-full border border-slate-200 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 transition">
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
    <div wire:loading.class="opacity-60" wire:target="reportType,dateFrom,dateTo,customerId,vendorId,status,sort,setThisMonth,setThisQuarter,setThisYear">
    @if($reportType === 'products')
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
                <p class="text-xs text-emerald-700 font-semibold uppercase tracking-wide">Ingreso total</p>
                <p class="text-2xl font-black text-emerald-800 mt-1">${{ number_format($t['total_revenue'], 2) }}</p>
            </div>
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                <p class="text-xs text-blue-700 font-semibold uppercase tracking-wide">Unidades vendidas</p>
                <p class="text-2xl font-black text-blue-800 mt-1">{{ number_format($t['total_qty'], 0) }}</p>
            </div>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-{{ $reportType === 'invoices' ? '4' : '3' }} gap-4">
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
                <p class="text-xs text-emerald-700 font-semibold uppercase tracking-wide">Total</p>
                <p class="text-xl font-black text-emerald-800 mt-1">${{ number_format($t['total'], 2) }}</p>
                @if($t['qty'] > 0)
                    <p class="text-[10px] text-emerald-600 mt-0.5">{{ $t['qty'] }} documentos</p>
                @endif
            </div>
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                <p class="text-xs text-blue-700 font-semibold uppercase tracking-wide">IVA</p>
                <p class="text-xl font-black text-blue-800 mt-1">${{ number_format($t['tax'], 2) }}</p>
            </div>
            <div class="bg-rose-50 border border-rose-200 rounded-xl p-4">
                <p class="text-xs text-rose-600 font-semibold uppercase tracking-wide">Descuentos</p>
                <p class="text-xl font-black text-rose-700 mt-1">${{ number_format($t['discount'], 2) }}</p>
            </div>
            @if($reportType === 'invoices')
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                <p class="text-xs text-amber-700 font-semibold uppercase tracking-wide">Sin IVA (neto)</p>
                <p class="text-xl font-black text-amber-800 mt-1">${{ number_format($t['total'] - $t['tax'], 2) }}</p>
            </div>
            @endif
        </div>
    @endif
    </div>
    @endif

    {{-- ── Tabla de resultados ─────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden"
        wire:loading.class="opacity-60"
        wire:target="reportType,dateFrom,dateTo,customerId,vendorId,status,sort,setThisMonth,setThisQuarter,setThisYear">

        {{-- Indicador de carga --}}
        <div wire:loading wire:target="reportType,dateFrom,dateTo,customerId,vendorId,status,sort,setThisMonth,setThisQuarter,setThisYear"
            class="px-4 py-2 bg-indigo-50 border-b border-indigo-100 flex items-center gap-2 text-xs text-indigo-600 font-semibold">
            <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            Actualizando resultados...
        </div>

        @if($reportType === 'products')
            {{-- Tabla por producto --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="text-left px-4 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Producto / Descripción</th>
                            <th class="text-right px-4 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest cursor-pointer hover:text-indigo-600 transition"
                                wire:click="sort('qty')">
                                Cant. vendida
                                @if($sortBy === 'qty') <span class="ml-0.5">{{ $sortDir === 'asc' ? '↑' : '↓' }}</span> @endif
                            </th>
                            <th class="text-right px-4 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Clientes</th>
                            <th class="text-right px-4 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Facturas</th>
                            <th class="text-right px-4 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest cursor-pointer hover:text-indigo-600 transition"
                                wire:click="sort('total')">
                                Ingreso
                                @if($sortBy === 'total') <span class="ml-0.5">{{ $sortDir === 'asc' ? '↑' : '↓' }}</span> @endif
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($rows as $row)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-3 font-semibold text-slate-800">{{ $row->description }}</td>
                                <td class="px-4 py-3 text-right text-slate-700">{{ number_format($row->total_qty, 2) }}</td>
                                <td class="px-4 py-3 text-right text-slate-500">{{ $row->customer_count }}</td>
                                <td class="px-4 py-3 text-right text-slate-500">{{ $row->invoice_count }}</td>
                                <td class="px-4 py-3 text-right font-bold text-emerald-700">${{ number_format($row->total_revenue, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-10 text-center text-slate-400 italic">Sin resultados</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            {{-- Tabla general --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="text-left px-4 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest cursor-pointer hover:text-indigo-600 transition"
                                wire:click="sort('date')">
                                Fecha @if($sortBy === 'date') {{ $sortDir === 'asc' ? '↑' : '↓' }} @endif
                            </th>
                            <th class="text-left px-4 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest cursor-pointer hover:text-indigo-600 transition"
                                wire:click="sort('folio')">
                                Folio @if($sortBy === 'folio') {{ $sortDir === 'asc' ? '↑' : '↓' }} @endif
                            </th>
                            <th class="text-left px-4 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest cursor-pointer hover:text-indigo-600 transition"
                                wire:click="sort('customer')">
                                Cliente @if($sortBy === 'customer') {{ $sortDir === 'asc' ? '↑' : '↓' }} @endif
                            </th>
                            <th class="text-left px-4 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Vendedor</th>
                            <th class="text-center px-4 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest cursor-pointer hover:text-indigo-600 transition"
                                wire:click="sort('status')">
                                Estatus @if($sortBy === 'status') {{ $sortDir === 'asc' ? '↑' : '↓' }} @endif
                            </th>
                            <th class="text-right px-4 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Subtotal</th>
                            <th class="text-right px-4 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Desc.</th>
                            <th class="text-right px-4 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest cursor-pointer hover:text-indigo-600 transition"
                                wire:click="sort('total')">
                                Total @if($sortBy === 'total') {{ $sortDir === 'asc' ? '↑' : '↓' }} @endif
                            </th>
                            @if($reportType === 'invoices')
                                <th class="text-right px-4 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pagado</th>
                                <th class="text-right px-4 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Saldo</th>
                            @endif
                            <th class="w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($rows as $row)
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
                            <tr class="hover:bg-slate-50 transition-colors {{ $row->status === 'cancelled' ? 'opacity-60' : '' }}">
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap text-xs">
                                    {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3 font-bold text-slate-900 text-xs">{{ $row->folio }}</td>
                                <td class="px-4 py-3 text-slate-700 max-w-[200px] truncate">{{ $row->customer_name }}</td>
                                <td class="px-4 py-3 text-slate-500 text-xs">{{ $row->vendor_name ?? '—' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold {{ $statusColors[$row->status] ?? 'bg-slate-100 text-slate-600' }}">
                                        {{ $statusLabels[$row->status] ?? $row->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-slate-600 text-xs">${{ number_format($row->subtotal, 2) }}</td>
                                <td class="px-4 py-3 text-right text-rose-500 text-xs">
                                    {{ $row->discount_amount > 0 ? '-$'.number_format($row->discount_amount, 2) : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-slate-900">${{ number_format($row->total, 2) }}</td>
                                @if($reportType === 'invoices')
                                    <td class="px-4 py-3 text-right text-emerald-600 text-xs">${{ number_format($row->paid_amount ?? 0, 2) }}</td>
                                    @php $balance = $row->total - ($row->paid_amount ?? 0); @endphp
                                    <td class="px-4 py-3 text-right text-xs font-semibold {{ $balance > 0.01 ? 'text-amber-600' : 'text-slate-400' }}">
                                        ${{ number_format($balance, 2) }}
                                    </td>
                                @endif
                                <td class="px-4 py-3 text-center">
                                    @if($detailRoute)
                                        <a wire:navigate href="{{ $detailRoute }}" class="text-indigo-400 hover:text-indigo-600 transition cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                            </svg>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="px-4 py-12 text-center">
                                    <svg class="w-10 h-10 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="text-slate-400 italic text-sm">Sin resultados para los filtros seleccionados</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Paginación --}}
        @if(method_exists($rows, 'hasPages') && $rows->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">
                {{ $rows->links() }}
            </div>
        @endif
    </div>

</div>
