<div>
    {{-- Préstamos --}}
    <div class="bg-white rounded-xl border border-slate-200 mb-4">
        <div class="p-4 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-sm font-semibold text-slate-700">Préstamos</h3>
            @can('edit hr')
            <button wire:click="openLoan"
                    class="text-xs font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 px-2 py-1 rounded">
                + NUEVO PRÉSTAMO
            </button>
            @endcan
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($loans as $loan)
            <div class="px-4 py-3 flex items-center justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                            {{ $loan->status === 'active' ? 'bg-blue-100 text-blue-700' : ($loan->status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600') }}">
                            {{ ['active' => 'Activo', 'paid' => 'Liquidado', 'cancelled' => 'Cancelado'][$loan->status] ?? $loan->status }}
                        </span>
                        <span class="text-xs text-slate-500">{{ $loan->loan_date->format('d/m/Y') }}</span>
                    </div>
                    @if($loan->reason)
                    <p class="text-xs text-slate-400 mt-0.5">{{ $loan->reason }}</p>
                    @endif
                    <p class="text-xs text-slate-500 mt-0.5">
                        Cuota: ${{ number_format($loan->installment_amount, 2) }} · Saldo: <span class="{{ $loan->balance > 0 ? 'text-red-500 font-medium' : 'text-green-600' }}">${{ number_format($loan->balance, 2) }}</span>
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold text-slate-800">${{ number_format($loan->amount, 2) }}</p>
                    @if($loan->status === 'active')
                    <button wire:click="cancelLoan({{ $loan->id }})"
                            wire:confirm="¿Cancelar este préstamo?"
                            class="text-[10px] text-red-500 hover:text-red-700 mt-1">Cancelar</button>
                    @endif
                </div>
            </div>
            @empty
            <div class="px-4 py-6 text-center text-sm text-slate-400">Sin préstamos registrados</div>
            @endforelse
        </div>
    </div>

    {{-- Bonos --}}
    <div class="bg-white rounded-xl border border-slate-200">
        <div class="p-4 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-sm font-semibold text-slate-700">Bonos</h3>
            @can('edit hr')
            <button wire:click="openBonus"
                    class="text-xs font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 px-2 py-1 rounded">
                + NUEVO BONO
            </button>
            @endcan
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($bonuses as $bonus)
            <div class="px-4 py-3 flex items-center justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                            {{ $bonus->is_applied ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                            {{ $bonus->is_applied ? 'Aplicado' : 'Pendiente' }}
                        </span>
                        <span class="text-xs text-slate-500">Aplica: {{ $bonus->apply_at->format('d/m/Y') }}</span>
                    </div>
                    @if($bonus->reason)
                    <p class="text-xs text-slate-400 mt-0.5">{{ $bonus->reason }}</p>
                    @endif
                    @if($bonus->concept)
                    <p class="text-xs text-slate-400 mt-0.5">Concepto: {{ $bonus->concept->name }}</p>
                    @endif
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold text-green-700">${{ number_format($bonus->amount, 2) }}</p>
                    @if(!$bonus->is_applied)
                    <button wire:click="deleteBonus({{ $bonus->id }})"
                            wire:confirm="¿Eliminar este bono?"
                            class="text-[10px] text-red-500 hover:text-red-700 mt-1">Eliminar</button>
                    @endif
                </div>
            </div>
            @empty
            <div class="px-4 py-6 text-center text-sm text-slate-400">Sin bonos registrados</div>
            @endforelse
        </div>
    </div>

    {{-- Modal Préstamo --}}
    @if($modalType === 'loan')
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">
            <h3 class="text-base font-bold text-slate-800 mb-4">Registrar préstamo</h3>
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Monto total <span class="text-red-400">*</span></label>
                        <input wire:model="loanAmount" type="number" min="1" step="0.01"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                               placeholder="0.00">
                        @error('loanAmount') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Cuota por nómina <span class="text-red-400">*</span></label>
                        <input wire:model="loanInstallment" type="number" min="1" step="0.01"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                               placeholder="0.00">
                        @error('loanInstallment') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Fecha del préstamo</label>
                    <input wire:model="loanDate" type="date"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Motivo</label>
                    <input wire:model="loanReason" type="text"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                           placeholder="Motivo del préstamo">
                </div>
            </div>
            <div class="flex gap-3 mt-5">
                <button wire:click="saveLoan" wire:loading.attr="disabled"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    Guardar
                </button>
                <button wire:click="$set('modalType', '')"
                        class="flex-1 text-slate-600 text-sm px-4 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 transition">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal Bono --}}
    @if($modalType === 'bonus')
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">
            <h3 class="text-base font-bold text-slate-800 mb-4">Registrar bono</h3>
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Monto <span class="text-red-400">*</span></label>
                        <input wire:model="bonusAmount" type="number" min="0.01" step="0.01"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                               placeholder="0.00">
                        @error('bonusAmount') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Aplicar en nómina del</label>
                        <input wire:model="bonusApplyAt" type="date"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Concepto</label>
                    <select wire:model="bonusConcept"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                        <option value="">Sin concepto específico</option>
                        @foreach($concepts as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Descripción / motivo</label>
                    <input wire:model="bonusReason" type="text"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                           placeholder="Ej. Bono por cumplimiento de metas">
                </div>
            </div>
            <div class="flex gap-3 mt-5">
                <button wire:click="saveBonus" wire:loading.attr="disabled"
                        class="flex-1 bg-green-600 hover:bg-green-700 disabled:opacity-60 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    Guardar bono
                </button>
                <button wire:click="$set('modalType', '')"
                        class="flex-1 text-slate-600 text-sm px-4 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 transition">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
