<div>
    <x-page-header title="Requisiciones de compra"
        :description="$isComprador ? 'Gestiona y autoriza las solicitudes de compra del sistema' : 'Control de tus solicitudes de compra y seguimiento'">
        <x-slot:actions>
            @can('create purchases')
            <a href="{{ route('purchases.requisitions.create') }}" wire:navigate
                class="inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-all duration-200 shadow-sm hover:shadow-md active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Nueva requisición
            </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    {{-- Filters Toolbar --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-4 mb-6 shadow-sm space-y-3">
        <div class="flex flex-col md:flex-row gap-3">
            <div class="relative flex-grow group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400 group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text"
                    placeholder="Buscar por folio, justificación o proyecto..."
                    class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all duration-200 placeholder-gray-400">
            </div>
            <select wire:model.live="filterStatus"
                class="min-w-[170px] bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all duration-200 cursor-pointer">
                <option value="">Todos los estados</option>
                @foreach(\App\Models\PurchaseRequisition::STATUS as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterPriority"
                class="min-w-[140px] bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all duration-200 cursor-pointer">
                <option value="">Toda prioridad</option>
                @foreach(\App\Models\PurchaseRequisition::PRIORITY as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterType"
                class="min-w-[160px] bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all duration-200 cursor-pointer">
                <option value="">Todo tipo</option>
                @foreach(\App\Models\PurchaseRequisition::REQUISITION_TYPES as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Main Table --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto overflow-visible">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50">
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest">Folio & Solicitante</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest hidden md:table-cell text-center">Detalles</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest hidden sm:table-cell">Monto Estimado</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest">Estado</th>
                        <th class="px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($requisitions as $req)
                        @php
                            $amount       = $req->finalQuotation?->total ?? $req->preliminaryQuotation?->total ?? null;
                            $currency     = $req->currency;
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
                        <tr class="group hover:bg-gray-50/80 transition-all duration-200
                            {{ $req->priority === 'urgent' ? 'border-l-2 border-l-red-400' : ($req->priority === 'high' ? 'border-l-2 border-l-amber-400' : '') }}">
                            <td class="px-6 py-5">
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-2 mb-0.5">
                                        <span class="font-mono text-xs font-bold text-indigo-600 tracking-tight group-hover:underline">
                                            {{ $req->folio }}
                                        </span>
                                        {{-- Priority badge --}}
                                        @if($req->priority && $req->priority !== 'normal')
                                            <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold
                                                {{ \App\Models\PurchaseRequisition::PRIORITY_COLORS[$req->priority] ?? 'bg-gray-100 text-gray-500' }}">
                                                {{ \App\Models\PurchaseRequisition::PRIORITY[$req->priority] ?? $req->priority }}
                                            </span>
                                        @endif
                                        {{-- Type badge --}}
                                        @if($req->requisition_type)
                                            <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-medium
                                                {{ \App\Models\PurchaseRequisition::REQUISITION_TYPE_COLORS[$req->requisition_type] ?? 'bg-gray-100 text-gray-500' }}">
                                                {{ \App\Models\PurchaseRequisition::REQUISITION_TYPES[$req->requisition_type] ?? $req->requisition_type }}
                                            </span>
                                        @endif
                                    </div>
                                    <span class="font-semibold text-gray-900">{{ $req->requestedBy->name }}</span>
                                    <span class="text-[10px] text-gray-400 flex items-center gap-1 mt-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ $req->created_at->format('d/m/Y') }}
                                        @if($req->branch) · {{ $req->branch->name }} @endif
                                        @if($req->project_name) · <span class="text-indigo-400 font-medium">{{ $req->project_name }}</span> @endif
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-5 hidden md:table-cell">
                                <div class="flex flex-col items-center gap-1 text-center">
                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-600 text-[10px] font-bold rounded-lg">{{ $req->items_count }} partidas</span>
                                    @if($req->needed_by)
                                        <span class="text-[10px] {{ $req->needed_by->isPast() && !in_array($req->status, ['ordered','cancelled']) ? 'text-red-500 font-semibold' : 'text-gray-500 font-medium' }}">
                                            Req: {{ $req->needed_by->format('d/m/Y') }}
                                        </span>
                                    @endif
                                    @if($req->expense_type)
                                        <span class="text-[10px] text-gray-400">
                                            {{ \App\Models\PurchaseRequisition::EXPENSE_TYPES[$req->expense_type] ?? '' }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-5 hidden sm:table-cell">
                                @if($amount)
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-gray-900 tracking-tight">{{ $currency }} ${{ number_format($amount, 2) }}</span>
                                        <span class="text-[10px] text-gray-400 font-medium italic">Incluye impuestos</span>
                                    </div>
                                @else
                                    <span class="text-[11px] text-gray-400 font-medium bg-gray-50 px-2 py-1 rounded-lg border border-gray-100 italic">Pendiente cotizar</span>
                                @endif
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex flex-col gap-2">
                                    <x-status-badge :status="$req->status" :label="\App\Models\PurchaseRequisition::STATUS[$req->status] ?? $req->status" />
                                    @if($needsAction)
                                        <span class="inline-flex items-center gap-1 text-[10px] font-black text-amber-600 uppercase tracking-widest animate-pulse">
                                            <span class="w-1.5 h-1.5 bg-amber-500 rounded-full"></span>
                                            {{ $actionLabel }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <a href="{{ route('purchases.requisitions.show', $req) }}" wire:navigate
                                    class="inline-flex items-center justify-center p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12">
                                <x-empty-state message="No se encontraron requisiciones que coincidan con los filtros." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($requisitions->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30">
                {{ $requisitions->links() }}
            </div>
        @endif
    </div>
</div>

