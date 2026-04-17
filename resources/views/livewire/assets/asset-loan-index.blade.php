<div>
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a wire:navigate href="{{ route('assets.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-medium text-gray-900">Préstamos y herramientas asignadas</h1>
                @if($overdueCount > 0)
                    <p class="text-xs text-red-500 mt-0.5">{{ $overdueCount }} préstamo(s) vencido(s) sin devolución</p>
                @endif
            </div>
        </div>
        <a wire:navigate href="{{ route('assets.loans.create') }}"
            class="px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
            + Nuevo préstamo
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>
    @endif

    {{-- Filtros --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-5 flex flex-wrap gap-3">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por folio, activo o persona..."
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 min-w-64">

        <select wire:model.live="filterStatus"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
            <option value="">— Todos los estados —</option>
            @foreach(\App\Models\AssetLoan::STATUSES as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @if($loans->isEmpty())
            <div class="p-10 text-center text-gray-400 text-sm">No hay préstamos registrados.</div>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left">Folio</th>
                        <th class="px-4 py-3 text-left">Activo</th>
                        <th class="px-4 py-3 text-left">Prestado a</th>
                        <th class="px-4 py-3 text-left">Fecha préstamo</th>
                        <th class="px-4 py-3 text-left">Devolución esperada</th>
                        <th class="px-4 py-3 text-left">Estado</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($loans as $loan)
                        @php
                            $overdue = $loan->isOverdue();
                            $color   = \App\Models\AssetLoan::STATUS_COLORS[$loan->status] ?? 'gray';
                        @endphp
                        <tr class="hover:bg-gray-50 transition {{ $overdue ? 'bg-red-50' : '' }}">
                            <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $loan->folio }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-800">{{ $loan->asset->name }}</div>
                                <div class="text-xs text-gray-400">{{ $loan->asset->folio }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $loan->recipient_name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $loan->loan_date->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">
                                @if($loan->expected_return_date)
                                    <span class="{{ $overdue ? 'text-red-600 font-medium' : 'text-gray-600' }}">
                                        {{ $loan->expected_return_date->format('d/m/Y') }}
                                        @if($overdue) <span class="text-xs">(vencido)</span> @endif
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                    bg-{{ $color }}-100 text-{{ $color }}-700">
                                    {{ \App\Models\AssetLoan::STATUSES[$loan->status] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($loan->status === 'active')
                                    <button wire:click="openReturnModal({{ $loan->id }})"
                                        class="text-xs text-indigo-600 hover:text-indigo-800 font-medium mr-3">
                                        Registrar devolución
                                    </button>
                                @else
                                    <span class="text-xs text-gray-400">
                                        {{ $loan->actual_return_date?->format('d/m/Y') ?? '—' }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $loans->links() }}
            </div>
        @endif
    </div>

    {{-- Modal devolución --}}
    @if($showReturnModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md">
                <h2 class="text-base font-semibold text-gray-900 mb-4">Registrar devolución</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Condición al regreso</label>
                        <select wire:model="conditionOnReturn"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                            @foreach(\App\Models\AssetLoan::CONDITIONS as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Notas de devolución</label>
                        <textarea wire:model="returnNotes" rows="3"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 resize-none"
                            placeholder="Observaciones al recibir el activo..."></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-5">
                    <button wire:click="closeReturnModal"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                        Cancelar
                    </button>
                    <button wire:click="confirmReturn"
                        class="px-5 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
                        Confirmar devolución
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
