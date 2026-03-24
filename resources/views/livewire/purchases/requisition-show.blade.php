<div>
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('purchases.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="flex-1">
            <div class="flex items-center gap-3">
                <h1 class="text-xl font-medium text-gray-900">{{ $requisition->folio }}</h1>
                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                    {{ \App\Models\PurchaseRequisition::STATUS_COLORS[$requisition->status] ?? '' }}">
                    {{ \App\Models\PurchaseRequisition::STATUS[$requisition->status] ?? $requisition->status }}
                </span>
            </div>
            <p class="text-sm text-gray-500">
                Solicitado por {{ $requisition->requestedBy->name }} el {{ $requisition->created_at->format('d/m/Y') }}
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Columna izquierda --}}
        <div class="space-y-4">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Información</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Moneda</span>
                        <span class="font-medium">{{ $requisition->currency }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Requerido para</span>
                        <span class="font-medium">{{ $requisition->needed_by?->format('d/m/Y') }}</span>
                    </div>
                    @if($requisition->branch)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Sucursal</span>
                            <span class="font-medium">{{ $requisition->branch->name }}</span>
                        </div>
                    @endif
                    @if($requisition->quoted_amount > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Monto cotizado</span>
                            <span class="font-medium text-indigo-700">
                                {{ $requisition->currency }} ${{ number_format($requisition->quoted_amount, 2) }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Justificación</h2>
                <p class="text-sm text-gray-700">{{ $requisition->justification }}</p>
            </div>

            {{-- Aprobaciones --}}
            @if($requisition->approvals->count() > 0)
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <h2 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Aprobaciones</h2>
                    <div class="space-y-3">
                        @foreach($requisition->approvals as $approval)
                            <div class="flex items-start gap-3">
                                <div class="w-2 h-2 rounded-full mt-1.5 flex-shrink-0
                                    {{ $approval->status === 'approved' ? 'bg-green-500' :
                                       ($approval->status === 'rejected' ? 'bg-red-500' : 'bg-amber-400') }}">
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs font-medium text-gray-900">{{ $approval->user->name }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ \App\Models\PurchaseApproval::ROLES[$approval->role] ?? $approval->role }}
                                    </p>
                                    @if($approval->comments)
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $approval->comments }}</p>
                                    @endif
                                </div>
                                <span class="text-xs {{ $approval->status === 'approved' ? 'text-green-600' :
                                    ($approval->status === 'rejected' ? 'text-red-600' : 'text-amber-600') }}">
                                    {{ $approval->status === 'approved' ? 'Aprobado' :
                                       ($approval->status === 'rejected' ? 'Rechazado' : 'Pendiente') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Columna derecha --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Productos --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100">
                    <h2 class="text-sm font-medium text-gray-700">Productos solicitados</h2>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left px-5 py-2.5 text-xs font-medium text-gray-500">Descripción</th>
                            <th class="text-left px-5 py-2.5 text-xs font-medium text-gray-500">Cant.</th>
                            <th class="text-left px-5 py-2.5 text-xs font-medium text-gray-500">Unidad</th>
                            <th class="text-left px-5 py-2.5 text-xs font-medium text-gray-500">P. estimado</th>
                            <th class="text-left px-5 py-2.5 text-xs font-medium text-gray-500">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($requisition->items as $item)
                            <tr>
                                <td class="px-5 py-3">
                                    <p class="font-medium text-gray-900">{{ $item->description }}</p>
                                    @if($item->notes)
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $item->notes }}</p>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-gray-700">{{ $item->quantity }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $item->unit ?? '—' }}</td>
                                <td class="px-5 py-3 text-gray-700">${{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-5 py-3 font-medium text-gray-900">
                                    ${{ number_format($item->subtotal, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50 border-t border-gray-100">
                            <td colspan="4" class="px-5 py-2.5 text-xs font-medium text-gray-500 text-right">Total estimado:</td>
                            <td class="px-5 py-2.5 font-medium text-gray-900">
                                ${{ number_format($requisition->total, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Respuesta de cotización (visible para compras) --}}
            @if($requisition->quote_response)
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-5">
                    <h2 class="text-sm font-medium text-blue-800 mb-2">Respuesta de compras</h2>
                    <p class="text-sm text-blue-700">{{ $requisition->quote_response }}</p>
                    <p class="text-xs text-blue-500 mt-2">
                        Monto cotizado: {{ $requisition->currency }} ${{ number_format($requisition->quoted_amount, 2) }}
                        · {{ $requisition->quoted_at?->format('d/m/Y H:i') }}
                    </p>
                </div>
            @endif

            {{-- Acciones según rol y estado --}}

            {{-- Compras: enviar cotización --}}
            @if($requisition->status === 'pending_quote' && auth()->user()->hasRole('compras'))
                <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
                    <h2 class="text-sm font-medium text-gray-700">Responder con cotización</h2>
                    <textarea wire:model="quoteResponse" rows="3"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        placeholder="Detalle de la cotización, proveedores, tiempos de entrega..."></textarea>
                    @error('quoteResponse') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                    <div class="flex items-center gap-3">
                        <div class="flex-1">
                            <label class="block text-xs text-gray-500 mb-1">Monto total cotizado *</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-sm text-gray-400">$</span>
                                <input wire:model="quotedAmount" type="number" step="0.01" min="0"
                                    class="w-full border border-gray-200 rounded-lg pl-7 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                            </div>
                            @error('quotedAmount') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <button wire:click="sendQuote"
                            class="mt-4 px-5 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
                            Enviar cotización
                        </button>
                    </div>
                </div>
            @endif

            {{-- Solicitante: aceptar o rechazar cotización --}}
            @if($requisition->status === 'quoted' && auth()->id() === $requisition->requested_by)
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <h2 class="text-sm font-medium text-gray-700 mb-3">¿Aceptas la cotización?</h2>
                    <div class="flex gap-3">
                        <button wire:click="acceptQuote"
                            class="px-5 py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition">
                            Sí, iniciar aprobación
                        </button>
                        <button wire:click="rejectQuote"
                            class="px-5 py-2 text-sm bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 rounded-lg font-medium transition">
                            Rechazar cotización
                        </button>
                    </div>
                </div>
            @endif

            {{-- Aprobadores: aprobar o rechazar --}}
            @if($requisition->status === 'pending_approval')
                @php
                    $myApproval = $requisition->approvals
                        ->where('user_id', auth()->id())
                        ->where('status', 'pending')
                        ->first();
                @endphp
                @if($myApproval)
                    <div class="bg-white rounded-xl border border-amber-200 p-5 space-y-3">
                        <h2 class="text-sm font-medium text-gray-700">Tu aprobación es requerida</h2>
                        <textarea wire:model="approvalComment" rows="2"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                            placeholder="Comentarios (opcional)"></textarea>
                        <div class="flex gap-3">
                            <button wire:click="approve"
                                class="px-5 py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition">
                                Aprobar
                            </button>
                            <button wire:click="reject"
                                class="px-5 py-2 text-sm bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 rounded-lg font-medium transition">
                                Rechazar
                            </button>
                        </div>
                    </div>
                @endif
            @endif

            {{-- Orden generada --}}
            @if($requisition->order)
                <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-green-800">Orden de compra generada</p>
                            <p class="text-xs text-green-600 mt-0.5">{{ $requisition->order->folio }}</p>
                        </div>
                        <a href="{{ route('purchases.orders.show', $requisition->order) }}"
                            class="text-xs text-green-700 hover:text-green-900 font-medium">
                            Ver orden →
                        </a>
                    </div>
                </div>
            @endif

            {{-- Botón crear orden (si está aprobada y no tiene orden) --}}
            @if($requisition->status === 'approved' && !$requisition->order && auth()->user()->hasRole('compras'))
                <div class="flex justify-end">
                    <a href="{{ route('purchases.orders.create') }}?requisition={{ $requisition->id }}"
                        class="px-5 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
                        Generar orden de compra
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>