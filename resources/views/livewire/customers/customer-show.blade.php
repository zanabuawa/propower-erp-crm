<div>
    {{-- Header --}}
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('contacts.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        @if($customer->image)
            <img src="{{ Storage::url($customer->image) }}" class="w-14 h-14 rounded-full object-cover border border-gray-200">
        @else
            <div class="w-14 h-14 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-semibold text-xl">
                {{ strtoupper(substr($customer->name, 0, 2)) }}
            </div>
        @endif
        <div class="flex-1">
            <div class="flex items-center gap-3">
                <h1 class="text-xl font-medium text-gray-900">{{ $customer->name }}</h1>
                <span class="text-xs px-2 py-0.5 rounded-full
                    {{ $customer->status === 'active' ? 'bg-green-50 text-green-700' :
                       ($customer->status === 'prospect' ? 'bg-amber-50 text-amber-700' : 'bg-gray-100 text-gray-500') }}">
                    {{ \App\Models\Customer::STATUS[$customer->status] }}
                </span>
            </div>
            <p class="text-sm text-gray-500">
                {{ $customer->type === 'company' ? 'Empresa' : 'Persona física' }}
                @if($customer->rfc) · RFC: {{ $customer->rfc }} @endif
            </p>
        </div>
        <a href="{{ route('contacts.edit', $customer) }}"
            class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition">
            Editar
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Columna izquierda: datos --}}
        <div class="space-y-4">

            {{-- Info general --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Información</h2>
                <div class="space-y-2 text-sm">
                    @if($customer->phones->count())
                        @foreach($customer->phones as $phone)
                            <div class="flex items-center gap-2 text-gray-700">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                {{ $email->email }}
                                @if($email->is_primary) <span class="text-xs text-indigo-500">principal</span> @endif
                            </div>
                        @endforeach
                    @endif
                    @if($customer->address)
                        <div class="flex items-start gap-2 text-gray-700">
                            <svg class="w-4 h-4 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>{{ $customer->address }}, {{ $customer->city }}, {{ $customer->state }}</span>
                        </div>
                    @endif
                    @if($customer->type === 'person' && $customer->birthdate)
                        <div class="flex items-center gap-2 text-gray-700">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Nacimiento: {{ $customer->birthdate->format('d/m/Y') }}
                        </div>
                    @endif
                    @if($customer->type === 'company' && $customer->anniversary_date)
                        <div class="flex items-center gap-2 text-gray-700">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                            Aniversario: {{ $customer->anniversary_date->format('d/m/Y') }}
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
        <div class="lg:col-span-2 space-y-4">

            {{-- Tabs --}}
            <div class="flex gap-1 bg-gray-100 p-1 rounded-lg w-fit">
                <button wire:click="$set('activeTab', 'contacts')"
                    class="px-4 py-1.5 text-sm rounded-md transition {{ $activeTab === 'contacts' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    Contactos ({{ $customer->contacts->count() }})
                </button>
                <button wire:click="$set('activeTab', 'notes')"
                    class="px-4 py-1.5 text-sm rounded-md transition {{ $activeTab === 'notes' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    Historial ({{ $customer->notes->count() }})
                </button>
            </div>

            {{-- Tab Contactos --}}
            @if($activeTab === 'contacts')
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-sm font-medium text-gray-700">Contactos vinculados</h2>
                        @if($customer->type === 'company')
                            <button wire:click="$set('showContactForm', true)"
                                class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">+ Agregar contacto</button>
                        @endif
                    </div>

                    @if($showContactForm)
                        <div class="border border-indigo-100 bg-indigo-50/30 rounded-lg p-4 mb-4 space-y-3">
                            <div class="grid grid-cols-2 gap-3">
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
                                <div class="col-span-2">
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

                    @forelse($customer->contacts as $contact)
                        <div class="flex items-center gap-3 py-3 border-b border-gray-100 last:border-0">
                            <div class="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 font-medium text-xs">
                                {{ strtoupper(substr($contact->first_name, 0, 1) . substr($contact->last_name ?? '', 0, 1)) }}
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ $contact->full_name }}</p>
                                <p class="text-xs text-gray-400">{{ $contact->position ?? '' }} {{ $contact->phone ? '· ' . $contact->phone : '' }}</p>
                            </div>
                            @if($contact->is_primary)
                                <span class="text-xs text-indigo-500">Principal</span>
                            @endif
                            <button wire:click="deleteContact({{ $contact->id }})"
                                class="text-red-400 hover:text-red-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 text-center py-4">
                            {{ $customer->type === 'company' ? 'No hay contactos vinculados.' : 'Los contactos aplican solo para empresas.' }}
                        </p>
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
                            <div class="grid grid-cols-2 gap-3">
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
                                <div class="col-span-2">
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
                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-sm">
                                {{ $noteIcons[$note->type] ?? '📝' }}
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-900">{{ $note->title }}</p>
                                    <div class="flex items-center gap-2">
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
        </div>
    </div>
</div>