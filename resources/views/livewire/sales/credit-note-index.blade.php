<div>
    <x-page-header title="Notas de crédito" description="Gestiona las notas de crédito emitidas">
        <x-slot:actions>
            @can('create sales')
            <a wire:navigate href="{{ route('sales.credit-notes.create') }}"
                class="inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nueva nota
            </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text"
                placeholder="Buscar por folio, motivo o cliente..."
                class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
        </div>
        <select wire:model.live="filterCustomer" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
            <option value="">Todos los clientes</option>
            @foreach($customers as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="filterStatus" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
            <option value="">Todos los estados</option>
            <option value="draft">Borrador</option>
            <option value="applied">Aplicada</option>
            <option value="cancelled">Cancelada</option>
        </select>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[700px]">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Folio</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Cliente</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden md:table-cell">Factura</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden lg:table-cell">Motivo</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Total</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Estado</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($notes as $note)
                    @php
                        $statusColor = match($note->status) {
                            'draft'     => 'bg-gray-100 text-gray-600',
                            'applied'   => 'bg-green-100 text-green-700',
                            'cancelled' => 'bg-red-100 text-red-600',
                            default     => 'bg-gray-100 text-gray-500',
                        };
                        $statusLabel = \App\Models\SaleCreditNote::STATUS[$note->status] ?? $note->status;
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-3 font-mono text-xs font-medium text-gray-900">{{ $note->folio }}</td>
                        <td class="px-5 py-3 text-gray-700">{{ $note->customer->name ?? '—' }}</td>
                        <td class="px-5 py-3 hidden md:table-cell">
                            @if($note->invoice)
                            <a wire:navigate href="{{ route('sales.invoices.show', $note->invoice) }}"
                               class="text-indigo-600 hover:underline text-xs font-mono">{{ $note->invoice->folio }}</a>
                            @else
                            <span class="text-gray-400 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 hidden lg:table-cell text-gray-500 text-xs max-w-[200px] truncate">
                            {{ $note->reason ?? '—' }}
                        </td>
                        <td class="px-5 py-3 text-right font-semibold text-gray-900">
                            {{ $note->currency }} ${{ number_format($note->total, 2) }}
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <a wire:navigate href="{{ route('sales.credit-notes.show', $note) }}"
                               class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Ver →</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-gray-400 text-sm">No se encontraron notas de crédito.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($notes->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">{{ $notes->links('vendor.pagination.tailwind') }}</div>
        @endif
    </div>
</div>
