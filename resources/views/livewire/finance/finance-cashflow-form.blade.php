<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a wire:navigate href="{{ route('finance.cashflow.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-xl font-medium text-gray-900">
            {{ $cashflow && $cashflow->exists ? 'Editar movimiento' : 'Nuevo movimiento de flujo' }}
        </h1>
    </div>

    <form wire:submit="save" class="space-y-5">

        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Datos del movimiento</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Flujo *</label>
                    <select wire:model="flow" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                        <option value="entrada">Entrada</option>
                        <option value="salida">Salida</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Tipo *</label>
                    <select wire:model="type" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                        <option value="proyectado">Proyectado</option>
                        <option value="real">Real</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Categoría *</label>
                    <select wire:model="category" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                        <option value="operacion">Operación</option>
                        <option value="inversion">Inversión</option>
                        <option value="financiamiento">Financiamiento</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Cuenta *</label>
                    <select wire:model="account_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                        <option value="">— Seleccionar —</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
                    @error('account_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Concepto *</label>
                    <input wire:model="concept" type="text" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    @error('concept') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Monto *</label>
                    <input wire:model="amount" type="number" step="0.01" min="0.01" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    @error('amount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Moneda</label>
                    <select wire:model="currency" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                        <option value="MXN">MXN</option>
                        <option value="USD">USD</option>
                        <option value="EUR">EUR</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Fecha esperada *</label>
                    <input wire:model="expected_date" type="date" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    @error('expected_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Fecha realizada</label>
                    <input wire:model="realized_date" type="date" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Proyecto</label>
                    <select wire:model="project_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                        <option value="">— Sin proyecto —</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->code }} – {{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Presupuesto</label>
                    <select wire:model="budget_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                        <option value="">— Sin presupuesto —</option>
                        @foreach($budgets as $budget)
                            <option value="{{ $budget->id }}">{{ $budget->name }} ({{ $budget->year }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Notas</label>
                    <textarea wire:model="notes" rows="2" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 resize-none"></textarea>
                </div>
                <div class="sm:col-span-2 flex items-center gap-2">
                    <input wire:model="is_realized" type="checkbox" id="is_realized" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-300">
                    <label for="is_realized" class="text-sm text-gray-700">Movimiento ya realizado</label>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a wire:navigate href="{{ route('finance.cashflow.index') }}"
                class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 border border-gray-200 rounded-lg transition">
                Cancelar
            </a>
            <button type="submit"
                class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition shadow-sm">
                {{ $cashflow && $cashflow->exists ? 'Guardar cambios' : 'Registrar movimiento' }}
            </button>
        </div>
    </form>
</div>
