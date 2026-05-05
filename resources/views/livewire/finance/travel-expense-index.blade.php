<div class="min-h-screen bg-slate-50/50 -m-4 sm:-m-6 lg:-m-8">
    {{-- STICKY HEADER --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-xl bg-amber-500 flex items-center justify-center text-white shrink-0 shadow-lg shadow-amber-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">Viáticos</h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Gastos de viaje y comisiones</p>
                </div>
            </div>
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                @can('manage travel expenses')
                <a wire:navigate href="{{ route('finance.travel-expenses.create') }}"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-amber-500/25 hover:shadow-amber-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Nuevo viático</span>
                </a>
                @endcan
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto p-4 sm:p-6 lg:p-8 space-y-6">

        @if(session('success'))
            <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl animate-in fade-in slide-in-from-top-4 duration-300">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-sm font-semibold">{{ session('success') }}</p>
            </div>
        @endif

        {{-- Status KPIs --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
            @php
                $statDefs = [
                    'borrador'   => ['label' => 'Borrador',    'color' => 'slate',   'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                    'aprobado'   => ['label' => 'Aprobados',   'color' => 'blue',    'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                    'pagado'     => ['label' => 'Pagados',     'color' => 'emerald', 'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
                    'comprobado' => ['label' => 'Comprobados', 'color' => 'violet',  'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
                    'rechazado'  => ['label' => 'Rechazados',  'color' => 'red',     'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'],
                ];
            @endphp
            @foreach($statDefs as $key => $def)
                <button wire:click="$set('filterStatus', '{{ $filterStatus === $key ? '' : $key }}')"
                    class="bg-white rounded-2xl border p-4 text-left transition-all hover:shadow-md {{ $filterStatus === $key ? 'border-' . $def['color'] . '-300 ring-2 ring-' . $def['color'] . '-100' : 'border-slate-200/60' }}">
                    <p class="text-2xl font-black text-slate-800">{{ $counts->get($key, 0) }}</p>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">{{ $def['label'] }}</p>
                </button>
            @endforeach
        </div>

        {{-- Filters --}}
        <div class="bg-white p-4 rounded-3xl border border-slate-200/60 shadow-sm flex flex-wrap gap-3 items-center">
            <div class="flex-1 min-w-[240px] relative group">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar folio, destino, propósito..."
                    class="w-full pl-11 pr-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm">
            </div>
            <select wire:model.live="filterEmployee"
                class="px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 text-sm font-medium text-slate-600 max-w-[200px]">
                <option value="">Todos los empleados</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->first_name }} {{ $emp->last_name }}</option>
                @endforeach
            </select>
            <input type="date" wire:model.live="filterDateFrom"
                class="px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 text-sm text-slate-600">
            <input type="date" wire:model.live="filterDateTo"
                class="px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 text-sm text-slate-600">
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="px-5 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Folio</th>
                            <th class="px-5 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Empleado</th>
                            <th class="px-5 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Destino / Propósito</th>
                            <th class="px-5 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Fechas</th>
                            <th class="px-5 py-4 text-right text-[10px] font-bold text-slate-400 uppercase tracking-widest">Monto</th>
                            <th class="px-5 py-4 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest">Estado</th>
                            <th class="px-5 py-4 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest">Viaje</th>
                            <th class="px-5 py-4 text-right text-[10px] font-bold text-slate-400 uppercase tracking-widest">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($travels as $travel)
                            @php
                                $colors = \App\Models\TravelExpense::STATUS_COLORS[$travel->status] ?? 'bg-slate-100 text-slate-600 border-slate-200';
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-5 py-4">
                                    <span class="text-sm font-mono font-bold text-slate-700">{{ $travel->folio }}</span>
                                    @if($travel->project)
                                        <p class="text-[10px] text-indigo-500 font-medium mt-0.5">{{ $travel->project->name }}</p>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <p class="text-sm font-semibold text-slate-700">{{ $travel->employee->full_name }}</p>
                                    <p class="text-[11px] text-slate-400">{{ $travel->employee->employee_number }}</p>
                                </td>
                                <td class="px-5 py-4 max-w-[200px]">
                                    <p class="text-sm font-semibold text-slate-700 truncate">{{ $travel->destination }}</p>
                                    <p class="text-[11px] text-slate-400 truncate">{{ $travel->purpose }}</p>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <p class="text-sm font-medium text-slate-700">{{ $travel->departure_date->format('d/m/Y') }}</p>
                                    <p class="text-[11px] text-slate-400">→ {{ $travel->return_date->format('d/m/Y') }}</p>
                                </td>
                                <td class="px-5 py-4 text-right whitespace-nowrap">
                                    <p class="text-sm font-black text-slate-800">${{ number_format($travel->amount_approved, 2) }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $travel->currency }}</p>
                                    @if($travel->amount_spent !== null)
                                        <p class="text-[10px] text-slate-500">Gasto: ${{ number_format($travel->amount_spent, 2) }}</p>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-[10px] font-bold border {{ $colors }}">
                                        {{ \App\Models\TravelExpense::STATUSES[$travel->status] }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    @if($travel->trip_confirmed)
                                        <span class="inline-flex items-center gap-1 text-emerald-600 text-[11px] font-bold">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            Confirmado
                                        </span>
                                    @elseif($travel->status === 'pagado')
                                        <span class="text-amber-500 text-[11px] font-bold">Pendiente</span>
                                    @else
                                        <span class="text-slate-300 text-[11px]">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-1">
                                        {{-- Edit (only borrador) --}}
                                        @if($travel->status === 'borrador')
                                            @can('manage travel expenses')
                                            <a wire:navigate href="{{ route('finance.travel-expenses.edit', $travel) }}"
                                                class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </a>
                                            @endcan
                                        @endif

                                        {{-- Approve (borrador) --}}
                                        @if($travel->status === 'borrador')
                                            @can('manage travel expenses')
                                            <button wire:click="openApprove({{ $travel->id }})"
                                                class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-all" title="Aprobar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                            </button>
                                            <button wire:click="openReject({{ $travel->id }})"
                                                class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:text-red-500 hover:bg-red-50 transition-all" title="Rechazar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </button>
                                            @endcan
                                        @endif

                                        {{-- Pay (aprobado) --}}
                                        @if($travel->status === 'aprobado')
                                            @can('manage travel expenses')
                                            <button wire:click="openPay({{ $travel->id }})"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-bold rounded-xl transition-colors" title="Registrar pago">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                                Pagar
                                            </button>
                                            @endcan
                                        @endif

                                        {{-- Comprobar (pagado) --}}
                                        @if($travel->status === 'pagado')
                                            @can('manage travel expenses')
                                            <button wire:click="openComprobar({{ $travel->id }})"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-violet-50 hover:bg-violet-100 text-violet-700 text-xs font-bold rounded-xl transition-colors" title="Registrar comprobación">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                                Comprobar
                                            </button>
                                            @endcan
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center">
                                            <svg class="w-7 h-7 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                        </div>
                                        <p class="text-sm font-semibold text-slate-400">No hay viáticos registrados</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($travels->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">{{ $travels->links() }}</div>
            @endif
        </div>
    </div>

    {{-- ─── APPROVE MODAL ─────────────────────────────────────────────── --}}
    @if($showApproveModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-800">Aprobar viático</h3>
                    <button wire:click="$set('showApproveModal', false)" class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-5">
                    <div class="space-y-2">
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Cuenta de pago *</label>
                        <select wire:model="approveAccountId"
                            class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 text-sm font-medium">
                            <option value="">— Selecciona cuenta —</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                        @error('approveAccountId') <p class="text-xs text-red-500 mt-1 ml-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-2">
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Notas de aprobación</label>
                        <textarea wire:model="approveNotes" rows="3" placeholder="Instrucciones adicionales..."
                            class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 text-sm resize-none"></textarea>
                    </div>
                </div>
                <div class="px-6 pb-6 flex gap-3 justify-end">
                    <button wire:click="$set('showApproveModal', false)" class="px-4 py-2.5 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">Cancelar</button>
                    <button wire:click="approve"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition-colors shadow-lg shadow-blue-500/25">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Aprobar viático
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ─── REJECT MODAL ──────────────────────────────────────────────── --}}
    @if($showRejectModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-800">Rechazar viático</h3>
                    <button wire:click="$set('showRejectModal', false)" class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6">
                    <div class="space-y-2">
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Motivo del rechazo *</label>
                        <textarea wire:model="rejectReason" rows="3" placeholder="Explica el motivo del rechazo..."
                            class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-red-400 focus:ring-4 focus:ring-red-400/5 text-sm resize-none"></textarea>
                        @error('rejectReason') <p class="text-xs text-red-500 mt-1 ml-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="px-6 pb-6 flex gap-3 justify-end">
                    <button wire:click="$set('showRejectModal', false)" class="px-4 py-2.5 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">Cancelar</button>
                    <button wire:click="reject"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-bold rounded-xl transition-colors shadow-lg shadow-red-500/25">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                        Rechazar
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ─── PAY MODAL ─────────────────────────────────────────────────── --}}
    @if($showPayModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-800">Registrar pago</h3>
                    <button wire:click="$set('showPayModal', false)" class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div class="p-4 bg-emerald-50 border border-emerald-100 rounded-2xl">
                        <p class="text-xs font-bold text-emerald-700">Se creará una transacción financiera de egreso automáticamente.</p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Notas del pago</label>
                        <textarea wire:model="payNotes" rows="3" placeholder="Referencia de transferencia, cheque, etc."
                            class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-emerald-400 text-sm resize-none"></textarea>
                    </div>
                </div>
                <div class="px-6 pb-6 flex gap-3 justify-end">
                    <button wire:click="$set('showPayModal', false)" class="px-4 py-2.5 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">Cancelar</button>
                    <button wire:click="pay" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl transition-colors shadow-lg shadow-emerald-500/25 disabled:opacity-60">
                        <svg wire:loading.remove wire:target="pay" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <svg wire:loading wire:target="pay" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        Confirmar pago
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ─── COMPROBAR MODAL ────────────────────────────────────────────── --}}
    @if($showComprobarModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-800">Comprobación de viático</h3>
                    <button wire:click="$set('showComprobarModal', false)" class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-5">
                    <div class="space-y-2">
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Monto realmente gastado *</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm">$</span>
                            <input wire:model="amountSpent" type="number" step="0.01" min="0"
                                class="w-full pl-8 pr-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-violet-400 focus:ring-4 focus:ring-violet-400/5 text-right text-lg font-black text-slate-800">
                        </div>
                        @error('amountSpent') <p class="text-xs text-red-500 mt-1 ml-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-2">
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Notas de comprobación</label>
                        <textarea wire:model="comprobarNotes" rows="3" placeholder="Observaciones o diferencias..."
                            class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-violet-400 text-sm resize-none"></textarea>
                    </div>
                </div>
                <div class="px-6 pb-6 flex gap-3 justify-end">
                    <button wire:click="$set('showComprobarModal', false)" class="px-4 py-2.5 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">Cancelar</button>
                    <button wire:click="comprobar"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-violet-600 hover:bg-violet-700 text-white text-sm font-bold rounded-xl transition-colors shadow-lg shadow-violet-500/25">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        Registrar comprobación
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
