<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a wire:navigate href="{{ route('sales.crm.pipeline') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-xl font-medium text-gray-900">{{ $opportunity ? 'Editar oportunidad' : 'Nueva oportunidad' }}</h1>
    </div>

    <form wire:submit="save" class="space-y-5">
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Información de la oportunidad</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Título *</label>
                    <input wire:model="title" type="text" placeholder="Ej: Proyecto instalación eléctrica norte"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    @error('title') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Vinculado a --}}
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Vinculado a</label>
                    <div class="flex gap-3 mb-2">
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" wire:model.live="linked_type" value="prospect"> <span class="text-sm text-gray-700">Prospecto</span>
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" wire:model.live="linked_type" value="customer"> <span class="text-sm text-gray-700">Cliente</span>
                        </label>
                    </div>
                    @if($linked_type === 'prospect')
                        <select wire:model="prospect_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                            <option value="">— Seleccionar prospecto —</option>
                            @foreach($prospects as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    @else
                        <select wire:model="customer_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                            <option value="">— Seleccionar cliente —</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <div>
                    <label class="block text-xs text-gray-500 mb-1">Etapa</label>
                    <select wire:model.live="stage" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        @foreach(\App\Models\SalesOpportunity::STAGES as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Probabilidad (%)</label>
                    <input wire:model="probability" type="number" min="0" max="100"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Valor estimado ($)</label>
                    <input wire:model="estimated_value" type="number" step="0.01" min="0"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Fecha estimada de cierre</label>
                    <input wire:model="expected_close_date" type="date"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Asignado a</label>
                    <select wire:model="assigned_to" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="">— Sin asignar —</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                @if($stage === 'lost')
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Motivo de pérdida</label>
                        <select wire:model="lost_reason" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                            <option value="">— Seleccionar —</option>
                            @foreach(\App\Models\SalesOpportunity::LOST_REASONS as $k => $v)
                                <option value="{{ $k }}">{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Descripción</label>
                    <textarea wire:model="description" rows="3"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"></textarea>
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pb-6">
            <a wire:navigate href="{{ route('sales.crm.pipeline') }}"
                class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition text-center">Cancelar</a>
            <button type="submit" class="px-5 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
                {{ $opportunity ? 'Guardar cambios' : 'Crear oportunidad' }}
            </button>
        </div>
    </form>
</div>
