<div>
    <x-page-header title="Requisiciones de compra"
        :description="$isComprador ? 'Todas las solicitudes de compra' : 'Mis solicitudes de compra'">
        <x-slot:actions>
            @can('create purchases')
            <a href="{{ route('purchases.requisitions.create') }}" wire:navigate
                class="inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nueva requisición
            </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    <div class="flex flex-col sm:flex-row gap-3 mb-5">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text"
                placeholder="Buscar por folio o justificación..."
                aria-label="Buscar requisiciones"
                class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-transparent transition">
        </div>
        <select wire:model.live="filterStatus" aria-label="Filtrar por estado"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
            <option value="">Todos los estados</option>
            @foreach(\App\Models\PurchaseRequisition::STATUS as $key => $label)
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
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden sm:table-cell">Solicitante</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden md:table-cell">Sucursal</th>
                        <th class="text-center px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden md:table-cell">Líneas</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden sm:table-cell">Monto</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden md:table-cell">Requerido</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Estado</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($requisitions as $req)
                        @php
                            $amount       = $req->finalQuotation?->total ?? $req->preliminaryQuotation?->total ?? null;
                            $currency     = $req->currency;
                            $userId       = auth()->id();
                            $needsAction  = false;
                            $actionLabel  = '';

                            if ($req->requested_by === $userId && $req->status === 'preliminary_quoted') {
                                $needsAction = true;
                                $actionLabel = 'Revisar cotización';
                            }
                            if ($isComprador) {
                                if (in_array($req->status, ['submitted', 'requester_returned'])) {
                                    $needsAction = true;
                                    $actionLabel = 'Cotizar';
                                } elseif ($req->status === 'requester_confirmed') {
                                    $needsAction = true;
                                    $actionLabel = 'Cot. final';
                                }
                            }
                            if (!$needsAction && $req->finalQuotation && $req->status === 'pending_auth') {
                                $hasPending = $req->finalQuotation->approvals()
                                    ->where('user_id', $userId)
                                    ->where('status', 'pending')
                                    ->exists();
                                if ($hasPending) {
                                    $needsAction = true;
                                    $actionLabel = 'Autorizar';
                                }
                            }
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-3">
                                <span class="font-mono text-xs font-medium text-gray-900">{{ $req->folio }}</span>
                                @if($needsAction)
                                    <span class="ml-2 px-1.5 py-0.5 text-[10px] font-medium bg-amber-100 text-amber-700 rounded-full">
                                        {{ $actionLabel }}
                                    </span>
                                @endif
                                <p class="text-xs text-gray-400 sm:hidden mt-0.5">{{ $req->requestedBy->name }}</p>
                            </td>
                            <td class="px-5 py-3 text-gray-700 hidden sm:table-cell">{{ $req->requestedBy->name }}</td>
                            <td class="px-5 py-3 text-gray-500 text-xs hidden md:table-cell">{{ $req->branch?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-center text-gray-600 hidden md:table-cell">{{ $req->items_count }}</td>
                            <td class="px-5 py-3 hidden sm:table-cell">
                                @if($amount)
                                    <span class="font-semibold text-gray-900">{{ $currency }} ${{ number_format($amount, 2) }}</span>
                                @else
                                    <span class="text-gray-400 text-xs">Sin cotizar</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-gray-500 text-xs hidden md:table-cell">{{ $req->needed_by?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ \App\Models\PurchaseRequisition::STATUS_COLORS[$req->status] ?? 'bg-gray-100 text-gray-500' }}">
                                    {{ \App\Models\PurchaseRequisition::STATUS[$req->status] ?? $req->status }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('purchases.requisitions.show', $req) }}" wire:navigate
                                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Ver →</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8"><x-empty-state message="No se encontraron requisiciones." /></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($requisitions->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">{{ $requisitions->links() }}</div>
        @endif
    </div>
</div>
