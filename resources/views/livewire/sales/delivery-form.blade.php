<div class="max-w-5xl space-y-5">

    {{-- Encabezado --}}
    <div class="flex items-center gap-3">
        @if($order)
            <a wire:navigate href="{{ route('sales.orders.show', $order) }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
        @endif
        <div>
            <h1 class="text-xl font-medium text-gray-900">Salida de almacén</h1>
            @if($order)
                <p class="text-sm text-gray-500">Orden: {{ $order->folio }} — {{ $order->customer->name }}</p>
            @endif
        </div>
    </div>

    {{-- Flash --}}
    @if(session('scan_ok'))
        <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-4 py-3">
            {{ session('scan_ok') }}
        </div>
    @endif
    @if(session('scan_error'))
        <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3">
            {{ session('scan_error') }}
        </div>
    @endif

    <form class="space-y-5">

        {{-- ── Cabecera ─────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Datos de la salida</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Almacén --}}
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Almacén de salida *</label>
                    <select wire:model.live="warehouse_id"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="">— Seleccionar almacén —</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }} — {{ $wh->branch->name }}</option>
                        @endforeach
                    </select>
                    @error('warehouse_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Motivo --}}
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Motivo de salida *</label>
                    <select wire:model.live="reason"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        @foreach($reasons as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Notas --}}
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Notas</label>
                    <input wire:model="notes" type="text" placeholder="Observaciones opcionales"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
            </div>
        </div>

        {{-- ── Escaneo ──────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3 mb-4">
                Escanear código de lote
            </h2>
            <div class="flex gap-3 items-center">
                <div class="relative flex-1 max-w-xs">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1M12 19v1M4 12h1M19 12h1M6.22 6.22l.7.7M17.08 17.08l.7.7M6.22 17.78l.7-.7M17.08 6.92l.7-.7"/>
                        <rect x="7" y="7" width="10" height="10" rx="1" stroke-width="1.5"/>
                    </svg>
                    <input wire:model.live.debounce.300ms="scanInput"
                        type="text"
                        placeholder="Escanear o escribir código de lote…"
                        class="w-full pl-9 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                <span class="text-xs text-gray-400">El escáner envía Enter automáticamente</span>
            </div>
        </div>

        {{-- ── Productos con lotes ──────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100">
                <h2 class="text-sm font-medium text-gray-700">Productos y asignación de lotes</h2>
                <div class="flex gap-2">
                    <button type="button" wire:click="setFullDelivery"
                        class="px-3 py-1.5 text-xs rounded-lg font-medium transition
                            {{ $delivery_mode === 'full'
                                ? 'bg-indigo-600 text-white'
                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Entrega completa
                    </button>
                    <button type="button" wire:click="setPartialDelivery"
                        class="px-3 py-1.5 text-xs rounded-lg font-medium transition
                            {{ $delivery_mode === 'partial'
                                ? 'bg-amber-500 text-white'
                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Entrega parcial
                    </button>
                </div>
            </div>

            @error('items') <p class="text-xs text-red-500 px-5 pt-2">{{ $message }}</p> @enderror

            @if(empty($items))
                <div class="px-5 py-10 text-center text-sm text-gray-400">
                    Todos los productos ya fueron entregados.
                </div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach($items as $index => $item)
                        <div x-data="{ open: true }" class="p-5">

                            {{-- Fila resumen del producto --}}
                            <div class="flex items-start gap-4">
                                {{-- Checkbox incluir --}}
                                <div class="pt-0.5">
                                    <input type="checkbox" wire:model.live="items.{{ $index }}.include"
                                        class="w-4 h-4 rounded text-indigo-600 border-gray-300 focus:ring-indigo-500">
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-wrap items-center gap-3 mb-2">
                                        <p class="font-medium text-gray-900 text-sm">{{ $item['product_name'] }}</p>

                                        {{-- Disponible en lotes --}}
                                        @php
                                            $lotAvail = (float)($item['lot_available'] ?? 0);
                                            $needed   = (float)$item['quantity_to_deliver'];
                                            $stockOk  = $lotAvail >= $needed;
                                        @endphp
                                        <span class="text-xs px-2 py-0.5 rounded-full
                                            {{ $stockOk ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                                            {{ $lotAvail }} disponible en lotes
                                        </span>

                                        <button type="button" @click="open = !open"
                                            class="ml-auto text-xs text-indigo-500 hover:text-indigo-700 flex items-center gap-1">
                                            <span x-text="open ? 'Ocultar lotes' : 'Ver lotes'"></span>
                                            <svg class="w-3 h-3 transition-transform" :class="open ? 'rotate-180' : ''"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>
                                    </div>

                                    {{-- Cantidad a entregar --}}
                                    <div class="flex items-center gap-4">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-gray-500">Pendiente:</span>
                                            <span class="text-sm font-medium text-gray-700">{{ $item['quantity_pending'] }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <label class="text-xs text-gray-500">A entregar:</label>
                                            <input wire:model.live.debounce.400ms="items.{{ $index }}.quantity_to_deliver"
                                                type="number" step="0.01" min="0"
                                                max="{{ $item['quantity_pending'] }}"
                                                :disabled="{{ $item['include'] ? 'false' : 'true' }}"
                                                class="w-24 border border-gray-200 rounded px-2 py-1 text-sm text-center
                                                    focus:outline-none focus:ring-1 focus:ring-indigo-300
                                                    disabled:bg-gray-100 disabled:text-gray-400">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Tabla de lotes PEPS --}}
                            <div x-show="open" x-transition class="mt-4 ml-8">
                                @if(empty($item['lot_lines']))
                                    <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-sm text-amber-700">
                                        <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                        No hay lotes disponibles en el almacén seleccionado para este producto.
                                        @if(!$warehouse_id)
                                            Selecciona un almacén para ver los lotes.
                                        @endif
                                    </div>
                                @else
                                    <div class="rounded-lg border border-gray-200 overflow-hidden">
                                        <table class="w-full text-xs">
                                            <thead>
                                                <tr class="bg-gray-50 border-b border-gray-200">
                                                    <th class="text-left px-3 py-2 font-medium text-gray-500">Lote</th>
                                                    <th class="text-left px-3 py-2 font-medium text-gray-500">Código de barras</th>
                                                    <th class="text-left px-3 py-2 font-medium text-gray-500">Ingreso</th>
                                                    <th class="text-left px-3 py-2 font-medium text-gray-500">Vence</th>
                                                    <th class="text-right px-3 py-2 font-medium text-gray-500">Disponible</th>
                                                    <th class="text-right px-3 py-2 font-medium text-gray-500">Costo unitario</th>
                                                    <th class="text-right px-3 py-2 font-medium text-gray-500 w-24">Cantidad PEPS</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                @foreach($item['lot_lines'] as $li => $line)
                                                    <tr class="{{ $li === 0 ? 'bg-green-50/50' : 'bg-white' }}">
                                                        <td class="px-3 py-2">
                                                            <span class="font-mono font-medium text-gray-800">{{ $line['lot_number'] }}</span>
                                                            @if($li === 0)
                                                                <span class="ml-1 text-[10px] bg-green-100 text-green-700 px-1.5 py-0.5 rounded-full">más antiguo</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-3 py-2 font-mono text-gray-500">{{ $line['barcode'] }}</td>
                                                        <td class="px-3 py-2 text-gray-600">{{ $line['entry_date'] }}</td>
                                                        <td class="px-3 py-2">
                                                            @if($line['expiry_date'])
                                                                @php $exp = \Carbon\Carbon::parse($line['expiry_date']); @endphp
                                                                <span class="{{ $exp->isPast() ? 'text-red-600 font-medium' : ($exp->diffInDays() < 30 ? 'text-amber-600' : 'text-gray-600') }}">
                                                                    {{ $line['expiry_date'] }}
                                                                </span>
                                                            @else
                                                                <span class="text-gray-300">—</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-3 py-2 text-right text-gray-700">{{ number_format($line['available'], 2) }}</td>
                                                        <td class="px-3 py-2 text-right text-gray-600">${{ number_format($line['unit_cost'], 2) }}</td>
                                                        <td class="px-3 py-2">
                                                            <input wire:model.live.debounce.400ms="items.{{ $index }}.lot_lines.{{ $li }}.quantity"
                                                                type="number" step="0.0001" min="0"
                                                                max="{{ $line['available'] }}"
                                                                class="w-full border border-gray-200 rounded px-2 py-1 text-right
                                                                    focus:outline-none focus:ring-1 focus:ring-indigo-300
                                                                    {{ $li === 0 ? 'border-green-300' : '' }}">
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr class="bg-gray-50 border-t border-gray-200">
                                                    <td colspan="6" class="px-3 py-2 text-xs text-gray-500 text-right font-medium">
                                                        Total asignado:
                                                    </td>
                                                    <td class="px-3 py-2 text-right font-semibold text-gray-800 text-xs">
                                                        {{ number_format(collect($item['lot_lines'])->sum('quantity'), 4) }}
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    {{-- Advertencia si la suma de lotes no cubre la cantidad solicitada --}}
                                    @php
                                        $assigned = collect($item['lot_lines'])->sum(fn($l) => (float)$l['quantity']);
                                        $delta    = abs($assigned - (float)$item['quantity_to_deliver']);
                                    @endphp
                                    @if($delta > 0.001 && (float)$item['quantity_to_deliver'] > 0)
                                        <p class="mt-2 text-xs text-amber-600">
                                            <svg class="inline w-3.5 h-3.5 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            La suma de lotes ({{ number_format($assigned, 2) }}) difiere de la cantidad a entregar ({{ number_format((float)$item['quantity_to_deliver'], 2) }}).
                                        </p>
                                    @endif
                                @endif
                            </div>

                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ── Acciones ─────────────────────────────────────────────────── --}}
        @if(!empty($items))
            <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pb-6">
                @if($order)
                    <a wire:navigate href="{{ route('sales.orders.show', $order) }}"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition text-center">
                        Cancelar
                    </a>
                @else
                    <a wire:navigate href="{{ route('inventory.movements.index') }}"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition text-center">
                        Cancelar
                    </a>
                @endif
                <button type="button" wire:click="save" wire:loading.attr="disabled"
                    class="px-5 py-2 text-sm bg-teal-600 hover:bg-teal-700 text-white rounded-lg font-medium transition
                        disabled:opacity-60 flex items-center justify-center gap-2">
                    <svg wire:loading wire:target="save" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                    Confirmar salida
                </button>
            </div>
        @endif

    </form>
</div>
