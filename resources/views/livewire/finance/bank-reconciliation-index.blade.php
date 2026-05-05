<div>
    <x-page-header title="Conciliación bancaria" description="Importa estados de cuenta y concilia con tus registros internos">
        <x-slot:actions>
            @can('create finance')
            <button wire:click="$set('showNewModal', true)"
                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nueva conciliación
            </button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

        {{-- ── Lista de conciliaciones ──────────────────────────────────── --}}
        <div class="xl:col-span-1">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-700">Conciliaciones</h3>
                </div>
                @forelse($reconciliations as $rec)
                <button wire:click="$set('viewingId', {{ $rec->id }})"
                        class="w-full flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition text-left border-b border-gray-50
                            {{ $viewing?->id === $rec->id ? 'bg-indigo-50 border-l-4 border-l-indigo-500' : '' }}">
                    <div>
                        <p class="text-sm font-medium {{ $viewing?->id === $rec->id ? 'text-indigo-700' : 'text-gray-800' }}">
                            {{ $rec->folio }}
                        </p>
                        <p class="text-xs text-gray-400">{{ $rec->account?->name }}</p>
                        <p class="text-xs text-gray-400">
                            {{ $rec->period_from->format('d/m/Y') }} – {{ $rec->period_to->format('d/m/Y') }}
                        </p>
                    </div>
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                        {{ \App\Models\BankReconciliation::STATUS_COLORS[$rec->status] ?? 'bg-gray-100 text-gray-500' }}">
                        {{ \App\Models\BankReconciliation::STATUS[$rec->status] ?? $rec->status }}
                    </span>
                </button>
                @empty
                <div class="px-5 py-8 text-center text-gray-400 text-sm">Sin conciliaciones.</div>
                @endforelse
                @if($reconciliations->hasPages())
                <div class="px-4 py-3 border-t border-gray-100">{{ $reconciliations->links('vendor.pagination.tailwind') }}</div>
                @endif
            </div>
        </div>

        {{-- ── Panel derecho: detalle ───────────────────────────────────── --}}
        <div class="xl:col-span-2">
            @if(!$viewing)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm flex flex-col items-center justify-center py-20 text-center">
                <svg class="w-12 h-12 text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                </svg>
                <p class="text-sm text-gray-400">Selecciona o crea una conciliación</p>
            </div>
            @else

            {{-- Header conciliación --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 mb-4">
                <div class="flex items-start justify-between flex-wrap gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-bold text-gray-900">{{ $viewing->folio }}</h3>
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                {{ \App\Models\BankReconciliation::STATUS_COLORS[$viewing->status] ?? 'bg-gray-100 text-gray-500' }}">
                                {{ \App\Models\BankReconciliation::STATUS[$viewing->status] ?? $viewing->status }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ $viewing->account?->name }} —
                            {{ $viewing->period_from->format('d/m/Y') }} al {{ $viewing->period_to->format('d/m/Y') }}
                        </p>
                    </div>
                    <div class="flex gap-2 flex-wrap">
                        @if($viewing->status !== 'closed')
                        @can('create finance')
                        <button wire:click="$set('showImportModal', true)"
                                class="text-xs text-indigo-600 hover:text-indigo-800 font-medium px-3 py-1.5 border border-indigo-200 rounded-lg hover:bg-indigo-50 transition">
                            Importar CSV
                        </button>
                        <button wire:click="runAutoMatch({{ $viewing->id }})"
                                class="text-xs text-teal-600 hover:text-teal-800 font-medium px-3 py-1.5 border border-teal-200 rounded-lg hover:bg-teal-50 transition">
                            Auto-match
                        </button>
                        @if($stats['unmatched'] === 0 && $stats['total'] > 0)
                        <button wire:click="closeReconciliation({{ $viewing->id }})"
                                class="text-xs bg-green-600 hover:bg-green-700 text-white font-medium px-3 py-1.5 rounded-lg transition">
                            Cerrar
                        </button>
                        @endif
                        @endcan
                        @endif
                    </div>
                </div>

                {{-- KPIs balances --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4">
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-500 mb-1">Saldo banco</p>
                        <p class="text-sm font-bold text-gray-900">${{ number_format($viewing->bank_closing_balance, 2) }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-500 mb-1">Saldo libros</p>
                        <p class="text-sm font-bold text-gray-900">${{ number_format($viewing->book_closing_balance, 2) }}</p>
                    </div>
                    <div class="bg-{{ abs($viewing->difference) < 0.01 ? 'green' : 'red' }}-50 rounded-lg p-3">
                        <p class="text-xs text-gray-500 mb-1">Diferencia</p>
                        <p class="text-sm font-bold {{ abs($viewing->difference) < 0.01 ? 'text-green-600' : 'text-red-600' }}">
                            ${{ number_format(abs($viewing->difference), 2) }}
                            {{ abs($viewing->difference) < 0.01 ? '✓' : '' }}
                        </p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-500 mb-1">Match</p>
                        <p class="text-sm font-bold text-gray-900">
                            {{ $stats['matched'] }}/{{ $stats['total'] }}
                            <span class="text-xs font-normal text-gray-400">({{ $stats['unmatched'] }} sin match)</span>
                        </p>
                    </div>
                </div>

                {{-- Barra de progreso --}}
                @if($stats['total'] > 0)
                <div class="mt-3">
                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                        <div class="h-1.5 rounded-full bg-green-500 transition-all"
                             style="width: {{ round($stats['matched'] / $stats['total'] * 100) }}%"></div>
                    </div>
                </div>
                @endif
            </div>

            {{-- Líneas del estado de cuenta --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-sm font-semibold text-gray-700">Líneas importadas</h3>
                    <div class="flex gap-2 text-xs">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-green-50 text-green-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> {{ $stats['matched'] }} con match
                        </span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-red-50 text-red-600">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span> {{ $stats['unmatched'] }} sin match
                        </span>
                    </div>
                </div>

                @if($lines->isEmpty())
                <div class="px-5 py-10 text-center text-gray-400 text-sm">
                    Sin líneas importadas. Usa "Importar CSV" para cargar el estado de cuenta del banco.
                </div>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[700px]">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold text-gray-500 uppercase">
                                <th class="text-left px-4 py-2">Fecha</th>
                                <th class="text-left px-4 py-2">Descripción</th>
                                <th class="text-left px-4 py-2 hidden md:table-cell">Referencia</th>
                                <th class="text-right px-4 py-2">Cargo</th>
                                <th class="text-right px-4 py-2">Abono</th>
                                <th class="text-right px-4 py-2 hidden lg:table-cell">Saldo</th>
                                <th class="text-center px-4 py-2">Match</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($lines as $line)
                            <tr class="hover:bg-gray-50 transition
                                {{ $line->match_status === 'unmatched' ? 'bg-red-50/20' : '' }}">
                                <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">
                                    {{ $line->transaction_date->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3 max-w-[200px]">
                                    <p class="text-gray-800 truncate">{{ $line->description ?? '—' }}</p>
                                    @if($line->transaction)
                                    <p class="text-xs text-indigo-500 truncate">↔ {{ $line->transaction->concept }} ({{ $line->transaction->folio }})</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 hidden md:table-cell text-xs text-gray-400">{{ $line->reference ?? '—' }}</td>
                                <td class="px-4 py-3 text-right text-sm {{ $line->flow === 'debit' ? 'text-red-600 font-medium' : 'text-gray-200' }}">
                                    {{ $line->flow === 'debit' ? '−$'.number_format($line->amount, 2) : '' }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm {{ $line->flow === 'credit' ? 'text-green-600 font-medium' : 'text-gray-200' }}">
                                    {{ $line->flow === 'credit' ? '+$'.number_format($line->amount, 2) : '' }}
                                </td>
                                <td class="px-4 py-3 text-right hidden lg:table-cell text-xs text-gray-500">
                                    ${{ number_format($line->balance, 2) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ \App\Models\BankStatementLine::MATCH_COLORS[$line->match_status] ?? 'bg-gray-100 text-gray-500' }}">
                                        {{ \App\Models\BankStatementLine::MATCH_LABELS[$line->match_status] ?? $line->match_status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if($viewing->status !== 'closed')
                                    @if($line->match_status === 'unmatched')
                                    <button wire:click="openMatchModal({{ $line->id }})"
                                            class="text-xs text-indigo-500 hover:text-indigo-700 font-medium px-2 py-1 rounded border border-indigo-100 hover:bg-indigo-50 transition">
                                        Match
                                    </button>
                                    @else
                                    <button wire:click="unmatch({{ $line->id }})"
                                            class="text-xs text-gray-400 hover:text-red-500 px-2 py-1 rounded border border-gray-100 hover:border-red-100 transition">
                                        Deshacer
                                    </button>
                                    @endif
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            @endif {{-- fin @if($viewing) --}}
        </div>
    </div>

    {{-- ── Modal: nueva conciliación ────────────────────────────────────── --}}
    @if($showNewModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">
            <h3 class="text-base font-bold text-gray-900 mb-4">Nueva conciliación bancaria</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Cuenta <span class="text-red-400">*</span></label>
                    <select wire:model="newAccountId"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white {{ $errors->has('newAccountId') ? 'border-red-400' : '' }}">
                        <option value="">Seleccionar cuenta</option>
                        @foreach($accounts as $acc)
                        <option value="{{ $acc['id'] }}">{{ $acc['name'] }} ({{ $acc['currency'] }})</option>
                        @endforeach
                    </select>
                    @error('newAccountId')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Período desde</label>
                        <input wire:model="newPeriodFrom" type="date"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Hasta</label>
                        <input wire:model="newPeriodTo" type="date"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Saldo inicial banco</label>
                        <input wire:model="newBankOpening" type="number" step="0.01"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Saldo final banco</label>
                        <input wire:model="newBankClosing" type="number" step="0.01"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    </div>
                </div>
            </div>
            <div class="flex gap-3 mt-5">
                <button wire:click="createReconciliation"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    Crear
                </button>
                <button wire:click="$set('showNewModal', false)"
                        class="flex-1 text-gray-600 text-sm font-medium px-4 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Modal: importar CSV ──────────────────────────────────────────── --}}
    @if($showImportModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
            <h3 class="text-base font-bold text-gray-900 mb-4">Importar estado de cuenta CSV</h3>

            @if($importError)
            <div class="mb-3 p-3 bg-red-50 border border-red-200 rounded-lg text-xs text-red-600">{{ $importError }}</div>
            @endif

            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Archivo CSV</label>
                    <input wire:model="csvFile" type="file" accept=".csv,.txt"
                           class="w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    <div wire:loading wire:target="csvFile" class="text-xs text-gray-400 mt-1">Cargando vista previa...</div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Separador</label>
                        <select wire:model.live="csvDelimiter"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                            <option value=",">Coma (,)</option>
                            <option value=";">Punto y coma (;)</option>
                            <option value="&#9;">Tabulador</option>
                        </select>
                    </div>
                    <div class="flex items-end pb-2">
                        <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                            <input wire:model="csvHasHeader" type="checkbox" class="rounded border-gray-300">
                            Primera fila es encabezado
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Columna fecha (0-based)</label>
                        <input wire:model="csvDateColumn" type="number" min="0"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Columna descripción</label>
                        <input wire:model="csvDescColumn" type="number" min="0"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Columna monto</label>
                        <input wire:model="csvAmountColumn" type="number" min="0"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <p class="text-xs text-gray-400 mt-0.5">Positivo=crédito, negativo=débito</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Columna saldo</label>
                        <input wire:model="csvBalanceColumn" type="number" min="0"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    </div>
                </div>

                {{-- Vista previa --}}
                @if(!empty($csvPreview))
                <div>
                    <p class="text-xs font-medium text-gray-600 mb-1">Vista previa (primeras 5 filas)</p>
                    <div class="overflow-x-auto border border-gray-100 rounded-lg">
                        <table class="w-full text-xs">
                            @foreach($csvPreview as $i => $row)
                            <tr class="{{ $i === 0 && $csvHasHeader ? 'bg-gray-50 font-semibold' : 'border-t border-gray-50' }}">
                                @foreach($row as $j => $cell)
                                <td class="px-2 py-1.5 text-gray-600 max-w-[100px] truncate
                                    {{ in_array($j, [(int)$csvDateColumn,(int)$csvDescColumn,(int)$csvAmountColumn,(int)$csvBalanceColumn]) ? 'bg-indigo-50' : '' }}">
                                    {{ $cell }}
                                </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
                @endif
            </div>

            <div class="flex gap-3 mt-5">
                <button wire:click="importCsv" wire:loading.attr="disabled"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    <span wire:loading.remove wire:target="importCsv">Importar y hacer match</span>
                    <span wire:loading wire:target="importCsv">Importando...</span>
                </button>
                <button wire:click="$set('showImportModal', false)"
                        class="flex-1 text-gray-600 text-sm font-medium px-4 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Modal: match manual ─────────────────────────────────────────── --}}
    @if($showMatchModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-lg mx-4">
            <h3 class="text-base font-bold text-gray-900 mb-4">Seleccionar transacción para match</h3>
            <div class="max-h-64 overflow-y-auto border border-gray-100 rounded-lg divide-y divide-gray-50">
                @forelse($unmatchedTxs as $tx)
                <label class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 cursor-pointer">
                    <input type="radio" wire:model="matchTxId" value="{{ $tx->id }}"
                           class="text-indigo-600 border-gray-300">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-800">{{ $tx->concept }}</p>
                        <p class="text-xs text-gray-400">
                            {{ \Carbon\Carbon::parse($tx->transaction_date)->format('d/m/Y') }} —
                            ${{ number_format($tx->amount, 2) }} —
                            {{ $tx->folio }}
                        </p>
                    </div>
                </label>
                @empty
                <p class="px-4 py-4 text-sm text-gray-400 text-center">Sin transacciones disponibles para este tipo y período.</p>
                @endforelse
            </div>
            <div class="flex gap-3 mt-4">
                <button wire:click="confirmManualMatch"
                        :disabled="{{ !$matchTxId ? 'true' : 'false' }}"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    Confirmar match
                </button>
                <button wire:click="$set('showMatchModal', false)"
                        class="flex-1 text-gray-600 text-sm font-medium px-4 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
