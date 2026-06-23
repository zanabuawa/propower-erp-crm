<div>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-6">
        <div class="flex items-center gap-3 flex-1">
            <a wire:navigate href="{{ route('contacts.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            @if($customer->image)
                <img src="{{ Storage::url($customer->image) }}" class="w-14 h-14 rounded-full object-cover border border-gray-200">
            @else
                <div class="w-14 h-14 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-semibold text-xl flex-shrink-0">
                    {{ strtoupper(substr($customer->name, 0, 2)) }}
                </div>
            @endif
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-xl font-medium text-gray-900">{{ $customer->name }}</h1>
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ $customer->status === 'active' ? 'bg-emerald-50 text-emerald-700' :
                           ($customer->status === 'prospect' ? 'bg-amber-50 text-amber-700' : 'bg-gray-100 text-gray-500') }}">
                        {{ \App\Models\Customer::STATUS[$customer->status] }}
                    </span>
                </div>
                <p class="text-sm text-gray-500">
                    Empresa
                    @if($customer->rfc) · RFC: {{ $customer->rfc }} @endif
                </p>
            </div>
        </div>
        <a wire:navigate href="{{ route('contacts.edit', $customer) }}"
            class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition self-start sm:self-auto shadow-sm">
            Editar cliente
        </a>
    </div>

    <x-alert />

    <div class="grid grid-cols-1 lg:grid-cols-3 xl:grid-cols-4 gap-5 lg:gap-6">

        {{-- Columna izquierda: datos --}}
        <div class="space-y-4 lg:space-y-6">

            {{-- Info general --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Información</h2>
                <div class="space-y-2 text-sm">
                    @if($customer->phones->count())
                        @foreach($customer->phones as $phone)
                            <div class="flex items-center gap-2 text-gray-700">
                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                {{ $phone->number }}
                                @if($phone->is_primary) <span class="text-xs text-indigo-500">principal</span> @endif
                            </div>
                        @endforeach
                    @endif
                    @if($customer->emails->count())
                        @foreach($customer->emails as $email)
                            <div class="flex items-center gap-2 text-gray-700">
                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                {{ $email->email }}
                                @if($email->is_primary) <span class="text-xs text-indigo-500">principal</span> @endif
                            </div>
                        @endforeach
                    @endif
                    @if($customer->address)
                        <div class="flex items-start gap-2 text-gray-700">
                            <svg class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>{{ $customer->address }}, {{ $customer->city }}, {{ $customer->state }}</span>
                        </div>
                    @endif
                    @if($customer->anniversary_date)
                        <div class="flex items-center gap-2 text-gray-700">
                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                            Aniversario como cliente: {{ $customer->anniversary_date->format('d/m/Y') }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Condiciones comerciales --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Condiciones comerciales</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Límite de crédito</span>
                        <span class="font-medium">${{ number_format($customer->credit_limit, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Días de crédito</span>
                        <span class="font-medium">{{ $customer->payment_terms === 0 ? 'Contado' : $customer->payment_terms . ' días' }}</span>
                    </div>
                    @if($customer->assignedTo)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Vendedor</span>
                            <span class="font-medium">{{ $customer->assignedTo->name }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Columna derecha: tabs --}}
        <div class="lg:col-span-2 xl:col-span-3 space-y-4 lg:space-y-6">

            {{-- Tabs --}}
            <div class="flex flex-wrap gap-1 bg-gray-100/80 p-1 rounded-xl w-fit shadow-sm border border-gray-200/50">
                <button wire:click="$set('activeTab', 'contacts')"
                    class="px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ $activeTab === 'contacts' ? 'bg-white text-indigo-600 shadow-md ring-1 ring-black/5' : 'text-gray-500 hover:text-gray-700 hover:bg-white/50' }}">
                    Contactos ({{ $customer->contacts->count() }})
                </button>
                <button wire:click="$set('activeTab', 'notes')"
                    class="px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ $activeTab === 'notes' ? 'bg-white text-indigo-600 shadow-md ring-1 ring-black/5' : 'text-gray-500 hover:text-gray-700 hover:bg-white/50' }}">
                    Interacciones ({{ $customer->notes->count() }})
                </button>
                <button wire:click="$set('activeTab', 'history')"
                    class="px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ $activeTab === 'history' ? 'bg-white text-indigo-600 shadow-md ring-1 ring-black/5' : 'text-gray-500 hover:text-gray-700 hover:bg-white/50' }}">
                    Historial comercial
                </button>
                <button wire:click="$set('activeTab', 'activities')"
                    class="px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ $activeTab === 'activities' ? 'bg-white text-indigo-600 shadow-md ring-1 ring-black/5' : 'text-gray-500 hover:text-gray-700 hover:bg-white/50' }}">
                    Agenda CRM
                </button>
                <button wire:click="$set('activeTab', 'segment')"
                    class="px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ $activeTab === 'segment' ? 'bg-white text-indigo-600 shadow-md ring-1 ring-black/5' : 'text-gray-500 hover:text-gray-700 hover:bg-white/50' }}">
                    Segmentación
                </button>
            </div>

            {{-- Tab Contactos --}}
            @if($activeTab === 'contacts')
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-sm font-medium text-gray-700">Contactos vinculados</h2>
                        <button wire:click="$set('showContactForm', true)"
                            class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">+ Agregar contacto</button>
                    </div>

                    @if($showContactForm)
                        <div class="border border-indigo-100 bg-indigo-50/30 rounded-lg p-4 mb-4 space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="sm:col-span-1">
                                    <label class="block text-xs text-gray-500 mb-1">Nombre(s) *</label>
                                    <input wire:model="contactFirstName" type="text"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                    @error('contactFirstName') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Alias</label>
                                    <input wire:model="contactAlias" type="text"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Apellido paterno</label>
                                    <input wire:model="contactPaternalSurname" type="text"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Apellido materno</label>
                                    <input wire:model="contactMaternalSurname" type="text"
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
                                <div class="col-span-1 sm:col-span-2">
                                    <label class="block text-xs text-gray-500 mb-1">Correo</label>
                                    <input wire:model="contactEmail" type="email"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                </div>
                                <div class="col-span-1 sm:col-span-2">
                                    <label class="block text-xs text-gray-500 mb-1">Notas / descripción</label>
                                    <textarea wire:model="contactDescription" rows="2"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 resize-none"></textarea>
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

                    @forelse($customer->contacts as $contact)
                        <div class="flex items-center gap-3 py-3 border-b border-gray-100 last:border-0">
                            <div class="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 font-medium text-xs flex-shrink-0">
                                {{ strtoupper(substr($contact->first_name, 0, 1) . substr($contact->paternal_surname ?? '', 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $contact->full_name }}
                                    @if($contact->alias)
                                        <span class="text-xs text-gray-400 font-normal">({{ $contact->alias }})</span>
                                    @endif
                                </p>
                                <p class="text-xs text-gray-400">
                                    {{ $contact->position ?? '' }}
                                    {{ $contact->phone ? '· ' . $contact->phone : '' }}
                                    {{ $contact->email ? '· ' . $contact->email : '' }}
                                </p>
                                @if($contact->description)
                                    <p class="text-xs text-gray-500 mt-1 italic">{{ $contact->description }}</p>
                                @endif
                            </div>
                            @if($contact->is_primary)
                                <span class="text-xs text-indigo-500 flex-shrink-0">Principal</span>
                            @endif
                            <button wire:click="deleteContact({{ $contact->id }})"
                                class="text-red-400 hover:text-red-600 flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 text-center py-4">No hay contactos vinculados.</p>
                    @endforelse
                </div>
            @endif

            {{-- Tab Historial --}}
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
                                <div class="col-span-1 sm:col-span-2">
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

                    @php
                        $noteIcons = [
                            'note'    => '📝',
                            'call'    => '📞',
                            'email'   => '✉️',
                            'meeting' => '🤝',
                            'task'    => '✅',
                        ];
                    @endphp

                    @forelse($customer->notes as $note)
                        <div class="flex gap-3 py-3 border-b border-gray-100 last:border-0">
                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-sm flex-shrink-0">
                                {{ $noteIcons[$note->type] ?? '📝' }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $note->title }}</p>
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <span class="text-xs text-gray-400">{{ $note->noted_at->format('d/m/Y H:i') }}</span>
                                        <button wire:click="deleteNote({{ $note->id }})"
                                            class="text-red-400 hover:text-red-600">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
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
                        <p class="text-sm text-gray-400 text-center py-4">No hay interacciones registradas.</p>
                    @endforelse
                </div>
            @endif

            {{-- Tab Historial comercial --}}
            @if($activeTab === 'history')
                {{-- Stats rápidos --}}
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
                    <div class="bg-white rounded-xl border border-gray-200 p-3 text-center">
                        <p class="text-lg font-bold text-indigo-600">${{ number_format($commercialStats['total_invoiced'], 0) }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">Total facturado</p>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-200 p-3 text-center">
                        <p class="text-lg font-bold text-gray-700">{{ $commercialStats['total_orders'] }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">Órdenes de venta</p>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-200 p-3 text-center">
                        <p class="text-lg font-bold text-gray-700">{{ $commercialStats['total_quotes'] }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">Cotizaciones</p>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-200 p-3 text-center">
                        <p class="text-lg font-bold {{ $commercialStats['pending_invoices'] > 0 ? 'text-amber-600' : 'text-gray-400' }}">{{ $commercialStats['pending_invoices'] }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">Facturas pendientes</p>
                    </div>
                </div>

                {{-- Cotizaciones --}}
                <div class="bg-white rounded-xl border border-gray-200 p-5 mb-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Cotizaciones recientes</h3>
                    @forelse($quotations as $q)
                        <div class="flex items-center justify-between py-2.5 border-b border-gray-50 last:border-0">
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $q->folio ?? 'COT-' . $q->id }}</p>
                                <p class="text-xs text-gray-400">{{ $q->created_at->format('d/m/Y') }}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <p class="text-sm font-medium text-gray-700">${{ number_format($q->total ?? 0, 0) }}</p>
                                <span class="inline-flex px-2 py-0.5 text-xs rounded-lg border bg-gray-100 text-gray-600 border-gray-200">
                                    {{ $q->status ?? '—' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 text-center py-3">Sin cotizaciones.</p>
                    @endforelse
                </div>

                {{-- Órdenes --}}
                <div class="bg-white rounded-xl border border-gray-200 p-5 mb-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Órdenes de venta recientes</h3>
                    @forelse($orders as $o)
                        <div class="flex items-center justify-between py-2.5 border-b border-gray-50 last:border-0">
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $o->folio ?? 'OV-' . $o->id }}</p>
                                <p class="text-xs text-gray-400">{{ $o->created_at->format('d/m/Y') }}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <p class="text-sm font-medium text-gray-700">${{ number_format($o->total ?? 0, 0) }}</p>
                                <span class="inline-flex px-2 py-0.5 text-xs rounded-lg border bg-gray-100 text-gray-600 border-gray-200">
                                    {{ $o->status ?? '—' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 text-center py-3">Sin órdenes de venta.</p>
                    @endforelse
                </div>

                {{-- Facturas --}}
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Facturas recientes</h3>
                    @forelse($invoices as $inv)
                        <div class="flex items-center justify-between py-2.5 border-b border-gray-50 last:border-0">
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $inv->folio ?? 'FAC-' . $inv->id }}</p>
                                <p class="text-xs text-gray-400">{{ $inv->created_at->format('d/m/Y') }}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <p class="text-sm font-medium text-gray-700">${{ number_format($inv->total ?? 0, 0) }}</p>
                                <span class="inline-flex px-2 py-0.5 text-xs rounded-lg border bg-gray-100 text-gray-600 border-gray-200">
                                    {{ $inv->status ?? '—' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 text-center py-3">Sin facturas.</p>
                    @endforelse
                </div>
            @endif

            {{-- Tab Agenda CRM --}}
            @if($activeTab === 'activities')
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-700">Actividades CRM</h3>
                        <a wire:navigate href="{{ route('agenda.index') }}"
                            class="text-xs text-indigo-600 hover:underline">Ver agenda completa →</a>
                    </div>
                    <div class="space-y-3">
                        @forelse($activities as $act)
                            <div class="flex gap-3 p-3 rounded-xl {{ $act->status === 'completed' ? 'bg-gray-50 opacity-70' : ($act->isOverdue() ? 'bg-amber-50 border border-amber-100' : 'border border-gray-100') }}">
                                <div class="text-lg mt-0.5 shrink-0">{{ \App\Models\CrmActivity::TYPE_ICONS[$act->type] ?? '📋' }}</div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 {{ $act->status === 'completed' ? 'line-through' : '' }}">{{ $act->title }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        {{ \App\Models\CrmActivity::TYPES[$act->type] ?? $act->type }}
                                        · {{ $act->scheduled_at?->format('d/m/Y H:i') ?? '—' }}
                                        · <span class="inline-flex px-1.5 py-0.5 rounded text-xs {{ \App\Models\CrmActivity::STATUS_COLORS[$act->status] ?? '' }}">{{ \App\Models\CrmActivity::STATUSES[$act->status] ?? $act->status }}</span>
                                    </p>
                                    @if($act->outcome) <p class="text-xs text-gray-500 mt-0.5 italic">{{ $act->outcome }}</p> @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400 text-center py-4">Sin actividades CRM registradas.</p>
                        @endforelse
                    </div>
                </div>
            @endif

            {{-- Tab Segmentación --}}
            @if($activeTab === 'segment')
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-700">Segmentación del cliente</h3>
                        @if(!$editingSegment)
                            <button wire:click="$set('editingSegment', true)" class="text-xs text-indigo-600 hover:underline">Editar</button>
                        @endif
                    </div>

                    @if($editingSegment)
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Segmento</label>
                                    <select wire:model="segment" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                        <option value="">— Sin segmento —</option>
                                        @foreach(\App\Models\Customer::SEGMENTS as $k => $v)
                                            <option value="{{ $k }}">{{ $v }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Categoría</label>
                                    <select wire:model="customer_category" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                        <option value="">— Sin categoría —</option>
                                        @foreach(\App\Models\Customer::CATEGORIES as $k => $v)
                                            <option value="{{ $k }}">{{ $v }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Zona / Región</label>
                                    <input wire:model="zone" type="text" placeholder="Ej: Norte, CDMX, Zona Bajío"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Ingreso anual estimado ($)</label>
                                    <input wire:model="annual_revenue" type="number" step="0.01" min="0"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button wire:click="saveSegmentation" type="button"
                                    class="px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium">
                                    Guardar
                                </button>
                                <button wire:click="$set('editingSegment', false)" type="button"
                                    class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50">Cancelar</button>
                            </div>
                        </div>
                    @else
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-400">Segmento</p>
                                <p class="text-sm font-medium text-gray-800 mt-0.5">
                                    @if($customer->segment)
                                        <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100">
                                            {{ \App\Models\Customer::SEGMENTS[$customer->segment] ?? $customer->segment }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">Sin asignar</span>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">Categoría</p>
                                <p class="text-sm text-gray-700 mt-0.5">
                                    {{ \App\Models\Customer::CATEGORIES[$customer->customer_category] ?? ($customer->customer_category ? $customer->customer_category : '—') }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">Zona / Región</p>
                                <p class="text-sm text-gray-700 mt-0.5">{{ $customer->zone ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">Ingreso anual estimado</p>
                                <p class="text-sm font-medium text-gray-800 mt-0.5">
                                    {{ $customer->annual_revenue ? '$' . number_format($customer->annual_revenue, 0) : '—' }}
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

        </div>
    </div>
</div>
