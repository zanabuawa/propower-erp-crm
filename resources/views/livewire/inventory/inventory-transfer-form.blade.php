<div class="min-h-screen bg-slate-50/60 -m-4 lg:-m-6">
<div class="w-full px-4 lg:px-8 py-6">

    {{-- ── HEADER ──────────────────────────────────────────────────────────────── --}}
    <div class="flex items-start justify-between gap-4 mb-6">
        <div class="flex items-start gap-3 min-w-0">
            <a wire:navigate href="{{ route('inventory.transfers.index') }}"
                class="mt-0.5 flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 text-slate-400 hover:text-slate-600 hover:border-slate-300 hover:bg-slate-50 transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div class="min-w-0">
                <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                    <span>Inventario</span>
                    <span>/</span>
                    <span>Transferencias</span>
                    @if($stockMovement)
                        <span>/</span>
                        <span class="text-slate-600 font-medium font-mono">{{ $stockMovement->folio }}</span>
                    @endif
                </div>
                <h1 class="text-lg font-semibold text-slate-900 leading-tight">
                    @if($mode === 'create')
                        Nueva transferencia de inventario
                    @else
                        Transferencia {{ $stockMovement->folio }}
                    @endif
                </h1>
                <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                    @if($mode !== 'create')
                        @php
                            $statusConfig = [
                                'requested'          => ['bg-blue-50 text-blue-700 border-blue-200',   'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                                'accepted'           => ['bg-indigo-50 text-indigo-700 border-indigo-200', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                                'in_transit'         => ['bg-amber-50 text-amber-700 border-amber-200',  'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4'],
                                'partially_received' => ['bg-orange-50 text-orange-700 border-orange-200','M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                                'completed'          => ['bg-emerald-50 text-emerald-700 border-emerald-200','M5 13l4 4L19 7'],
                                'rejected'           => ['bg-red-50 text-red-700 border-red-200',        'M6 18L18 6M6 6l12 12'],
                                'cancelled'          => ['bg-slate-100 text-slate-600 border-slate-200', 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636'],
                            ];
                            [$statusCls, $statusIcon] = $statusConfig[$stockMovement->status] ?? ['bg-slate-100 text-slate-600 border-slate-200', 'M12 12h.01'];
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border {{ $statusCls }}">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $statusIcon }}"/>
                            </svg>
                            {{ \App\Models\StockMovement::TRANSFER_STATUSES[$stockMovement->status] ?? $stockMovement->status }}
                        </span>
                    @endif
                    @if($mode !== 'create' && $isLocal)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-teal-50 text-teal-700 border border-teal-200">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            Local
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Action buttons (header) --}}
        <div class="flex items-center gap-2 flex-shrink-0 flex-wrap justify-end">
            @if($mode === 'receive' && $stockMovement?->status === 'requested' && $isLocal)
                <button wire:click="markInTransit" wire:confirm="¿Autorizar y marcar como en tránsito?"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-teal-600 hover:bg-teal-700 text-white rounded-lg transition cursor-pointer disabled:opacity-60">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Autorizar transferencia
                </button>
            @endif
            @if($stockMovement && in_array($stockMovement->status, ['requested','accepted','in_transit','partially_received']))
                @if($stockMovement->user_id === auth()->id() || auth()->user()->hasRole(['admin','gerente']))
                    <button wire:click="cancelTransfer"
                        wire:confirm="{{ $stockMovement->status === 'accepted' ? '¿Cancelar esta transferencia? El stock reservado en el Almacén de Transferencias será devuelto al almacén origen.' : '¿Cancelar esta transferencia?' }}"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium border border-red-200 text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Cancelar
                    </button>
                @endif
            @endif
        </div>
    </div>

    <x-alert />

    {{-- ── DISPATCH NOTES / REJECTION ──────────────────────────────────────────── --}}
    @if($stockMovement && $stockMovement->dispatch_notes)
        @php
            $isRejected  = $stockMovement->status === 'rejected';
            $noteCls     = $isRejected
                ? 'bg-red-50 border-red-200 text-red-800'
                : 'bg-amber-50 border-amber-200 text-amber-800';
            $noteLabel   = $isRejected ? 'Motivo del rechazo' : 'Nota del almacén origen';
            $noteIcon    = $isRejected
                ? 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'
                : 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
        @endphp
        <div class="flex items-start gap-3 rounded-xl border {{ $noteCls }} px-4 py-3 mb-5">
            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $noteIcon }}"/>
            </svg>
            <div>
                <p class="text-sm font-semibold">{{ $noteLabel }}</p>
                <p class="text-sm mt-0.5">{{ $stockMovement->dispatch_notes }}</p>
            </div>
        </div>
    @endif

    {{-- ── SECCIÓN: ORIGEN Y DESTINO ───────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 mb-4">
        <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-4">Origen y destino</h2>

        @if($mode === 'create')

            {{-- Selects con flecha visual --}}
            <div class="grid grid-cols-1 sm:grid-cols-[1fr_28px_1fr] gap-3 items-start mb-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1.5">
                        Almacén origen <span class="text-red-500">*</span>
                    </label>
                    <select wire:model.live="warehouse_id"
                        class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm text-slate-800 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 transition cursor-pointer">
                        <option value="">— Seleccionar almacén —</option>
                        @foreach($warehouses->groupBy('branch_id') as $branchWarehouses)
                            <optgroup label="{{ $branchWarehouses->first()->branch->name }}">
                                @foreach($branchWarehouses as $w)
                                    <option value="{{ $w->id }}">{{ $w->name }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @error('warehouse_id')
                        <p class="text-xs text-red-500 mt-1 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="flex items-center justify-center pt-7">
                    <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1.5">
                        Almacén destino <span class="text-red-500">*</span>
                    </label>
                    <select wire:model.live="warehouse_destination_id"
                        class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm text-slate-800 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 transition
                            {{ !$warehouse_id ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }}"
                        @if(!$warehouse_id) disabled @endif>
                        <option value="">{{ $warehouse_id ? '— Seleccionar almacén —' : 'Primero elige el origen' }}</option>
                        @if($warehouse_id)
                            @php
                                $originBranchId      = $warehouses->firstWhere('id', $warehouse_id)?->branch_id;
                                $sameBranchWhs       = $warehouses->where('branch_id', $originBranchId)->where('id', '!=', $warehouse_id);
                                $otherBranchesByGroup = $warehouses->where('branch_id', '!=', $originBranchId)->groupBy('branch_id');
                            @endphp
                            @if($sameBranchWhs->count())
                                <optgroup label="Misma sucursal — {{ $warehouses->firstWhere('id', $warehouse_id)?->branch?->name }}">
                                    @foreach($sameBranchWhs as $w)
                                        <option value="{{ $w->id }}">{{ $w->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endif
                            @foreach($otherBranchesByGroup as $branchWhs)
                                <optgroup label="Sucursal: {{ $branchWhs->first()->branch->name }}">
                                    @foreach($branchWhs as $w)
                                        <option value="{{ $w->id }}">{{ $w->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        @endif
                    </select>
                    @error('warehouse_destination_id')
                        <p class="text-xs text-red-500 mt-1 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            {{-- Banner tipo de transferencia --}}
            @if($warehouse_id && $warehouse_destination_id)
                @if($isLocal)
                    <div class="flex items-center gap-2.5 rounded-xl bg-teal-50 border border-teal-200 px-4 py-2.5 mb-4">
                        <svg class="w-4 h-4 text-teal-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <div>
                            <span class="text-sm font-semibold text-teal-800">Transferencia local</span>
                            <span class="text-xs text-teal-600 ml-1.5">— misma sucursal, se autoriza directamente sin aprobación del origen</span>
                        </div>
                    </div>
                @else
                    @php
                        $originW = $warehouses->firstWhere('id', $warehouse_id);
                        $destW   = $warehouses->firstWhere('id', $warehouse_destination_id);
                    @endphp
                    <div class="rounded-xl border-2 border-amber-300 bg-amber-50 px-4 py-3 mb-4">
                        <div class="flex items-center gap-2 mb-1.5">
                            <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                            <span class="text-sm font-semibold text-amber-800">Transferencia entre sucursales</span>
                        </div>
                        <p class="text-xs text-amber-700 mb-2.5">
                            El almacén origen debe revisar y aprobar la solicitud antes de que la mercancía pueda enviarse.
                        </p>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="inline-flex items-center gap-1.5 bg-white border border-amber-200 text-amber-900 text-xs font-medium rounded-lg px-3 py-1.5">
                                <svg class="w-3 h-3 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/>
                                </svg>
                                {{ $originW?->branch?->name }} · {{ $originW?->name }}
                            </span>
                            <svg class="w-4 h-4 text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                            <span class="inline-flex items-center gap-1.5 bg-white border border-amber-200 text-amber-900 text-xs font-medium rounded-lg px-3 py-1.5">
                                <svg class="w-3 h-3 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                </svg>
                                {{ $destW?->branch?->name }} · {{ $destW?->name }}
                            </span>
                        </div>
                    </div>
                @endif
            @endif

            {{-- Meta fields --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1.5">Fecha solicitada</label>
                    <input wire:model="moved_at" type="datetime-local"
                        class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 transition">
                </div>

                {{-- Referencia auto-generada --}}
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1.5">
                        Referencia
                        @if($saleReference)
                            <span class="ml-1.5 text-[10px] font-semibold text-indigo-600 bg-indigo-50 border border-indigo-200 rounded-full px-1.5 py-0.5">
                                Vinculada a venta
                            </span>
                        @elseif($referenceAutoGenerated)
                            <span class="ml-1.5 text-[10px] font-semibold text-teal-600 bg-teal-50 border border-teal-200 rounded-full px-1.5 py-0.5">
                                Auto-generada
                            </span>
                        @endif
                    </label>
                    <div class="relative">
                        <input wire:model="reference" type="text"
                            class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm text-slate-800 font-mono focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 transition
                                {{ $saleReference ? 'pr-9 bg-indigo-50/40' : 'pr-9' }}"
                            placeholder="Referencia..."
                            @if($saleReference) readonly @endif>
                        @if($saleReference)
                            <div class="absolute right-2.5 top-1/2 -translate-y-1/2 text-indigo-400" title="Referencia vinculada a orden de venta">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                            </div>
                        @elseif($referenceAutoGenerated)
                            <button wire:click="regenerateReference" type="button"
                                title="Regenerar referencia"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-indigo-600 transition cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                            </button>
                        @endif
                    </div>
                    @if($saleReference)
                        <p class="text-[11px] text-indigo-500 mt-1 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Tomada de la orden de venta vinculada
                        </p>
                    @elseif($referenceAutoGenerated)
                        <p class="text-[11px] text-slate-400 mt-1 flex items-center gap-1">
                            <svg class="w-3 h-3 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Generada automáticamente · puedes editarla o
                            <button wire:click="regenerateReference" type="button" class="text-indigo-500 hover:text-indigo-700 underline cursor-pointer">regenerar</button>
                        </p>
                    @endif
                </div>

                <div class="sm:col-span-2 lg:col-span-1">
                    <label class="block text-xs font-medium text-slate-600 mb-1.5">Notas</label>
                    <input wire:model="notes" type="text"
                        class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 transition"
                        placeholder="Observaciones opcionales...">
                </div>
            </div>

        @else
            {{-- ── MODO VISTA ── --}}
            @php
                $originBranchName = $stockMovement->warehouse?->branch?->name;
                $destBranchName   = $stockMovement->warehouseDestination?->branch?->name;
            @endphp

            {{-- Banner inter-sucursal --}}
            @if(!$isLocal)
                <div class="flex items-center gap-3 rounded-xl border-2 border-amber-200 bg-amber-50 px-4 py-2.5 mb-4">
                    <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    <div class="flex flex-wrap items-center gap-1.5 text-sm">
                        <span class="font-semibold text-amber-800">Entre sucursales:</span>
                        <span class="bg-white border border-amber-200 text-amber-900 font-medium text-xs rounded-lg px-2.5 py-1">{{ $originBranchName }}</span>
                        <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                        <span class="bg-white border border-amber-200 text-amber-900 font-medium text-xs rounded-lg px-2.5 py-1">{{ $destBranchName }}</span>
                    </div>
                </div>
            @endif

            {{-- Tarjetas origen → destino --}}
            <div class="grid grid-cols-1 sm:grid-cols-[1fr_28px_1fr] gap-3 items-center mb-4">
                <div class="rounded-xl bg-slate-50 border border-slate-200 px-4 py-3">
                    <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide mb-1">Almacén origen</p>
                    <p class="text-sm font-semibold text-slate-900">{{ $stockMovement->warehouse?->name }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $originBranchName }}</p>
                </div>
                <div class="flex items-center justify-center">
                    <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </div>
                </div>
                <div class="rounded-xl border px-4 py-3
                    {{ $isLocal ? 'bg-teal-50 border-teal-200' : 'bg-amber-50 border-amber-200' }}">
                    <p class="text-[10px] font-semibold uppercase tracking-wide mb-1
                        {{ $isLocal ? 'text-teal-500' : 'text-amber-500' }}">Almacén destino</p>
                    <p class="text-sm font-semibold text-slate-900">{{ $stockMovement->warehouseDestination?->name }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $destBranchName }}</p>
                </div>
            </div>

            {{-- Meta --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide mb-1">Fecha solicitada</p>
                    <p class="text-sm text-slate-800">{{ $stockMovement->moved_at->format('d/m/Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide mb-1">Referencia</p>
                    @if($mode !== 'readonly' && $mode !== 'dispatch')
                        <input wire:model="reference" type="text"
                            class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm font-mono text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-300 transition">
                    @else
                        <p class="text-sm font-mono text-slate-800">{{ $reference ?: '—' }}</p>
                    @endif
                </div>
                <div class="col-span-2">
                    <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide mb-1">Notas</p>
                    @if($mode !== 'readonly' && $mode !== 'dispatch')
                        <input wire:model="notes" type="text"
                            class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-300 transition">
                    @else
                        <p class="text-sm text-slate-700">{{ $notes ?: '—' }}</p>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- ── TABLA DE PRODUCTOS ───────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm mb-4 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Productos solicitados</h2>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                    {{ count($items) }}
                </span>
                @if($mode === 'receive' && $stockMovement?->status !== 'requested')
                    <span class="text-xs font-medium text-amber-600 bg-amber-50 border border-amber-100 px-2 py-0.5 rounded-full">
                        Puedes agregar productos adicionales
                    </span>
                @endif
            </div>
            @if($mode !== 'readonly' && $mode !== 'dispatch')
                <livewire:shared.product-picker :multi-select="true" :warehouseId="$warehouse_id" :wire:key="'transfer-picker-' . ($warehouse_id ?? 0)" />
            @endif
        </div>

        @error('items')
            <p class="text-xs text-red-500 px-5 py-2 flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                {{ $message }}
            </p>
        @enderror

        @if(count($items) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm" style="min-width:680px">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="text-left px-4 py-3 text-[10px] font-semibold text-slate-500 uppercase tracking-wide">Producto</th>
                            <th class="text-right px-4 py-3 text-[10px] font-semibold text-slate-500 uppercase tracking-wide w-28">Stock origen</th>
                            <th class="text-right px-4 py-3 text-[10px] font-semibold text-slate-500 uppercase tracking-wide w-32">Cant. solicitada</th>
                            @if($mode === 'dispatch')
                                <th class="text-right px-4 py-3 text-[10px] font-semibold text-slate-500 uppercase tracking-wide w-32">Cant. a enviar</th>
                            @elseif($mode === 'receive' && $stockMovement?->status !== 'requested')
                                @if(in_array($stockMovement?->status, ['in_transit','partially_received']))
                                    <th class="text-right px-4 py-3 text-[10px] font-semibold text-slate-500 uppercase tracking-wide w-28">Despachado</th>
                                @endif
                                <th class="text-right px-4 py-3 text-[10px] font-semibold text-slate-500 uppercase tracking-wide w-32">Cant. recibida</th>
                                <th class="text-left px-4 py-3 text-[10px] font-semibold text-slate-500 uppercase tracking-wide w-32">Fecha recepción</th>
                            @endif
                            <th class="w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($items as $index => $item)
                            <tr class="{{ $item['is_late_addition'] ? 'bg-amber-50/30' : '' }} hover:bg-slate-50/60 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="flex items-start gap-2">
                                        <div class="min-w-0">
                                            <p class="font-medium text-slate-900">{{ $item['product_name'] }}</p>
                                            @if($item['sku'])
                                                <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $item['sku'] }}</p>
                                            @endif
                                        </div>
                                        @if($item['is_late_addition'])
                                            <span class="flex-shrink-0 inline-flex items-center gap-1 bg-amber-100 text-amber-700 border border-amber-200 text-[10px] font-medium px-1.5 py-0.5 rounded-full">
                                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                </svg>
                                                Agregado {{ $item['added_at'] }}
                                            </span>
                                        @endif
                                        @if(!empty($item['received_quantity']) && $item['received_quantity'] > 0)
                                            <span class="flex-shrink-0 inline-flex items-center gap-1 bg-emerald-100 text-emerald-700 border border-emerald-200 text-[10px] font-medium px-1.5 py-0.5 rounded-full">
                                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                Recibido
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm text-slate-600 font-mono">{{ number_format($item['stock_origen'], 2) }}</span>
                                </td>

                                <td class="px-4 py-3 text-right">
                                    @if($mode === 'create' || ($item['is_late_addition'] && empty($item['received_quantity'])))
                                        <input wire:model.live="items.{{ $index }}.quantity"
                                            @if($item['is_late_addition'] && $item['item_id'])
                                                wire:change="updateLateItemQty({{ $index }})"
                                            @endif
                                            type="number" step="0.01" min="0.01" max="{{ $item['stock_origen'] }}"
                                            class="w-24 border border-slate-200 rounded-lg px-2 py-1.5 text-sm text-right font-mono focus:outline-none focus:ring-1 focus:ring-indigo-300 focus:border-indigo-300 transition">
                                    @else
                                        <span class="text-sm text-slate-700 font-mono">{{ number_format($item['quantity'], 2) }}</span>
                                    @endif
                                </td>

                                @if($mode === 'dispatch')
                                    <td class="px-4 py-3 text-right">
                                        @if($dispatchAction === 'partial')
                                            <input wire:model.live="items.{{ $index }}.dispatched_quantity"
                                                type="number" step="0.01" min="0" max="{{ $item['quantity'] }}"
                                                class="w-24 border border-slate-200 rounded-lg px-2 py-1.5 text-sm text-right font-mono focus:outline-none focus:ring-1 focus:ring-indigo-300 focus:border-indigo-300 transition">
                                            @error("items.{$index}.dispatched_quantity")
                                                <p class="text-xs text-red-500 mt-0.5">{{ $message }}</p>
                                            @enderror
                                        @elseif($dispatchAction === 'complete')
                                            <span class="text-sm font-semibold text-emerald-700 font-mono">{{ number_format($item['quantity'], 2) }}</span>
                                        @else
                                            <span class="text-sm text-slate-400">—</span>
                                        @endif
                                    </td>
                                @endif

                                @if($mode === 'receive' && $stockMovement?->status !== 'requested')
                                    @if(in_array($stockMovement?->status, ['in_transit','partially_received']))
                                        <td class="px-4 py-3 text-right">
                                            <span class="text-sm text-slate-600 font-mono">
                                                {{ $item['dispatched_quantity'] !== null ? number_format($item['dispatched_quantity'], 2) : number_format($item['quantity'], 2) }}
                                            </span>
                                        </td>
                                    @endif
                                    <td class="px-4 py-3 text-right">
                                        @if(empty($item['received_quantity']))
                                            <input wire:model="items.{{ $index }}.received_quantity"
                                                type="number" step="0.01" min="0"
                                                max="{{ $item['dispatched_quantity'] ?? $item['quantity'] }}"
                                                placeholder="0"
                                                class="w-24 border border-slate-200 rounded-lg px-2 py-1.5 text-sm text-right font-mono focus:outline-none focus:ring-1 focus:ring-indigo-300 focus:border-indigo-300 transition">
                                        @else
                                            <span class="text-sm font-semibold text-emerald-700 font-mono">{{ number_format($item['received_quantity'], 2) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if(empty($item['received_quantity']))
                                            <input wire:model="items.{{ $index }}.received_at" type="date"
                                                class="w-full border border-slate-200 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300 focus:border-indigo-300 transition">
                                        @else
                                            <span class="text-xs text-slate-500">
                                                {{ $item['received_at'] ? \Carbon\Carbon::parse($item['received_at'])->format('d/m/Y') : '—' }}
                                            </span>
                                        @endif
                                    </td>
                                @endif

                                <td class="px-4 py-3">
                                    @if($mode !== 'readonly' && $mode !== 'dispatch' && empty($item['received_quantity']))
                                        <button type="button" wire:click="removeItem({{ $index }})"
                                            class="flex items-center justify-center w-7 h-7 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition cursor-pointer"
                                            aria-label="Eliminar producto">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="py-12 text-center">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-slate-500">
                    {{ $warehouse_id ? 'Agrega productos desde el catálogo.' : 'Selecciona un almacén origen para agregar productos.' }}
                </p>
                <p class="text-xs text-slate-400 mt-1">Los productos deben tener stock disponible en el almacén origen.</p>
            </div>
        @endif
    </div>

    {{-- ── SECCIÓN DESPACHO ─────────────────────────────────────────────────────── --}}
    @if($mode === 'dispatch')

        @if($stockMovement->status === 'accepted')
            <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-5 mb-4">
                <div class="flex items-start gap-3.5">
                    <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1.5 13h11L19 8M10 12v6M14 12v6"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-indigo-900">Transferencia aceptada — mercancía preparada</p>
                        <p class="text-xs text-indigo-700 mt-0.5 leading-relaxed">
                            El stock ya fue descontado del almacén origen y reservado en el Almacén de Transferencias.
                            Cuando la mercancía salga físicamente, márcala como <strong>Enviada</strong>.
                        </p>
                        @if($stockMovement->dispatch_notes)
                            <p class="text-xs text-indigo-600 mt-2 italic">"{{ $stockMovement->dispatch_notes }}"</p>
                        @endif
                    </div>
                </div>
            </div>

        @else
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 mb-4">
                <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-4">Respuesta del almacén origen</h2>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
                    @foreach([
                        'complete' => ['label' => 'Aceptar completamente', 'desc' => 'Se envían todos los productos y cantidades solicitadas.', 'ring' => 'ring-emerald-400', 'bg' => 'bg-emerald-50', 'dot' => 'bg-emerald-500', 'border' => 'border-emerald-400'],
                        'partial'  => ['label' => 'Aceptar parcialmente',  'desc' => 'Se ajustan cantidades y se justifica la diferencia.',     'ring' => 'ring-amber-400',   'bg' => 'bg-amber-50',   'dot' => 'bg-amber-500',   'border' => 'border-amber-400'],
                        'reject'   => ['label' => 'Rechazar',              'desc' => 'No se puede atender la solicitud. Requiere motivo.',       'ring' => 'ring-red-400',     'bg' => 'bg-red-50',     'dot' => 'bg-red-500',     'border' => 'border-red-400'],
                    ] as $value => $opt)
                        @php $sel = $dispatchAction === $value; @endphp
                        <label wire:click="$set('dispatchAction', '{{ $value }}')"
                            class="cursor-pointer rounded-xl border-2 p-4 transition-all
                                {{ $sel ? "{$opt['border']} {$opt['bg']}" : 'border-slate-200 hover:border-slate-300 bg-white' }}">
                            <div class="flex items-center gap-2 mb-1.5">
                                <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center flex-shrink-0
                                    {{ $sel ? 'border-current ' . $opt['dot'] : 'border-slate-300' }}">
                                    @if($sel)
                                        <div class="w-2 h-2 rounded-full {{ $opt['dot'] }}"></div>
                                    @endif
                                </div>
                                <span class="text-sm font-semibold text-slate-900">{{ $opt['label'] }}</span>
                            </div>
                            <p class="text-xs text-slate-500 pl-6 leading-relaxed">{{ $opt['desc'] }}</p>
                        </label>
                    @endforeach
                </div>

                @if($dispatchAction === 'partial')
                    <div class="flex items-start gap-3 p-3.5 rounded-xl bg-amber-50 border border-amber-200 mb-4">
                        <input wire:model.live="dispatchIsFinal" id="dispatch_is_final" type="checkbox"
                            class="mt-0.5 rounded border-slate-300 text-amber-600 focus:ring-amber-500 cursor-pointer">
                        <div>
                            <label for="dispatch_is_final" class="text-sm font-semibold text-amber-800 cursor-pointer">
                                Marcar como envío final
                            </label>
                            <p class="text-xs text-amber-700 mt-0.5 leading-relaxed">
                                Si está marcado, no se esperarán más envíos y la transferencia se completará al recibirse.
                            </p>
                        </div>
                    </div>
                @endif

                @if($dispatchAction !== 'complete')
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1.5">
                            @if($dispatchAction === 'reject')
                                Motivo del rechazo <span class="text-red-500">*</span>
                            @else
                                Justificación del envío parcial <span class="text-red-500">*</span>
                            @endif
                        </label>
                        <textarea wire:model="dispatchNotes" rows="3"
                            placeholder="{{ $dispatchAction === 'reject' ? 'Explique por qué no se puede atender la solicitud...' : 'Explique por qué no se envían las cantidades completas...' }}"
                            class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 transition resize-none leading-relaxed"></textarea>
                        @error('dispatchNotes')
                            <p class="text-xs text-red-500 mt-1 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                @endif
            </div>
        @endif
    @endif

    {{-- ── FOOTER ACTIONS ───────────────────────────────────────────────────────── --}}
    @if($mode === 'create')
        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 py-4">
            <a wire:navigate href="{{ route('inventory.transfers.index') }}"
                class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-medium border border-slate-200 text-slate-600 rounded-xl hover:bg-slate-50 transition text-center cursor-pointer">
                Cancelar
            </a>
            <button wire:click="save" wire:loading.attr="disabled"
                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-sm transition disabled:opacity-60 cursor-pointer">
                <span wire:loading.remove wire:target="save">
                    <svg class="w-4 h-4 inline-block mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    Solicitar transferencia
                </span>
                <span wire:loading wire:target="save" class="flex items-center gap-2">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    Guardando...
                </span>
            </button>
        </div>

    @elseif($mode === 'dispatch')
        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 py-4">
            <a wire:navigate href="{{ route('inventory.transfers.index') }}"
                class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-medium border border-slate-200 text-slate-600 rounded-xl hover:bg-slate-50 transition cursor-pointer">
                Cerrar
            </a>
            @if($stockMovement->status === 'accepted')
                <button wire:click="markAsSent"
                    wire:confirm="¿Confirmar que la mercancía ya fue enviada físicamente?"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-sm transition disabled:opacity-60 cursor-pointer">
                    <span wire:loading.remove wire:target="markAsSent">
                        <svg class="w-4 h-4 inline-block mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Marcar como enviada
                    </span>
                    <span wire:loading wire:target="markAsSent" class="flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        Procesando...
                    </span>
                </button>
            @else
                <button wire:click="submitDispatch" wire:loading.attr="disabled"
                    @class([
                        'inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold rounded-xl shadow-sm transition disabled:opacity-60 cursor-pointer',
                        'bg-emerald-600 hover:bg-emerald-700 text-white' => $dispatchAction === 'complete',
                        'bg-amber-500 hover:bg-amber-600 text-white'     => $dispatchAction === 'partial',
                        'bg-red-600 hover:bg-red-700 text-white'         => $dispatchAction === 'reject',
                    ])>
                    <span wire:loading.remove wire:target="submitDispatch">
                        @if($dispatchAction === 'complete') Confirmar aceptación completa
                        @elseif($dispatchAction === 'partial') Confirmar aceptación parcial
                        @else Confirmar rechazo
                        @endif
                    </span>
                    <span wire:loading wire:target="submitDispatch" class="flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        Procesando...
                    </span>
                </button>
            @endif
        </div>

    @elseif($mode === 'receive' && $stockMovement?->status !== 'requested')
        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 py-4">
            <a wire:navigate href="{{ route('inventory.transfers.index') }}"
                class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-medium border border-slate-200 text-slate-600 rounded-xl hover:bg-slate-50 transition cursor-pointer">
                Cerrar
            </a>
            <button wire:click="saveReceipt" wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-sm transition disabled:opacity-60 cursor-pointer">
                <span wire:loading.remove wire:target="saveReceipt">
                    <svg class="w-4 h-4 inline-block mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Registrar recepción
                </span>
                <span wire:loading wire:target="saveReceipt" class="flex items-center gap-2">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    Guardando...
                </span>
            </button>
        </div>

    @else
        @if($stockMovement && in_array($stockMovement->status, ['in_transit','partially_received']))
            <div class="flex items-start gap-3 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 mb-4">
                <svg class="w-4 h-4 text-slate-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm text-slate-600">
                    <span class="font-semibold text-slate-700">Esperando recepción.</span>
                    Solo puede registrarla el área de <strong>Compras</strong> o el <strong>almacenista del almacén destino</strong>.
                </p>
            </div>
        @endif
        <div class="flex justify-end py-4">
            <a wire:navigate href="{{ route('inventory.transfers.index') }}"
                class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-medium border border-slate-200 text-slate-600 rounded-xl hover:bg-slate-50 transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Volver al listado
            </a>
        </div>
    @endif

</div>{{-- /max-w-5xl --}}
</div>{{-- /min-h-screen --}}
