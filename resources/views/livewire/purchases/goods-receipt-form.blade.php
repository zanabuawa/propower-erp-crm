<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('purchases.goods-receipts.index') }}" 
                   class="group flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-teal-600 hover:border-teal-100 hover:shadow-sm transition-all duration-200">
                    <svg class="w-5 h-5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">Recepción de Mercancía</h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Entrada física al inventario</p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a wire:navigate href="{{ route('purchases.goods-receipts.index') }}"
                    class="hidden sm:inline-flex px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">
                    Cancelar
                </a>
                @if(count($items) > 0)
                <button type="button" wire:click="confirm"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-teal-600 to-teal-700 hover:from-teal-700 hover:to-teal-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-teal-500/25 hover:shadow-teal-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <span>Revisar y Confirmar</span>
                </button>
                @endif
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto p-4 sm:p-6 lg:p-8">
        <div class="space-y-6 lg:space-y-8">
            <x-alert />

            {{-- ── SECCIÓN: TIPO Y ALMACÉN ────────────────────────────────────── --}}
            <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                <div class="p-6 lg:p-8 space-y-8">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-5">
                        <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center text-teal-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <h2 class="text-base font-bold text-slate-800">Configuración de Entrada</h2>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                        {{-- Tipo de recepción --}}
                        <div class="lg:col-span-4 space-y-4">
                            <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest">¿Qué tipo de mercancía ingresa? *</label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                @foreach(\App\Models\PurchaseReceipt::RECEPTION_TYPES as $value => $label)
                                    @php
                                        $tIcons = ['purchase' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z', 'return' => 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6', 'transfer' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4', 'defective' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16'];
                                        $tColors = ['purchase' => 'teal', 'return' => 'rose', 'transfer' => 'indigo', 'defective' => 'amber'];
                                        $tColor = $tColors[$value] ?? 'slate';
                                    @endphp
                                    <label class="relative group cursor-pointer">
                                        <input type="radio" wire:model.live="reception_type" value="{{ $value }}" class="sr-only">
                                        <div class="h-full flex flex-col items-center gap-3 p-4 rounded-2xl border-2 transition-all duration-200 
                                            {{ $reception_type === $value 
                                                ? "bg-$tColor-50 border-$tColor-500 shadow-sm shadow-$tColor-100" 
                                                : "bg-white border-slate-100 hover:border-$tColor-200 hover:bg-slate-50/50" }}">
                                            <div class="w-10 h-10 rounded-xl flex items-center justify-center transition-transform group-hover:scale-110 
                                                {{ $reception_type === $value ? "bg-white text-$tColor-600 shadow-sm" : "bg-slate-50 text-slate-400" }}">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tIcons[$value] }}"/></svg>
                                            </div>
                                            <span class="text-[10px] font-black uppercase tracking-wider text-center {{ $reception_type === $value ? "text-$tColor-700" : "text-slate-500" }}">
                                                {{ $label }}
                                            </span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Destino --}}
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Almacén Destino *</label>
                            <div class="relative">
                                <select wire:model="warehouse_id"
                                    class="w-full bg-slate-50 border-none rounded-2xl px-4 py-3.5 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-teal-500/20 appearance-none cursor-pointer">
                                    <option value="">— Seleccionar —</option>
                                    @foreach($warehouses as $wh)
                                        <option value="{{ $wh->id }}">
                                            {{ $wh->name }}{{ $wh->is_defective ? ' (Defectuosos)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                            @error('warehouse_id') <p class="text-[10px] text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Referencia --}}
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Referencia / Remisión</label>
                            <input wire:model="reference" type="text" placeholder="N° Documento"
                                class="w-full bg-slate-50 border-none rounded-2xl px-4 py-3.5 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-teal-500/20">
                        </div>

                        {{-- Gastos --}}
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Gastos Operativos ($)</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">$</span>
                                <input wire:model="operating_expenses" type="number" step="0.01" min="0" placeholder="0.00"
                                    class="w-full bg-slate-50 border-none rounded-2xl pl-8 pr-4 py-3.5 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-amber-500/20">
                            </div>
                        </div>

                        {{-- Cuenta (solo compras) --}}
                        <div class="lg:col-span-1 space-y-2">
                            @if($reception_type === 'purchase')
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Cuenta de Egreso *</label>
                                <select wire:model="financeAccountId"
                                    class="w-full bg-slate-50 border-none rounded-2xl px-4 py-3.5 text-[11px] font-bold text-slate-700 focus:ring-2 focus:ring-teal-500/20">
                                    <option value="">— Seleccionar cuenta —</option>
                                    @foreach($financeAccounts as $account)
                                        <option value="{{ $account['id'] }}">
                                            {{ $account['name'] }} ({{ $account['currency'] }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('financeAccountId') <p class="text-[10px] text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                            @endif
                        </div>

                        {{-- Notas --}}
                        <div class="lg:col-span-4 space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Observaciones</label>
                            <input wire:model="notes" type="text" placeholder="Notas internas para esta entrada..."
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-3.5 text-sm font-medium text-slate-700 focus:ring-2 focus:ring-teal-500/20">
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── SECCIÓN: DOCUMENTO ORIGEN ──────────────────────────────────── --}}
            @php
                $hasActiveSource = ($reception_type === 'purchase') || ($reception_type === 'return') || ($reception_type === 'transfer');
            @endphp
            @if($hasActiveSource)
            <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden animate-in fade-in zoom-in-95">
                <div class="p-6 lg:p-8 space-y-6">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-5">
                        <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.828a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        </div>
                        <h2 class="text-sm font-black text-slate-800 uppercase tracking-widest">Vincular Documento Origen</h2>
                    </div>

                    @if($reception_type === 'purchase')
                        <div class="space-y-3">
                            <label class="text-[10px] font-bold text-slate-400 uppercase">Orden de Compra Asociada</label>
                            <select wire:model.live="purchase_order_id"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-teal-500/20">
                                <option value="">— Carga manual / Sin OC —</option>
                                @foreach($purchaseOrders as $po)
                                    <option value="{{ $po->id }}">{{ $po->folio }} ({{ $po->supplier?->name }})</option>
                                @endforeach
                            </select>
                            <p class="text-[10px] text-teal-600 font-bold uppercase tracking-wider pl-1">Ahorra tiempo vinculando el pedido original</p>
                        </div>
                    @elseif($reception_type === 'return')
                        <div class="space-y-3">
                            <label class="text-[10px] font-bold text-slate-400 uppercase">Entrega de Venta Original</label>
                            <select wire:model.live="sale_delivery_id"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-teal-500/20">
                                <option value="">— Devolución libre —</option>
                                @foreach($saleDeliveries as $del)
                                    <option value="{{ $del->id }}">{{ $del->folio }} ({{ $del->customer?->name }})</option>
                                @endforeach
                            </select>
                        </div>
                    @elseif($reception_type === 'transfer')
                        <div class="space-y-3">
                            <label class="text-[10px] font-bold text-slate-400 uppercase">Traspaso en Tránsito</label>
                            <select wire:model.live="origin_movement_id"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-teal-500/20">
                                <option value="">— Traspaso directo —</option>
                                @foreach($pendingTransfers as $tm)
                                    <option value="{{ $tm->id }}">{{ $tm->folio }} (De: {{ $tm->warehouse?->name }})</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- ── SECCIÓN: PRODUCTOS ─────────────────────────────────────────── --}}
            <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                <div class="p-6 lg:p-8 space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-slate-800">Partidas Recibidas</h2>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Detalle físico de lo que está llegando</p>
                            </div>
                        </div>
                        <livewire:shared.product-picker :multi-select="true" />
                    </div>

                    {{-- Buscador Rápido --}}
                    <div class="relative group">
                        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-focus-within:text-teal-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input wire:model.live.debounce.250ms="productSearch" type="text"
                            placeholder="Agregar producto extra al conteo..."
                            class="w-full pl-10 pr-4 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-teal-500/20 transition-all">
                        
                        @if(count($productResults) > 0)
                            <div class="absolute top-full left-0 right-0 bg-white border border-slate-200 rounded-2xl shadow-2xl z-40 mt-2 overflow-hidden animate-in fade-in zoom-in-95">
                                @foreach($productResults as $result)
                                    <button type="button" wire:click="addProduct({{ $result['id'] }})"
                                        class="w-full text-left px-5 py-4 hover:bg-slate-50 transition flex items-center justify-between border-b border-slate-50 last:border-0">
                                        <div>
                                            <p class="text-sm font-bold text-slate-800">{{ $result['name'] }}</p>
                                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">SKU: {{ $result['sku'] ?? '—' }}</p>
                                        </div>
                                        <p class="text-[10px] text-teal-500 font-black uppercase tracking-widest">+ Recibir</p>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    @if(count($items) > 0)
                        <div class="bg-slate-50 rounded-2xl border border-slate-200/50 overflow-hidden shadow-inner">
                            <div class="overflow-x-auto overflow-visible">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="text-left border-b border-slate-200/60">
                                            <th class="px-5 py-4 w-12 text-center">
                                                <svg class="w-4 h-4 text-slate-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            </th>
                                            <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Producto</th>
                                            <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-28 text-center">Cant. Recibida</th>
                                            @if(!in_array($reception_type, ['defective', 'transfer']))
                                                @can('view prices')
                                                    <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-32">Costo Act.</th>
                                                    <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-24">Margen %</th>
                                                    <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-32 text-right">Venta Sugerida</th>
                                                @endcan
                                            @endif
                                            @if($reception_type === 'purchase')
                                                <th class="px-5 py-4 text-[10px] font-bold text-rose-400 uppercase tracking-widest w-24">Rechazo</th>
                                            @endif
                                            <th class="w-12"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200/40 bg-white">
                                        @php
                                                $_totalMercCost = collect($items)->sum(fn($i) => (float)($i['purchase_price'] ?? 0) * (float)($i['quantity'] ?? 0));
                                            @endphp
                                    @foreach($items as $index => $item)
                                            @php
                                                $received    = $item['received'] ?? true;
                                                $cost        = (float)($item['purchase_price'] ?? 0);
                                                $qty         = (float)($item['quantity'] ?? 1);
                                                $margin      = (float)($item['profit_margin'] ?? 0);
                                                $_itemCost   = $cost * $qty;
                                                $_opShare    = $_totalMercCost > 0 ? ($_itemCost / $_totalMercCost) * (float)$operating_expenses : 0;
                                                $_landed     = $cost + ($qty > 0 ? $_opShare / $qty : 0);
                                                $_div        = 1 - $margin / 100;
                                                $salePrice   = $_div > 0 ? round($_landed / $_div, 2) : 0;
                                            @endphp
                                            <tr class="{{ $received ? "hover:bg-slate-50/50" : "opacity-40 grayscale" }} transition-all">
                                                <td class="px-5 py-3 text-center">
                                                    <label class="relative inline-flex items-center cursor-pointer">
                                                        <input type="checkbox" wire:model.live="items.{{ $index }}.received" class="sr-only peer">
                                                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-teal-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                                                    </label>
                                                </td>
                                                <td class="px-5 py-3">
                                                    <div class="flex flex-col">
                                                        <span class="font-bold text-slate-800 text-sm truncate max-w-[300px]">{{ $item['product_name'] }}</span>
                                                        <span class="text-[10px] font-mono text-slate-400 uppercase">{{ $item['sku'] ?: 'Sin SKU' }}</span>
                                                        @if(isset($item['quantity_ordered']) && $item['quantity_ordered'] > 0)
                                                            <span class="text-[9px] font-black text-indigo-500 uppercase mt-1">Pedido: {{ $item['quantity_ordered'] }}</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="px-5 py-3">
                                                    <input wire:model.live="items.{{ $index }}.quantity" type="number" step="0.01" min="0.01"
                                                        {{ !$received ? 'disabled' : '' }}
                                                        class="w-full border-none focus:ring-0 p-0 text-sm font-black text-center text-slate-900 bg-transparent">
                                                </td>
                                                @if(!in_array($reception_type, ['defective', 'transfer']))
                                                    @can('view prices')
                                                        <td class="px-5 py-3">
                                                            <div class="flex items-center gap-1">
                                                                <span class="text-xs font-bold text-slate-400">$</span>
                                                                <input wire:model.live="items.{{ $index }}.purchase_price" type="number" step="0.01" min="0"
                                                                    {{ !$received ? 'disabled' : '' }}
                                                                    class="w-full border-none focus:ring-0 p-0 text-sm font-bold text-teal-600 bg-transparent">
                                                            </div>
                                                        </td>
                                                        <td class="px-5 py-3">
                                                            <div class="flex items-center gap-1">
                                                                <input wire:model.live="items.{{ $index }}.profit_margin" type="number" step="0.01" min="0"
                                                                    {{ !$received ? 'disabled' : '' }}
                                                                    class="w-20 border-none focus:ring-0 p-0 text-sm font-bold text-slate-600 bg-transparent">
                                                                <span class="text-xs font-bold text-slate-400">%</span>
                                                            </div>
                                                        </td>
                                                        <td class="px-5 py-3 text-right">
                                                            <span class="text-sm font-black text-indigo-600">
                                                                ${{ number_format($salePrice, 2) }}
                                                            </span>
                                                        </td>
                                                    @endcan
                                                @endif
                                                @if($reception_type === 'purchase')
                                                    <td class="px-5 py-3">
                                                        <input wire:model.live="items.{{ $index }}.quantity_rejected" type="number" step="0.01" min="0"
                                                            {{ !$received ? 'disabled' : '' }}
                                                            class="w-full border-none focus:ring-0 p-0 text-sm font-bold text-rose-500 bg-transparent placeholder-rose-200" placeholder="0">
                                                    </td>
                                                @endif
                                                <td class="px-5 py-3 text-center">
                                                    <button type="button" wire:click="removeItem({{ $index }})"
                                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-300 hover:text-rose-600 hover:bg-rose-50 transition-all">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            {{-- Resumen de Conteo --}}
                            @php
                                $receivedCount = collect($items)->where('received', true)->count();
                                $totalCount    = count($items);
                            @endphp
                            <div class="bg-slate-100/50 border-t border-slate-200/60 p-4 px-6 flex justify-between items-center">
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Progreso del Conteo:</span>
                                <div class="flex items-center gap-3">
                                    <span class="text-sm font-black {{ $receivedCount === $totalCount ? 'text-teal-600' : 'text-amber-500' }}">
                                        {{ $receivedCount }} / {{ $totalCount }} productos
                                    </span>
                                    @if($receivedCount < $totalCount)
                                        <span class="inline-flex px-2 py-0.5 rounded-lg bg-amber-100 text-amber-700 text-[9px] font-black uppercase tracking-wider animate-pulse">Pendientes</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="p-12 text-center bg-slate-50 rounded-[2rem] border-2 border-dashed border-slate-200">
                            <div class="w-16 h-16 rounded-full bg-white flex items-center justify-center text-slate-200 mx-auto mb-4 shadow-sm">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                            </div>
                            <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">Esperando selección de documento origen</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ── MODAL DE CONFIRMACIÓN (ESTILO INVENTARIOS) ──────────────────── --}}
    @if($showConfirmModal)
    <div class="fixed inset-0 z-[60] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" wire:click="cancelConfirm"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200">
                <div class="bg-white p-8 lg:p-10 space-y-8">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-teal-50 flex items-center justify-center text-teal-600 shadow-sm border border-teal-100">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-black text-slate-800 tracking-tight">Confirmar Entrada</h3>
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Revisa los productos antes de afectar stock</p>
                        </div>
                    </div>

                    {{-- Lista de productos --}}
                    <div class="bg-slate-50 rounded-3xl border border-slate-100 overflow-hidden shadow-inner">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-100/50 border-b border-slate-200/50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Producto</th>
                                    <th class="px-6 py-4 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Cant.</th>
                                    @if($reception_type === 'purchase')
                                        <th class="px-6 py-4 text-right text-[10px] font-black text-rose-400 uppercase tracking-widest">Rech.</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach($items as $item)
                                    @if($item['received'] ?? true)
                                        <tr>
                                            <td class="px-6 py-4">
                                                <p class="font-bold text-slate-800">{{ $item['product_name'] }}</p>
                                                <p class="text-[10px] text-slate-400 font-mono">{{ $item['sku'] }}</p>
                                            </td>
                                            <td class="px-6 py-4 text-right font-black text-teal-600 text-lg">
                                                {{ $item['quantity'] }}
                                            </td>
                                            @if($reception_type === 'purchase')
                                                <td class="px-6 py-4 text-right font-bold text-rose-500">
                                                    {{ $item['quantity_rejected'] > 0 ? $item['quantity_rejected'] : '—' }}
                                                </td>
                                            @endif
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Warnings (Price changes) --}}
                    @if(!empty($priceWarnings))
                        <div class="p-6 bg-amber-50 rounded-3xl border border-amber-100 space-y-4">
                            <div class="flex items-center gap-2 text-amber-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                                <span class="text-sm font-black uppercase tracking-wider">Cambios de Precios Detectados</span>
                            </div>
                            <div class="grid grid-cols-1 gap-2">
                                @foreach($priceWarnings as $w)
                                    <div class="flex items-center justify-between text-[11px] font-bold">
                                        <span class="text-amber-800">{{ $w['name'] }}</span>
                                        <div class="flex items-center gap-2">
                                            <span class="text-slate-400 line-through">${{ number_format($w['prev'], 2) }}</span>
                                            <span class="px-2 py-0.5 rounded-lg {{ $w['increase'] ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                                                ${{ number_format($w['new'], 2) }} ({{ $w['increase'] ? '+' : '-' }}{{ $w['pct'] }}%)
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-4">
                        <button type="button" wire:click="cancelConfirm"
                            class="py-4 px-6 bg-slate-50 text-slate-500 rounded-2xl text-sm font-black uppercase tracking-widest hover:bg-slate-100 transition-all">
                            Revisar Datos
                        </button>
                        <button type="button" wire:click="save" wire:loading.attr="disabled"
                            class="py-4 px-6 bg-teal-600 text-white rounded-2xl text-sm font-black uppercase tracking-widest shadow-lg shadow-teal-500/25 hover:shadow-teal-500/40 hover:scale-[1.02] active:scale-[0.98] transition-all">
                            <span wire:loading.remove wire:target="save">Confirmar Entrada</span>
                            <span wire:loading wire:target="save" class="animate-pulse">Efectuando Stock...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

