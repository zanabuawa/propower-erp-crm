<div class="min-h-screen bg-slate-50/50 -m-4 sm:-m-6 lg:-m-8">
    <div class="sticky top-0 z-30 border-b border-slate-200/60 bg-white/80 px-4 py-3 backdrop-blur-md sm:px-6 lg:px-8">
        <div class="mx-auto flex max-w-full items-center justify-between gap-4">
            <div class="flex min-w-0 items-center gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-lg shadow-emerald-500/20">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 10v-1m0 0c-1.11 0-2.08-.402-2.599-1M12 16c1.11 0 2.08-.402 2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h1 class="truncate text-lg font-bold text-slate-800 sm:text-xl">Complementos de Nomina</h1>
                    <p class="text-[11px] font-medium uppercase tracking-wider text-slate-400">Conceptos, bonos, prestamos y ajustes para calculo de nomina</p>
                </div>
            </div>

            <div class="flex shrink-0 items-center gap-2 sm:gap-3">
                @can('create hr')
                    <a wire:navigate href="{{ route('hr.payroll.concepts.create') }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-700 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-emerald-500/25 transition-all duration-200 hover:scale-[1.02] hover:from-emerald-700 hover:to-emerald-800 active:scale-[0.98]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>Nuevo concepto</span>
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-full space-y-8 p-4 sm:p-6 lg:p-8">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-3xl border border-slate-200/60 bg-white p-6 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Conceptos</p>
                <p class="mt-2 text-3xl font-black text-slate-800">{{ $conceptStats['total'] }}</p>
                <p class="mt-1 text-xs font-semibold text-slate-400">{{ $conceptStats['active'] }} activos</p>
            </div>
            <div class="rounded-3xl border border-emerald-100 bg-white p-6 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-widest text-emerald-500">Percepciones</p>
                <p class="mt-2 text-3xl font-black text-emerald-600">{{ $conceptStats['perceptions'] }}</p>
                <p class="mt-1 text-xs font-semibold text-slate-400">Bonos, vales y pagos extra</p>
            </div>
            <div class="rounded-3xl border border-rose-100 bg-white p-6 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-widest text-rose-500">Deducciones</p>
                <p class="mt-2 text-3xl font-black text-rose-600">{{ $conceptStats['deductions'] }}</p>
                <p class="mt-1 text-xs font-semibold text-slate-400">Descuentos y retenciones</p>
            </div>
            <div class="rounded-3xl border border-indigo-100 bg-white p-6 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-widest text-indigo-500">Pendiente por aplicar</p>
                <p class="mt-2 text-2xl font-black text-indigo-600">$ {{ number_format((float) $totals['pending_bonus_amount'], 2) }}</p>
                <p class="mt-1 text-xs font-semibold text-slate-400">Bonos en proximas nominas</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <a wire:navigate href="{{ route('hr.payroll.concepts') }}"
               class="group rounded-3xl border border-slate-200/60 bg-white p-6 shadow-sm transition-all hover:-translate-y-0.5 hover:border-emerald-100 hover:shadow-xl hover:shadow-emerald-500/5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-base font-black text-slate-800 transition-colors group-hover:text-emerald-600">Conceptos de nomina</h2>
                        <p class="mt-2 text-sm font-medium text-slate-500">Administra percepciones, deducciones, claves internas y conceptos gravables.</p>
                    </div>
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </span>
                </div>
            </a>

            <a wire:navigate href="{{ route('hr.employees.index') }}"
               class="group rounded-3xl border border-slate-200/60 bg-white p-6 shadow-sm transition-all hover:-translate-y-0.5 hover:border-indigo-100 hover:shadow-xl hover:shadow-indigo-500/5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-base font-black text-slate-800 transition-colors group-hover:text-indigo-600">Bonos y prestamos</h2>
                        <p class="mt-2 text-sm font-medium text-slate-500">Registra complementos desde el expediente del empleado para aplicarlos en nomina.</p>
                    </div>
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                    </span>
                </div>
            </a>

            <a wire:navigate href="{{ route('hr.payrolls.index') }}"
               class="group rounded-3xl border border-slate-200/60 bg-white p-6 shadow-sm transition-all hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-xl hover:shadow-slate-500/5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-base font-black text-slate-800 transition-colors group-hover:text-slate-900">Volver a nominas</h2>
                        <p class="mt-2 text-sm font-medium text-slate-500">Consulta, calcula, aprueba y timbra los periodos de nomina.</p>
                    </div>
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/></svg>
                    </span>
                </div>
            </a>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div class="overflow-hidden rounded-3xl border border-slate-200/60 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-slate-50/40 px-6 py-4">
                    <h3 class="text-xs font-black uppercase tracking-widest text-slate-500">Bonos pendientes</h3>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($pendingBonuses as $bonus)
                        <div class="flex items-center justify-between gap-4 px-6 py-4">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-slate-700">{{ $bonus->employee?->full_name ?? 'Empleado no disponible' }}</p>
                                <p class="mt-0.5 text-xs font-medium text-slate-400">{{ $bonus->concept?->name ?? $bonus->reason ?? 'Bono' }} · {{ $bonus->apply_at?->format('d/m/Y') }}</p>
                            </div>
                            <p class="shrink-0 text-sm font-black text-emerald-600">$ {{ number_format((float) $bonus->amount, 2) }}</p>
                        </div>
                    @empty
                        <div class="px-6 py-10 text-center text-sm font-medium text-slate-400">No hay bonos pendientes por aplicar.</div>
                    @endforelse
                </div>
            </div>

            <div class="overflow-hidden rounded-3xl border border-slate-200/60 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-slate-50/40 px-6 py-4">
                    <h3 class="text-xs font-black uppercase tracking-widest text-slate-500">Prestamos activos</h3>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($activeLoans as $loan)
                        <div class="flex items-center justify-between gap-4 px-6 py-4">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-slate-700">{{ $loan->employee?->full_name ?? 'Empleado no disponible' }}</p>
                                <p class="mt-0.5 text-xs font-medium text-slate-400">{{ $loan->reason ?? 'Prestamo' }} · cuota $ {{ number_format((float) $loan->installment_amount, 2) }}</p>
                            </div>
                            <p class="shrink-0 text-sm font-black text-rose-600">$ {{ number_format((float) $loan->balance, 2) }}</p>
                        </div>
                    @empty
                        <div class="px-6 py-10 text-center text-sm font-medium text-slate-400">No hay prestamos activos.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
