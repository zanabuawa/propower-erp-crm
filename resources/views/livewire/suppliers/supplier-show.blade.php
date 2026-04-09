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
                </div>
                <p class="text-sm text-gray-500">
                    {{ $supplier->type === 'company' ? 'Empresa' : 'Persona física' }}
                    @if($supplier->rfc) · RFC: {{ $supplier->rfc }} @endif
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

        <div class="space-y-4">
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            {{ $supplier->address }}, {{ $supplier->city }}, {{ $supplier->state }}
                        </div>
                    @endif
                </div>
            </div>

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

        <div class="md:col-span-1 lg:col-span-2 space-y-4">
            <div class="flex gap-1 bg-gray-100 p-1 rounded-lg w-fit">
                <button wire:click="$set('activeTab', 'contacts')"
                    class="px-4 py-1.5 text-sm rounded-md transition {{ $activeTab === 'contacts' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    Contactos ({{ $supplier->contacts->count() }})
                </button>
                <button wire:click="$set('activeTab', 'notes')"
                    class="px-4 py-1.5 text-sm rounded-md transition {{ $activeTab === 'notes' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    Historial ({{ $supplier->notes->count() }})
                </button>
            </div>

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
                                <div class="col-span-1 sm:col-span-2">
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 text-center py-4">No hay contactos vinculados.</p>
                    @endforelse
                </div>
            @endif

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
                        $noteIcons = ['note' => '📝', 'call' => '📞', 'email' => '✉️', 'meeting' => '🤝', 'task' => '✅'];
                    @endphp

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
                                        <button wire:click="deleteNote({{ $note->id }})"
                                            class="text-red-400 hover:text-red-600">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M6 18L18 6M6 6l12 12" />
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
