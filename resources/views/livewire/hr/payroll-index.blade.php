<div>
    <x-page-header title="Nóminas" description="Cálculo y timbrado de nómina CFDI">
        <x-slot:actions>
            <a href="{{ route('hr.payrolls.print', request()->query()) }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-lg border border-slate-200 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Imprimir
            </a>
            @can('create hr')
            <a href="{{ route('hr.payrolls.create') }}" wire:navigate
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg">
                + Nueva nómina
            </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="mb-5">
        <select wire:model.live="filterStatus"
                class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
            <option value="">Todos los estados</option>
            @foreach(\App\Models\HrPayroll::STATUSES as $k => $v)
                <option value="{{ $k }}">{{ $v }}</option>
            @endforeach
        </select>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/60">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Folio</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Periodo</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase hidden md:table-cell">Empleados</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase hidden md:table-cell">Total bruto</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase hidden lg:table-cell">Total neto</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Estado</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($payrolls as $payroll)
                <tr class="hover:bg-slate-50/50">
                    <td class="px-4 py-3">
                        <p class="font-mono text-slate-700 text-xs">{{ $payroll->folio }}</p>
                        <p class="text-xs text-slate-400">{{ \App\Models\HrPayroll::PERIOD_TYPES[$payroll->period_type] ?? $payroll->period_type }}</p>
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-600">{{ $payroll->period_label }}</td>
                    <td class="px-4 py-3 hidden md:table-cell text-slate-600">{{ $payroll->total_employees }}</td>
                    <td class="px-4 py-3 hidden md:table-cell text-slate-700 font-medium">$ {{ number_format($payroll->total_gross, 2) }}</td>
                    <td class="px-4 py-3 hidden lg:table-cell text-green-700 font-semibold">$ {{ number_format($payroll->total_net, 2) }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $payroll->status_color }}">{{ $payroll->status_label }}</span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('hr.payrolls.show', $payroll) }}" wire:navigate
                           class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Ver</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-10 text-center text-slate-400 text-sm">No hay nóminas registradas.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($payrolls->hasPages())
        <div class="px-4 py-3 border-t border-slate-100">{{ $payrolls->links('vendor.pagination.tailwind') }}</div>
        @endif
    </div>
</div>
