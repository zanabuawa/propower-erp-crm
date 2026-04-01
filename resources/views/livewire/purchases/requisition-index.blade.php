<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-medium text-gray-900">Requisiciones de compra</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                {{ $isComprador ? 'Todas las solicitudes de compra' : 'Mis solicitudes de compra' }}
            </p>
        </div>
        @can('create purchases')
        <a href="{{ route('purchases.requisitions.create') }}" wire:navigate
            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
            + Nueva requisición
        </a>
        @endcan
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-wrap gap-3 mb-4">
        <input wire:model.live.debounce.300ms="search" type="text"
            placeholder="Buscar por folio o justificación..."
            class="flex-1 min-w-[200px] border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
        <select wire:model.live="filterStatus"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
            <option value="">Todos los estados</option>
            @foreach(\App\Models\PurchaseRequisition::STATUS as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Folio</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Solicitante</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Sucursal</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500 text-center">Líneas</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Monto</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Requerido</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Estado</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($requisitions as $req)
                    @php
                        // Monto: preferir cotización final, luego preliminar
                        $amount       = $req->finalQuotation?->total ?? $req->preliminaryQuotation?->total ?? null;
                        $currency     = $req->currency;

                        // ¿Requiere acción del usuario actual?
                        $userId       = auth()->id();
                        $needsAction  = false;
                        $actionLabel  = '';

                        if ($req->requested_by === $userId) {
                            if ($req->status === 'preliminary_quoted') {
                                $needsAction = true;
                                $actionLabel = 'Revisar cotización';
                            }
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
                        // Para autorizadores: detectar si tienen una aprobación pendiente
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
                        </td>
                        <td class="px-5 py-3 text-gray-700">{{ $req->requestedBy->name }}</td>
                        <td class="px-5 py-3 text-gray-500 text-xs">{{ $req->branch?->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-center text-gray-600">{{ $req->items_count }}</td>
                        <td class="px-5 py-3 text-gray-700">
                            @if($amount)
                                <span class="font-medium">{{ $currency }} ${{ number_format($amount, 2) }}</span>
                            @else
                                <span class="text-gray-400 text-xs">Sin cotizar</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-gray-500 text-xs">{{ $req->needed_by?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
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
                        <td colspan="8" class="px-5 py-12 text-center text-gray-400 text-sm">
                            No se encontraron requisiciones.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($requisitions->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">{{ $requisitions->links() }}</div>
        @endif
    </div>
</div>
