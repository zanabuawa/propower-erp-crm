<div class="max-w-5xl">
    {{-- Header --}}
    <div class="flex items-start justify-between gap-3 mb-6">
        <div class="flex items-center gap-3">
            <a wire:navigate href="{{ route('inventory.transfers.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-medium text-gray-900">
                    @if($mode === 'create')
                        Nueva transferencia de inventario
                    @else
                        Transferencia {{ $stockMovement->folio }}
                    @endif
                </h1>
                <div class="flex items-center gap-2 mt-1 flex-wrap">
                    @if($isLocal)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-teal-50 text-teal-700 border border-teal-100">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            Local
                        </span>
                    @endif
                    @if($stockMovement)
                        @php
                            $statusColors = [
                                'requested'          => 'bg-blue-50 text-blue-700',
                                'accepted'           => 'bg-indigo-50 text-indigo-700',
                                'in_transit'         => 'bg-amber-50 text-amber-700',
                                'partially_received' => 'bg-orange-50 text-orange-700',
                                'completed'          => 'bg-emerald-50 text-emerald-700',
                                'rejected'           => 'bg-red-50 text-red-700',
                                'cancelled'          => 'bg-red-50 text-red-700',
                            ];
                        @endphp
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$stockMovement->status] ?? 'bg-gray-100 text-gray-500' }}">
                            {{ \App\Models\StockMovement::TRANSFER_STATUSES[$stockMovement->status] ?? $stockMovement->status }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Action buttons --}}
        <div class="flex gap-2 flex-wrap justify-end">
            {{-- Local transfer: authorize directly --}}
            @if($mode === 'receive' && $stockMovement?->status === 'requested' && $isLocal)
                <button wire:click="markInTransit" wire:confirm="¿Autorizar y marcar como en tránsito?"
                    class="px-3 py-1.5 text-xs font-medium bg-teal-600 hover:bg-teal-700 text-white rounded-lg transition">
                    Autorizar transferencia
                </button>
            @endif

            {{-- Cancel button: requester on requested, or admin/gerente on any active status --}}
            @if($stockMovement && in_array($stockMovement->status, ['requested', 'accepted', 'in_transit', 'partially_received']))
                @if($stockMovement->user_id === auth()->id() || auth()->user()->hasRole(['admin', 'gerente']))
                    <button wire:click="cancelTransfer"
                        wire:confirm="{{ $stockMovement->status === 'accepted' ? '¿Cancelar esta transferencia? El stock reservado en el Almacén de Transferencias será devuelto al almacén origen.' : '¿Cancelar esta transferencia?' }}"
                        class="px-3 py-1.5 text-xs font-medium border border-red-200 text-red-600 hover:bg-red-50 rounded-lg transition">
                        Cancelar
                    </button>
                @endif
            @endif
        </div>
    </div>

    <x-alert />

    {{-- Dispatch notes / rejection reason (readonly display) --}}
    @if($stockMovement && $stockMovement->dispatch_notes)
        @php
            $isRejected = $stockMovement->status === 'rejected';
            $noteColor  = $isRejected ? 'bg-red-50 border-red-200 text-red-800' : 'bg-amber-50 border-amber-200 text-amber-800';
            $noteLabel  = $isRejected ? 'Motivo del rechazo' : 'Nota del almacén origen';
        @endphp
        <div class="rounded-xl border {{ $noteColor }} px-4 py-3 mb-5 text-sm">
            <p class="font-medium mb-0.5">{{ $noteLabel }}</p>
            <p>{{ $stockMovement->dispatch_notes }}</p>
        </div>
    @endif

    {{-- Warehouses & meta --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4 mb-5">
        <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Origen y destino</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Almacén origen *</label>
                @if($mode === 'create')
                    <select wire:model.live="warehouse_id"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="">— Seleccionar —</option>
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}">{{ $w->name }} — {{ $w->branch->name }}</option>
                        @endforeach
                    </select>
                    @error('warehouse_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                @else
                    <p class="text-sm text-gray-900 py-2">{{ $stockMovement->warehouse?->name }}
                        <span class="text-xs text-gray-400">— {{ $stockMovement->warehouse?->branch?->name }}</span>
                    </p>
                @endif
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Almacén destino *</label>
                @if($mode === 'create')
                    <select wire:model="warehouse_destination_id"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="">— Seleccionar —</option>
                        @foreach($warehouses as $w)
                            @if($w->id != $warehouse_id)
                                <option value="{{ $w->id }}">{{ $w->name }} — {{ $w->branch->name }}</option>
                            @endif
                        @endforeach
                    </select>
                    @error('warehouse_destination_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                @else
                    <p class="text-sm text-gray-900 py-2">{{ $stockMovement->warehouseDestination?->name }}
                        <span class="text-xs text-gray-400">— {{ $stockMovement->warehouseDestination?->branch?->name }}</span>
                    </p>
                @endif
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Fecha solicitada</label>
                @if($mode === 'create')
                    <input wire:model="moved_at" type="datetime-local"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                @else
                    <p class="text-sm text-gray-900 py-2">{{ $stockMovement->moved_at->format('d/m/Y H:i') }}</p>
                @endif
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Referencia</label>
                @if($mode !== 'readonly' && $mode !== 'dispatch')
                    <input wire:model="reference" type="text"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        placeholder="OC, nota de remisión...">
                @else
                    <p class="text-sm text-gray-900 py-2">{{ $reference ?: '—' }}</p>
                @endif
            </div>
            <div class="sm:col-span-2 lg:col-span-4">
                <label class="block text-xs text-gray-500 mb-1">Notas</label>
                @if($mode !== 'readonly' && $mode !== 'dispatch')
                    <input wire:model="notes" type="text"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                @else
                    <p class="text-sm text-gray-700 py-2">{{ $notes ?: '—' }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Products table --}}
    <div class="bg-white rounded-xl border border-gray-200 mb-5 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100">
            <h2 class="text-sm font-medium text-gray-700">
                Productos solicitados
                <span class="ml-2 text-xs font-normal text-gray-400">{{ count($items) }} producto(s)</span>
                @if($mode === 'receive' && $stockMovement?->status !== 'requested')
                    <span class="ml-2 text-xs font-normal text-amber-600 bg-amber-50 border border-amber-100 px-2 py-0.5 rounded-full">
                        Puedes agregar productos adicionales
                    </span>
                @endif
            </h2>
            @if($mode !== 'readonly' && $mode !== 'dispatch')
                <livewire:shared.product-picker :warehouseId="$warehouse_id" :wire:key="'transfer-picker-' . ($warehouse_id ?? 0)" />
            @endif
        </div>

        @error('items') <p class="text-xs text-red-500 px-5 py-2">{{ $message }}</p> @enderror

        @if(count($items) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm" style="min-width:700px">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Producto</th>
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-28">Stock origen</th>
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-28">Cant. solicitada</th>
                            @if($mode === 'dispatch')
                                <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-32">Cant. a enviar</th>
                            @elseif($mode === 'receive' && $stockMovement?->status !== 'requested')
                                @if($stockMovement?->status === 'in_transit' || $stockMovement?->status === 'partially_received')
                                    <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-28">Despachado</th>
                                @endif
                                <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-28">Cant. recibida</th>
                                <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-32">Fecha recepción</th>
                            @endif
                            <th class="w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($items as $index => $item)
                            <tr class="{{ $item['is_late_addition'] ? 'bg-amber-50/40' : '' }} hover:bg-gray-50 transition">
                                <td class="px-4 py-2.5">
                                    <div class="flex items-start gap-2">
                                        <div>
                                            <p class="font-medium text-gray-900 text-sm">{{ $item['product_name'] }}</p>
                                            @if($item['sku'])
                                                <p class="text-xs text-gray-400 font-mono">{{ $item['sku'] }}</p>
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
                                            <span class="flex-shrink-0 inline-flex items-center bg-emerald-100 text-emerald-700 text-[10px] font-medium px-1.5 py-0.5 rounded-full border border-emerald-200">
                                                Recibido
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Stock origen --}}
                                <td class="px-4 py-2.5 text-gray-600 text-sm">
                                    {{ number_format($item['stock_origen'], 2) }}
                                </td>

                                {{-- Cantidad solicitada --}}
                                <td class="px-4 py-2.5">
                                    @if($mode === 'create' || ($item['is_late_addition'] && empty($item['received_quantity'])))
                                        <input wire:model.live="items.{{ $index }}.quantity"
                                            @if($item['is_late_addition'] && $item['item_id'])
                                                wire:change="updateLateItemQty({{ $index }})"
                                            @endif
                                            type="number" step="0.01" min="0.01"
                                            max="{{ $item['stock_origen'] }}"
                                            class="w-full border border-gray-200 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                                    @else
                                        <span class="text-sm text-gray-700">{{ number_format($item['quantity'], 2) }}</span>
                                    @endif
                                </td>

                                {{-- Dispatch: cantidad a enviar (editable en modo parcial) --}}
                                @if($mode === 'dispatch')
                                    <td class="px-4 py-2.5">
                                        @if($dispatchAction === 'partial')
                                            <input wire:model.live="items.{{ $index }}.dispatched_quantity"
                                                type="number" step="0.01" min="0"
                                                max="{{ $item['quantity'] }}"
                                                class="w-full border border-gray-200 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                                            @error("items.{$index}.dispatched_quantity")
                                                <p class="text-xs text-red-500 mt-0.5">{{ $message }}</p>
                                            @enderror
                                        @elseif($dispatchAction === 'complete')
                                            <span class="text-sm font-medium text-emerald-700">{{ number_format($item['quantity'], 2) }}</span>
                                        @else
                                            <span class="text-sm text-gray-400">—</span>
                                        @endif
                                    </td>
                                @endif

                                {{-- Receive: dispatched + received columns --}}
                                @if($mode === 'receive' && $stockMovement?->status !== 'requested')
                                    @if(in_array($stockMovement?->status, ['in_transit', 'partially_received']))
                                        <td class="px-4 py-2.5 text-sm text-gray-600">
                                            {{ $item['dispatched_quantity'] !== null ? number_format($item['dispatched_quantity'], 2) : number_format($item['quantity'], 2) }}
                                        </td>
                                    @endif
                                    <td class="px-4 py-2.5">
                                        @if(empty($item['received_quantity']))
                                            <input wire:model="items.{{ $index }}.received_quantity"
                                                type="number" step="0.01" min="0"
                                                max="{{ $item['dispatched_quantity'] ?? $item['quantity'] }}"
                                                placeholder="0"
                                                class="w-full border border-gray-200 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                                        @else
                                            <span class="text-sm font-medium text-emerald-700">{{ number_format($item['received_quantity'], 2) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5">
                                        @if(empty($item['received_quantity']))
                                            <input wire:model="items.{{ $index }}.received_at"
                                                type="date"
                                                class="w-full border border-gray-200 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                                        @else
                                            <span class="text-xs text-gray-500">
                                                {{ $item['received_at'] ? \Carbon\Carbon::parse($item['received_at'])->format('d/m/Y') : '—' }}
                                            </span>
                                        @endif
                                    </td>
                                @endif

                                <td class="px-4 py-2.5">
                                    @if($mode !== 'readonly' && $mode !== 'dispatch' && empty($item['received_quantity']))
                                        <button type="button" wire:click="removeItem({{ $index }})"
                                            class="text-red-400 hover:text-red-600 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
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
            <div class="py-10 text-center text-gray-400 text-sm">
                {{ $warehouse_id ? 'Agrega productos desde el catálogo de arriba.' : 'Selecciona un almacén origen para ver el catálogo de productos.' }}
            </div>
        @endif
    </div>

    {{-- ── DISPATCH SECTION ──────────────────────────────────────────────────── --}}
    @if($mode === 'dispatch')

        @if($stockMovement->status === 'accepted')
            {{-- ── STEP 5: Mark as sent ─────────────────────────────────────── --}}
            <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-5 mb-5">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1.5 13h11L19 8M10 12v6M14 12v6"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-indigo-900">Transferencia aceptada — mercancía preparada</p>
                        <p class="text-xs text-indigo-700 mt-0.5">
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
            {{-- ── STEP 3: Dispatch decision ────────────────────────────────── --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 mb-5">
                <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3 mb-4">
                    Respuesta del almacén origen
                </h2>

                {{-- Action cards --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
                    @foreach([
                        'complete' => ['label' => 'Aceptar completamente', 'desc' => 'Se envían todos los productos y cantidades solicitadas.', 'color' => 'emerald'],
                        'partial'  => ['label' => 'Aceptar parcialmente',  'desc' => 'Se ajustan las cantidades a enviar y se justifica la diferencia.', 'color' => 'amber'],
                        'reject'   => ['label' => 'Rechazar',              'desc' => 'No se puede atender la solicitud. Requiere motivo obligatorio.', 'color' => 'red'],
                    ] as $value => $opt)
                        @php $isSelected = $dispatchAction === $value; @endphp
                        <label wire:click="$set('dispatchAction', '{{ $value }}')"
                            class="cursor-pointer rounded-xl border-2 p-4 transition
                                {{ $isSelected
                                    ? "border-{$opt['color']}-400 bg-{$opt['color']}-50"
                                    : 'border-gray-200 hover:border-gray-300 bg-white' }}">
                            <div class="flex items-center gap-2 mb-1">
                                <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center flex-shrink-0
                                    {{ $isSelected ? "border-{$opt['color']}-500" : 'border-gray-300' }}">
                                    @if($isSelected)
                                        <div class="w-2 h-2 rounded-full bg-{{ $opt['color'] }}-500"></div>
                                    @endif
                                </div>
                                <span class="text-sm font-medium text-gray-900">{{ $opt['label'] }}</span>
                            </div>
                            <p class="text-xs text-gray-500 pl-6">{{ $opt['desc'] }}</p>
                        </label>
                    @endforeach
                </div>

                {{-- Partial: is_final toggle --}}
                @if($dispatchAction === 'partial')
                    <div class="flex items-start gap-3 p-3 rounded-lg bg-amber-50 border border-amber-100 mb-4">
                        <input wire:model.live="dispatchIsFinal" id="dispatch_is_final" type="checkbox"
                            class="mt-0.5 rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                        <div>
                            <label for="dispatch_is_final" class="text-sm font-medium text-amber-800 cursor-pointer">
                                Marcar como envío final
                            </label>
                            <p class="text-xs text-amber-700 mt-0.5">
                                Si está marcado, no se esperarán más envíos y la transferencia se completará al recibirse.
                                Si no, quedará como recepción parcial con pendiente.
                            </p>
                        </div>
                    </div>
                @endif

                {{-- Notes / Rejection reason --}}
                @if($dispatchAction !== 'complete')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            @if($dispatchAction === 'reject')
                                Motivo del rechazo <span class="text-red-500">*</span>
                            @else
                                Justificación del envío parcial <span class="text-red-500">*</span>
                            @endif
                        </label>
                        <textarea wire:model="dispatchNotes" rows="3"
                            placeholder="{{ $dispatchAction === 'reject' ? 'Explique por qué no se puede atender la solicitud...' : 'Explique por qué no se envían las cantidades completas...' }}"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 resize-none"></textarea>
                        @error('dispatchNotes') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                @endif
            </div>
        @endif

    @endif

    {{-- ── FOOTER ACTIONS ───────────────────────────────────────────────────── --}}
    @if($mode === 'create')
        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pb-6">
            <a wire:navigate href="{{ route('inventory.transfers.index') }}"
                class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition text-center">
                Cancelar
            </a>
            <button wire:click="save" wire:loading.attr="disabled"
                class="px-5 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition disabled:opacity-60">
                <span wire:loading.remove wire:target="save">Solicitar transferencia</span>
                <span wire:loading wire:target="save">Guardando...</span>
            </button>
        </div>

    @elseif($mode === 'dispatch')
        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pb-6">
            <a wire:navigate href="{{ route('inventory.transfers.index') }}"
                class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition text-center">
                Cerrar
            </a>

            @if($stockMovement->status === 'accepted')
                {{-- Step 5: physical send confirmation --}}
                <button wire:click="markAsSent"
                    wire:confirm="¿Confirmar que la mercancía ya fue enviada físicamente?"
                    wire:loading.attr="disabled"
                    class="px-5 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition disabled:opacity-60">
                    <span wire:loading.remove wire:target="markAsSent">
                        <svg class="w-4 h-4 inline-block mr-1.5 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Marcar como enviada
                    </span>
                    <span wire:loading wire:target="markAsSent">Procesando...</span>
                </button>
            @else
                {{-- Step 3: dispatch decision --}}
                <button wire:click="submitDispatch" wire:loading.attr="disabled"
                    @class([
                        'px-5 py-2 text-sm rounded-lg font-medium transition disabled:opacity-60',
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
                    <span wire:loading wire:target="submitDispatch">Procesando...</span>
                </button>
            @endif
        </div>

    @elseif($mode === 'receive' && $stockMovement?->status !== 'requested')
        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pb-6">
            <a wire:navigate href="{{ route('inventory.transfers.index') }}"
                class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition text-center">
                Cerrar
            </a>
            <button wire:click="saveReceipt" wire:loading.attr="disabled"
                class="px-5 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition disabled:opacity-60">
                <span wire:loading.remove wire:target="saveReceipt">Registrar recepción</span>
                <span wire:loading wire:target="saveReceipt">Guardando...</span>
            </button>
        </div>

    @else
        {{-- Readonly footer: if in_transit/partially_received but no receive permission, explain why --}}
        @if($stockMovement && in_array($stockMovement->status, ['in_transit', 'partially_received']))
            <div class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 mb-5 text-sm text-gray-600">
                <p>
                    <span class="font-medium text-gray-700">Esperando recepción.</span>
                    La recepción solo puede ser registrada por el área de
                    <strong>Compras</strong> o el <strong>almacenista del almacén destino</strong>.
                </p>
            </div>
        @endif
        <div class="flex justify-end pb-6">
            <a wire:navigate href="{{ route('inventory.transfers.index') }}"
                class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                Volver al listado
            </a>
        </div>
    @endif
</div>
