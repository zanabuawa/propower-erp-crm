<div>
    <x-page-header title="Facturas" description="Gestiona tus facturas de venta">
        <x-slot:actions>
            <a wire:navigate href="{{ route('sales.invoices.create') }}"
                class="inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nueva factura
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    <div class="flex flex-col sm:flex-row gap-3 mb-5">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text"
                placeholder="Buscar por folio o cliente..."
                aria-label="Buscar facturas"
                class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-transparent transition">
        </div>
        <select wire:model.live="filterStatus" aria-label="Filtrar por estado"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
            <option value="">Todos los estados</option>
            @foreach(\App\Models\SaleInvoice::STATUS as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[700px]">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Folio</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Cliente</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden md:table-cell">Tipo</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Total</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden sm:table-cell">Pagado</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden sm:table-cell">Saldo</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Estado</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($invoices as $invoice)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-3">
                                <span class="font-mono text-xs font-medium text-gray-900">{{ $invoice->folio }}</span>
                                <p class="text-xs text-gray-400 sm:hidden mt-0.5">{{ $invoice->customer->name }}</p>
                            </td>
                            <td class="px-5 py-3 text-gray-700">{{ $invoice->customer->name }}</td>
                            <td class="px-5 py-3 hidden md:table-cell">
                                <span class="text-xs font-medium {{ $invoice->type === 'cfdi' ? 'text-indigo-600' : 'text-gray-500' }}">
                                    {{ $invoice->type === 'cfdi' ? 'CFDI' : 'Interna' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 font-semibold text-gray-900">
                                {{ $invoice->currency }} ${{ number_format($invoice->total, 2) }}
                            </td>
                            <td class="px-5 py-3 text-emerald-600 font-medium hidden sm:table-cell">
                                ${{ number_format($invoice->paid_amount, 2) }}
                            </td>
                            <td class="px-5 py-3 font-medium hidden sm:table-cell {{ ($invoice->total - $invoice->paid_amount) > 0 ? 'text-red-600' : 'text-gray-400' }}">
                                ${{ number_format($invoice->total - $invoice->paid_amount, 2) }}
                            </td>
                            <td class="px-5 py-3">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ \App\Models\SaleInvoice::STATUS_COLORS[$invoice->status] ?? 'bg-gray-100 text-gray-500' }}">
                                    {{ \App\Models\SaleInvoice::STATUS[$invoice->status] ?? $invoice->status }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a wire:navigate href="{{ route('sales.invoices.show', $invoice) }}"
                                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Ver →</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8"><x-empty-state message="No se encontraron facturas." /></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($invoices->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">{{ $invoices->links() }}</div>
        @endif
    </div>
</div>
