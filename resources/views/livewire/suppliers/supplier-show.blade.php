<div>
    <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-6">
        <div class="flex items-center gap-3 flex-1">
            <a wire:navigate href="{{ route('suppliers.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            @if($supplier->image)
                <img src="{{ Storage::url($supplier->image) }}"
                    class="w-14 h-14 rounded-full object-cover border border-gray-200">
            @else
                <div class="w-14 h-14 rounded-full bg-teal-100 flex items-center justify-center text-teal-600 font-semibold text-xl flex-shrink-0">
                    {{ strtoupper(substr($supplier->name, 0, 2)) }}
                </div>
            @endif
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-xl font-medium text-gray-900">{{ $supplier->name }}</h1>
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ $supplier->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ \App\Models\Supplier::STATUS[$supplier->status] }}
                    </span>
                    @php $avgScore = $this->kpis['avgScore'] @endphp
                    @if($avgScore)
                        <span class="text-xs px-2 py-0.5 rounded-full font-semibold
                            {{ $avgScore >= 4 ? 'bg-emerald-50 text-emerald-700' : ($avgScore >= 3 ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-600') }}">
                            ★ {{ number_format($avgScore, 1) }}/5
                        </span>
                    @endif
                </div>
                <p class="text-sm text-gray-500">
                    {{ $supplier->type === 'company' ? 'Empresa' : 'Persona física' }}
                    @if($supplier->rfc) · RFC: {{ $supplier->rfc }} @endif
                    @if($supplier->supplier_category && isset(\App\Models\Supplier::CATEGORIES[$supplier->supplier_category]))
                        · {{ \App\Models\Supplier::CATEGORIES[$supplier->supplier_category] }}
                    @endif
                </p>
            </div>
        </div>
        <a wire:navigate href="{{ route('suppliers.edit', $supplier) }}"
            class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition self-start sm:self-auto">
            Editar
        </a>
    </div>

    <x-alert />

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">

        {{-- ── Columna lateral ─────────────────────────────────────────── --}}
        <div class="space-y-4">

            {{-- KPIs resumen --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Resumen</h2>
                <div class="grid grid-cols-2 gap-3">
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <p class="text-lg font-bold text-gray-900">{{ $this->kpis['totalOrders'] }}</p>
                        <p class="text-xs text-gray-500">Órdenes</p>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <p class="text-lg font-bold text-teal-700">${{ number_format($this->kpis['totalSpent'], 0) }}</p>
                        <p class="text-xs text-gray-500">Total comprado</p>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <p class="text-lg font-bold text-indigo-700">{{ $this->kpis['completionRate'] }}%</p>
                        <p class="text-xs text-gray-500">Cumplimiento</p>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        @if($this->kpis['avgScore'])
                            <p class="text-lg font-bold {{ $this->kpis['avgScore'] >= 4 ? 'text-emerald-600' : ($this->kpis['avgScore'] >= 3 ? 'text-amber-600' : 'text-red-600') }}">
                                ★ {{ number_format($this->kpis['avgScore'], 1) }}
                            </p>
                        @else
                            <p class="text-lg font-bold text-gray-300">—</p>
                        @endif
                        <p class="text-xs text-gray-500">Score prom.</p>
                    </div>
                </div>

                @if($this->kpis['avgScore'])
                <div class="mt-4 space-y-1.5">
                    @foreach(['avgPrice' => 'Precio', 'avgQuality' => 'Calidad', 'avgDelivery' => 'Entrega', 'avgCompliance' => 'Cumplimiento'] as $key => $label)
                        @php $val = $this->kpis[$key] ?? 0; @endphp
                        <div class="flex items-center gap-2 text-xs">
                            <span class="w-24 text-gray-500 flex-shrink-0">{{ $label }}</span>
                            <div class="flex-1 bg-gray-100 rounded-full h-1.5">
                                <div class="h-1.5 rounded-full {{ $val >= 4 ? 'bg-emerald-500' : ($val >= 3 ? 'bg-amber-400' : 'bg-red-400') }}"
                                    style="width: {{ ($val / 5) * 100 }}%"></div>
                            </div>
                            <span class="w-6 text-right font-medium text-gray-700">{{ $val }}</span>
                        </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Info de contacto --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Información</h2>
                <div class="space-y-2 text-sm">
                    @foreach($supplier->phones as $phone)
                        <div class="flex items-center gap-2 text-gray-700">
                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            {{ $phone->number }}
                            @if($phone->is_primary) <span class="text-xs text-indigo-500">principal</span> @endif
                        </div>
                    @endforeach
                    @foreach($supplier->emails as $email)
                        <div class="flex items-center gap-2 text-gray-700">
                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            {{ $email->email }}
                            @if($email->is_primary) <span class="text-xs text-indigo-500">principal</span> @endif
                        </div>
                    @endforeach
                    @if($supplier->address)
                        <div class="flex items-start gap-2 text-gray-700">
                            <svg class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            {{ $supplier->address }}, {{ $supplier->city }}, {{ $supplier->state }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Cuentas bancarias --}}
            @if($supplier->bankAccounts->count())
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <h2 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Cuentas bancarias</h2>
                    <div class="space-y-3">
                        @foreach($supplier->bankAccounts as $account)
                            <div class="border border-gray-100 rounded-lg p-3 text-sm space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-gray-900">{{ $account->bank_name }}</span>
                                    @if($account->is_primary)
                                        <span class="text-xs text-indigo-500">Principal</span>
                                    @endif
                                </div>
                                @if($account->beneficiary)
                                    <p class="text-xs text-gray-500">{{ $account->beneficiary }}</p>
                                @endif
                                @if($account->account_number)
                                    <p class="text-xs text-gray-600 font-mono">Cuenta: {{ $account->account_number }}</p>
                                @endif
                                @if($account->clabe)
                                    <p class="text-xs text-gray-600 font-mono">CLABE: {{ $account->clabe }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Condiciones --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Condiciones</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Crédito otorgado</span>
                        <span class="font-medium">${{ number_format($supplier->credit_limit, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Días de crédito</span>
                        <span class="font-medium">{{ $supplier->payment_terms == 0 ? 'Contado' : $supplier->payment_terms . ' días' }}</span>
                    </div>
                    @if($supplier->assignedTo)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Responsable</span>
                            <span class="font-medium">{{ $supplier->assignedTo->name }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Contenido principal con tabs ────────────────────────────── --}}
        <div class="md:col-span-1 lg:col-span-2 space-y-4">

            {{-- Tabs --}}
            <div class="flex gap-1 bg-gray-100 p-1 rounded-lg w-fit overflow-x-auto">
                @foreach([
                    'contacts'    => 'Contactos (' . $supplier->contacts->count() . ')',
                    'notes'       => 'Interacciones (' . $supplier->notes->count() . ')',
                    'history'     => 'Historial (' . $this->kpis['totalOrders'] . ')',
                    'evaluations' => 'Evaluaciones (' . $this->evaluations->count() . ')',
                ] as $tab => $label)
                    <button wire:click="$set('activeTab', '{{ $tab }}')"
                        class="px-4 py-1.5 text-sm rounded-md transition whitespace-nowrap
                            {{ $activeTab === $tab ? 'bg-white text-gray-900 shadow-sm font-medium' : 'text-gray-500 hover:text-gray-700' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            {{-- ─── TAB: Contactos ─────────────────────────────────────── --}}
            @if($activeTab === 'contacts')
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-sm font-medium text-gray-700">Contactos vinculados</h2>
                        @if($supplier->type === 'company')
                            <button wire:click="$set('showContactForm', true)"
                                class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">+ Agregar contacto</button>
                        @endif
                    </div>

                    @if($showContactForm)
                        <div class="border border-indigo-100 bg-indigo-50/30 rounded-lg p-4 mb-4 space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Nombre *</label>
                                    <input wire:model="contactFirstName" type="text"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                    @error('contactFirstName') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Apellido</label>
                                    <input wire:model="contactLastName" type="text"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Cargo</label>
                                    <input wire:model="contactPosition" type="text"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Teléfono</label>
                                    <input wire:model="contactPhone" type="text"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs text-gray-500 mb-1">Correo</label>
                                    <input wire:model="contactEmail" type="email"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                </div>
                            </div>
                            <div class="flex justify-end gap-2">
                                <button type="button" wire:click="$set('showContactForm', false)"
                                    class="px-3 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-gray-50">Cancelar</button>
                                <button type="button" wire:click="saveContact"
                                    class="px-3 py-1.5 text-xs bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Guardar</button>
                            </div>
                        </div>
                    @endif

                    @forelse($supplier->contacts as $contact)
                        <div class="flex items-center gap-3 py-3 border-b border-gray-100 last:border-0">
                            <div class="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 font-medium text-xs flex-shrink-0">
                                {{ strtoupper(substr($contact->first_name, 0, 1) . substr($contact->last_name ?? '', 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">{{ $contact->full_name }}</p>
                                <p class="text-xs text-gray-400">
                                    {{ $contact->position ?? '' }}
                                    {{ $contact->phone ? '· ' . $contact->phone : '' }}
                                </p>
                            </div>
                            @if($contact->is_primary)
                                <span class="text-xs text-indigo-500 flex-shrink-0">Principal</span>
                            @endif
                            <button wire:click="deleteContact({{ $contact->id }})" class="text-red-400 hover:text-red-600 flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 text-center py-6">No hay contactos vinculados.</p>
                    @endforelse
                </div>
            @endif

            {{-- ─── TAB: Interacciones / Notas ─────────────────────────── --}}
            @if($activeTab === 'notes')
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-sm font-medium text-gray-700">Historial de interacciones</h2>
                        <button wire:click="$set('showNoteForm', true)"
                            class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">+ Agregar</button>
                    </div>

                    @if($showNoteForm)
                        <div class="border border-indigo-100 bg-indigo-50/30 rounded-lg p-4 mb-4 space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Tipo</label>
                                    <select wire:model="noteType"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                        <option value="note">Nota</option>
                                        <option value="call">Llamada</option>
                                        <option value="email">Correo</option>
                                        <option value="meeting">Reunión</option>
                                        <option value="task">Tarea</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Título *</label>
                                    <input wire:model="noteTitle" type="text"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                    @error('noteTitle') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs text-gray-500 mb-1">Descripción</label>
                                    <textarea wire:model="noteBody" rows="3"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"></textarea>
                                </div>
                            </div>
                            <div class="flex justify-end gap-2">
                                <button type="button" wire:click="$set('showNoteForm', false)"
                                    class="px-3 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-gray-50">Cancelar</button>
                                <button type="button" wire:click="saveNote"
                                    class="px-3 py-1.5 text-xs bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Guardar</button>
                            </div>
                        </div>
                    @endif

                    @php $noteIcons = ['note' => '📝', 'call' => '📞', 'email' => '✉️', 'meeting' => '🤝', 'task' => '✅']; @endphp
                    @forelse($supplier->notes as $note)
                        <div class="flex gap-3 py-3 border-b border-gray-100 last:border-0">
                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-sm flex-shrink-0">
                                {{ $noteIcons[$note->type] ?? '📝' }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $note->title }}</p>
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <span class="text-xs text-gray-400">{{ $note->noted_at->format('d/m/Y H:i') }}</span>
                                        <button wire:click="deleteNote({{ $note->id }})" class="text-red-400 hover:text-red-600">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                @if($note->body)
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $note->body }}</p>
                                @endif
                                <p class="text-xs text-gray-400 mt-1">{{ $note->user->name }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 text-center py-6">No hay interacciones registradas.</p>
                    @endforelse
                </div>
            @endif

            {{-- ─── TAB: Historial de compras ───────────────────────────── --}}
            @if($activeTab === 'history')
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <h2 class="text-sm font-medium text-gray-700 mb-4">Historial de órdenes de compra</h2>

                    @if($this->purchaseOrders->count())
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm min-w-[560px]">
                                <thead>
                                    <tr class="bg-gray-50 border-b border-gray-100">
                                        <th class="text-left px-3 py-2.5 text-xs font-medium text-gray-500">Folio</th>
                                        <th class="text-left px-3 py-2.5 text-xs font-medium text-gray-500">Fecha</th>
                                        <th class="text-left px-3 py-2.5 text-xs font-medium text-gray-500">Ítems</th>
                                        <th class="text-left px-3 py-2.5 text-xs font-medium text-gray-500">Estatus</th>
                                        <th class="text-right px-3 py-2.5 text-xs font-medium text-gray-500">Total</th>
                                        <th class="w-8"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($this->purchaseOrders as $order)
                                        @php
                                            $statusColors = [
                                                'draft'            => 'bg-gray-100 text-gray-600',
                                                'sent'             => 'bg-blue-50 text-blue-700',
                                                'confirmed'        => 'bg-indigo-50 text-indigo-700',
                                                'partial_received' => 'bg-amber-50 text-amber-700',
                                                'received'         => 'bg-teal-50 text-teal-700',
                                                'invoiced'         => 'bg-emerald-50 text-emerald-700',
                                                'cancelled'        => 'bg-red-50 text-red-600',
                                            ];
                                            $statusLabels = [
                                                'draft'            => 'Borrador',
                                                'sent'             => 'Enviada',
                                                'confirmed'        => 'Confirmada',
                                                'partial_received' => 'Recep. parcial',
                                                'received'         => 'Recibida',
                                                'invoiced'         => 'Facturada',
                                                'cancelled'        => 'Cancelada',
                                            ];
                                        @endphp
                                        <tr class="hover:bg-gray-50/50 transition">
                                            <td class="px-3 py-2.5 font-medium text-gray-900">{{ $order->folio }}</td>
                                            <td class="px-3 py-2.5 text-gray-500">{{ $order->created_at->format('d/m/Y') }}</td>
                                            <td class="px-3 py-2.5 text-gray-500">{{ $order->items->count() }} línea(s)</td>
                                            <td class="px-3 py-2.5">
                                                <span class="text-xs px-2 py-0.5 rounded-full {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-600' }}">
                                                    {{ $statusLabels[$order->status] ?? $order->status }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-2.5 text-right font-medium text-gray-900">
                                                ${{ number_format($order->total, 2) }}
                                            </td>
                                            <td class="px-3 py-2.5 text-right">
                                                <a wire:navigate href="{{ route('purchases.orders.show', $order) }}"
                                                    class="text-indigo-400 hover:text-indigo-600">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                    </svg>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="border-t-2 border-gray-200">
                                    <tr>
                                        <td colspan="4" class="px-3 py-2.5 text-xs font-medium text-gray-500">
                                            {{ $this->kpis['completedOrders'] }} de {{ $this->kpis['totalOrders'] }} órdenes completadas
                                        </td>
                                        <td class="px-3 py-2.5 text-right font-bold text-gray-900">
                                            ${{ number_format($this->kpis['totalSpent'], 2) }}
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="w-10 h-10 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-sm text-gray-400">No hay órdenes de compra con este proveedor.</p>
                        </div>
                    @endif
                </div>
            @endif

            {{-- ─── TAB: Evaluaciones ───────────────────────────────────── --}}
            @if($activeTab === 'evaluations')
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-sm font-medium text-gray-700">Evaluaciones del proveedor</h2>
                        <button wire:click="$set('showEvalForm', true)"
                            class="text-xs bg-teal-600 hover:bg-teal-700 text-white px-3 py-1.5 rounded-lg font-medium transition">
                            + Nueva evaluación
                        </button>
                    </div>

                    {{-- Formulario nueva evaluación --}}
                    @if($showEvalForm)
                    <div class="border border-teal-100 bg-teal-50/30 rounded-xl p-5 mb-5 space-y-4">
                        <p class="text-sm font-medium text-gray-700">Registrar evaluación</p>

                        {{-- Estrellas por dimensión --}}
                        @foreach([
                            'evalPrice'      => 'Competitividad de precio',
                            'evalQuality'    => 'Calidad del producto / servicio',
                            'evalDelivery'   => 'Tiempo de entrega',
                            'evalCompliance' => 'Cumplimiento y documentación',
                        ] as $field => $label)
                            <div>
                                <label class="block text-xs text-gray-500 mb-1.5">{{ $label }}</label>
                                <div class="flex gap-2">
                                    @for($s = 1; $s <= 5; $s++)
                                        <button type="button"
                                            wire:click="$set('{{ $field }}', {{ $s }})"
                                            class="w-9 h-9 rounded-lg border text-sm font-bold transition
                                                {{ $$field >= $s
                                                    ? 'bg-amber-400 border-amber-400 text-white'
                                                    : 'border-gray-200 text-gray-300 hover:border-amber-300 hover:text-amber-400' }}">
                                            {{ $s }}
                                        </button>
                                    @endfor
                                    <span class="ml-2 text-xs text-gray-400 self-center">
                                        {{ \App\Models\SupplierEvaluation::SCORE_LABELS[$$field] ?? '' }}
                                    </span>
                                </div>
                            </div>
                        @endforeach

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Orden relacionada (opcional)</label>
                                <select wire:model="evalOrderId"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-300">
                                    <option value="">— Sin orden específica —</option>
                                    @foreach($this->availableOrders as $ord)
                                        <option value="{{ $ord->id }}">
                                            {{ $ord->folio }} — ${{ number_format($ord->total, 0) }} ({{ $ord->created_at->format('d/m/Y') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Fecha de evaluación *</label>
                                <input wire:model="evalDate" type="date"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-300">
                                @error('evalDate') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs text-gray-500 mb-1">Observaciones</label>
                                <textarea wire:model="evalNotes" rows="2"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-300"
                                    placeholder="Comentarios adicionales sobre el desempeño..."></textarea>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-1">
                            <div class="text-sm text-gray-500">
                                Score global: <span class="font-bold text-gray-900">
                                    {{ number_format(($evalPrice + $evalQuality + $evalDelivery + $evalCompliance) / 4, 2) }} / 5
                                </span>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" wire:click="$set('showEvalForm', false)"
                                    class="px-3 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-gray-50">Cancelar</button>
                                <button type="button" wire:click="saveEvaluation"
                                    class="px-4 py-1.5 text-xs bg-teal-600 hover:bg-teal-700 text-white rounded-lg font-medium">
                                    Guardar evaluación
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Lista de evaluaciones --}}
                    @forelse($this->evaluations as $eval)
                        <div class="border border-gray-100 rounded-xl p-4 mb-3 last:mb-0">
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-base font-bold
                                            {{ $eval->score_overall >= 4 ? 'text-emerald-600' : ($eval->score_overall >= 3 ? 'text-amber-600' : 'text-red-600') }}">
                                            ★ {{ number_format($eval->score_overall, 1) }}/5
                                        </span>
                                        <span class="text-xs text-gray-400">
                                            {{ $eval->evaluated_at->format('d/m/Y') }}
                                            — {{ $eval->evaluatedBy->name }}
                                        </span>
                                        @if($eval->purchaseOrder)
                                            <a wire:navigate href="{{ route('purchases.orders.show', $eval->purchaseOrder) }}"
                                                class="text-xs text-indigo-500 hover:underline">
                                                OC: {{ $eval->purchaseOrder->folio }}
                                            </a>
                                        @endif
                                    </div>
                                    @if($eval->notes)
                                        <p class="text-xs text-gray-500 mt-1">{{ $eval->notes }}</p>
                                    @endif
                                </div>
                                <button wire:click="deleteEvaluation({{ $eval->id }})"
                                    wire:confirm="¿Eliminar esta evaluación?"
                                    class="text-red-300 hover:text-red-500 flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                @foreach(\App\Models\SupplierEvaluation::DIMENSIONS as $field => $dimLabel)
                                    @php $score = $eval->$field; @endphp
                                    <div class="text-center p-2 bg-gray-50 rounded-lg">
                                        <p class="text-sm font-bold {{ \App\Models\SupplierEvaluation::SCORE_COLORS[$score] ?? 'text-gray-600' }}">
                                            {{ $score }}/5
                                        </p>
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $dimLabel }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <svg class="w-10 h-10 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                            <p class="text-sm text-gray-400">Aún no hay evaluaciones para este proveedor.</p>
                            <button wire:click="$set('showEvalForm', true)"
                                class="mt-3 text-xs text-teal-600 hover:underline">Registrar primera evaluación</button>
                        </div>
                    @endforelse
                </div>
            @endif

        </div>
    </div>
</div>
