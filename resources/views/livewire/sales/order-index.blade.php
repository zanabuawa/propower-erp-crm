<div>
    <x-page-header title="Órdenes de venta" description="Gestiona tus órdenes de venta">
        <x-slot:actions>
            <a wire:navigate href="{{ route('sales.orders.create') }}"
                class="inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nueva orden
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
                aria-label="Buscar órdenes de venta"
                class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-transparent transition">
        </div>
        <select wire:model.live="filterStatus" aria-label="Filtrar por estado"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
            <option value="">Todos los estados</option>
            @foreach(\App\Models\SaleOrder::STATUS as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[580px]">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Folio</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Cliente</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden md:table-cell">Productos</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Total</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden sm:table-cell">Pago</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Estado</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($orders as $order)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-3">
                                <span class="font-mono text-xs font-medium text-gray-900">{{ $order->folio }}</span>
                                <p class="text-xs text-gray-400 sm:hidden mt-0.5">{{ $order->customer->name }}</p>
                            </td>
                            <td class="px-5 py-3 text-gray-700">{{ $order->customer->name }}</td>
                            <td class="px-5 py-3 text-gray-600 hidden md:table-cell">{{ $order->items_count }}</td>
                            <td class="px-5 py-3 font-semibold text-gray-900">
                                {{ $order->currency }} ${{ number_format($order->total, 2) }}
                            </td>
                            <td class="px-5 py-3 text-gray-500 text-xs hidden sm:table-cell">
                                {{ \App\Models\SaleOrder::PAYMENT_METHODS[$order->payment_method] ?? $order->payment_method }}
                            </td>
                            <td class="px-5 py-3">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ \App\Models\SaleOrder::STATUS_COLORS[$order->status] ?? 'bg-gray-100 text-gray-500' }}">
                                    {{ \App\Models\SaleOrder::STATUS[$order->status] ?? $order->status }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a wire:navigate href="{{ route('sales.orders.show', $order) }}"
                                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Ver →</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7"><x-empty-state message="No se encontraron órdenes de venta." /></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">{{ $orders->links() }}</div>
        @endif
    </div>
</div>
