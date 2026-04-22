<div>
    {{-- Header --}}
    <x-page-header title="Requisiciones de compra"
        :description="$isComprador ? 'Gestiona y autoriza las solicitudes de compra del sistema' : 'Control de tus solicitudes de compra y seguimiento'">
        <x-slot:actions>
            @can('create purchases')
            <a href="{{ route('purchases.requisitions.create') }}" wire:navigate
                class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-medium px-5 py-2 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-105">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nueva requisición
            </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    {{-- Filtros --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-5 mb-6 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.300ms="search" type="text"
                    placeholder="Folio, justificación o proyecto..."
                    class="w-full pl-9 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
            </div>
            
            <div class="relative">
                <select wire:model.live="filterStatus"
                    class="w-full pl-4 pr-10 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all hover:bg-gray-100 appearance-none">
                    <option value="">Todos los estados</option>
                    @foreach(\App\Models\PurchaseRequisition::STATUS as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>

            <div class="relative">
                <select wire:model.live="filterPriority"
                    class="w-full pl-4 pr-10 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all hover:bg-gray-100 appearance-none">
                    <option value="">Toda prioridad</option>
                    @foreach(\App\Models\PurchaseRequisition::PRIORITY as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>

            <div class="relative">
                <select wire:model.live="filterType"
                    class="w-full pl-4 pr-10 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all hover:bg-gray-100 appearance-none">
                    <option value="">Todo tipo</option>
                    @foreach(\App\Models\PurchaseRequisition::REQUISITION_TYPES as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- VISTA MÓVIL --}}
    <div class="space-y-4 lg:hidden mb-6">
        @forelse($requisitions as $req)
            @php
                $amount       = $req->finalQuotation?->total ?? $req->preliminaryQuotation?->total ?? null;
                $userId       = auth()->id();
                $needsAction  = false;
                $actionLabel  = '';

                if ($req->requested_by === $userId && $req->status === 'preliminary_quoted') {
                    $needsAction = true; $actionLabel = 'Revisar cotización';
                }
                if ($isComprador) {
                    if (in_array($req->status, ['submitted', 'requester_returned'])) {
                        $needsAction = true; $actionLabel = 'Por cotizar';
                    } elseif ($req->status === 'requester_confirmed') {
                        $needsAction = true; $actionLabel = 'Confirmar final';
                    }
                }
                if (!$needsAction && $req->finalQuotation && $req->status === 'pending_auth') {
                    $hasPending = $req->finalQuotation->approvals()->where('user_id', $userId)->where('status', 'pending')->exists();
                    if ($hasPending) { $needsAction = true; $actionLabel = 'Autorizar'; }
                }
            @endphp
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm
                {{ $req->priority === 'urgent' ? 'border-l-4 border-l-red-500' : ($req->priority === 'high' ? 'border-l-4 border-l-amber-500' : '') }}">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-mono text-xs font-bold text-indigo-600">{{ $req->folio }}</span>
                            @if($req->priority && $req->priority !== 'normal')
                                <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold {{ \App\Models\PurchaseRequisition::PRIORITY_COLORS[$req->priority] ?? 'bg-gray-100 text-gray-500' }}">
                                    {{ \App\Models\PurchaseRequisition::PRIORITY[$req->priority] ?? $req->priority }}
                                </span>
                            @endif
                        </div>
                        <h3 class="font-bold text-gray-900 mt-1 line-clamp-1">{{ $req->requestedBy->name }}</h3>
                    </div>
                    <x-status-badge :status="$req->status" :label="\App\Models\PurchaseRequisition::STATUS[$req->status] ?? $req->status" />
                </div>

                <div class="grid grid-cols-2 gap-3 mb-4 text-sm">
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-xs text-gray-500 mb-1">Monto Estimado</p>
                        @if($amount)
                            <p class="font-bold text-gray-900">{{ $req->currency }} ${{ number_format($amount, 2) }}</p>
                        @else
                            <p class="text-[11px] text-gray-400 italic">Pendiente</p>
                        @endif
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-xs text-gray-500 mb-1">Partidas</p>
                        <p class="font-medium text-gray-700">{{ $req->items_count }} partidas</p>
                    </div>
                    @if($req->project_name)
                    <div class="col-span-2 bg-indigo-50/50 rounded-xl p-3 border border-indigo-100">
                        <p class="text-xs text-indigo-600 mb-1">Proyecto</p>
                        <p class="text-sm font-semibold text-indigo-950">{{ $req->project_name }}</p>
                    </div>
                    @endif
                </div>

                <div class="flex flex-col gap-2">
                    @if($needsAction)
                        <div class="flex items-center justify-center gap-2 py-1.5 px-3 bg-amber-50 text-amber-700 rounded-lg text-[11px] font-black uppercase tracking-wider animate-pulse border border-amber-100">
                            <span class="w-1.5 h-1.5 bg-amber-500 rounded-full"></span>
                            Requiere: {{ $actionLabel }}
                        </div>
                    @endif
                    <a wire:navigate href="{{ route('purchases.requisitions.show', $req) }}"
                        class="flex items-center justify-center w-full py-2.5 bg-indigo-50 text-indigo-600 rounded-xl text-sm font-bold hover:bg-indigo-600 hover:text-white transition-all">
                        Ver requisición
                    </a>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center shadow-sm">
                <x-empty-state message="No se encontraron requisiciones." />
            </div>
        @endforelse
        {{ $requisitions->links() }}
    </div>

    {{-- VISTA ESCRITORIO --}}
    <div class="hidden lg:block bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-left">
                    <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Folio & Solicitante</th>
                    <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">Partidas / Entrega</th>
                    <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider text-right">Monto Estimado</th>
                    <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">Estado</th>
                    <th class="px-6 py-4"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($requisitions as $req)
                    @php
                        $amount       = $req->finalQuotation?->total ?? $req->preliminaryQuotation?->total ?? null;
                        $userId       = auth()->id();
                        $needsAction  = false;
                        $actionLabel  = '';

                        if ($req->requested_by === $userId && $req->status === 'preliminary_quoted') {
                            $needsAction = true; $actionLabel = 'Revisar cotización';
                        }
                        if ($isComprador) {
                            if (in_array($req->status, ['submitted', 'requester_returned'])) {
                                $needsAction = true; $actionLabel = 'Por cotizar';
                            } elseif ($req->status === 'requester_confirmed') {
                                $needsAction = true; $actionLabel = 'Confirmar final';
                            }
                        }
                        if (!$needsAction && $req->finalQuotation && $req->status === 'pending_auth') {
                            $hasPending = $req->finalQuotation->approvals()->where('user_id', $userId)->where('status', 'pending')->exists();
                            if ($hasPending) { $needsAction = true; $actionLabel = 'Autorizar'; }
                        }
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors group
                        {{ $req->priority === 'urgent' ? 'border-l-4 border-l-red-500' : ($req->priority === 'high' ? 'border-l-4 border-l-amber-500' : '') }}">
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <div class="flex items-center gap-2 mb-0.5">
                                    <span class="font-mono text-xs font-bold text-indigo-600 tracking-tight">{{ $req->folio }}</span>
                                    @if($req->priority && $req->priority !== 'normal')
                                        <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold {{ \App\Models\PurchaseRequisition::PRIORITY_COLORS[$req->priority] ?? 'bg-gray-100 text-gray-500' }}">
                                            {{ \App\Models\PurchaseRequisition::PRIORITY[$req->priority] ?? $req->priority }}
                                        </span>
                                    @endif
                                </div>
                                <span class="font-semibold text-gray-900 line-clamp-1">{{ $req->requestedBy->name }}</span>
                                <span class="text-[10px] text-gray-500 mt-1 uppercase tracking-wider">
                                    {{ $req->created_at->format('d/m/Y') }} 
                                    @if($req->project_name) • <span class="text-indigo-600 font-bold">{{ $req->project_name }}</span> @endif
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex flex-col items-center gap-1">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg bg-gray-100 text-gray-600 text-[10px] font-bold">
                                    {{ $req->items_count }} partidas
                                </span>
                                @if($req->needed_by)
                                    <span class="text-[10px] {{ $req->needed_by->isPast() && !in_array($req->status, ['ordered','cancelled']) ? 'text-red-500 font-bold' : 'text-gray-500' }}">
                                        Límite: {{ $req->needed_by->format('d/m/Y') }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if($amount)
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-gray-900 tracking-tight">{{ $req->currency }} ${{ number_format($amount, 2) }}</span>
                                    <span class="text-[10px] text-gray-400">Total cotizado</span>
                                </div>
                            @else
                                <span class="text-[10px] text-gray-400 font-medium px-2 py-1 bg-gray-50 rounded-lg border border-gray-100">Por cotizar</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col items-center gap-1.5">
                                <x-status-badge :status="$req->status" :label="\App\Models\PurchaseRequisition::STATUS[$req->status] ?? $req->status" />
                                @if($needsAction)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-black text-amber-600 uppercase tracking-widest animate-pulse">
                                        {{ $actionLabel }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a wire:navigate href="{{ route('purchases.requisitions.show', $req) }}"
                                class="inline-flex items-center justify-center p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <x-empty-state message="No se encontraron requisiciones." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($requisitions->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                {{ $requisitions->links() }}
            </div>
        @endif
    </div>
</div>


