<div>
    <x-page-header :title="'Nómina: '.$payroll->folio" description="Detalle y timbrado de nómina">
        <x-slot:actions>
            <a href="{{ route('hr.payrolls.index') }}" wire:navigate
               class="inline-flex items-center gap-2 px-3 py-2 text-sm text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50">
                ← Volver
            </a>
        </x-slot:actions>
    </x-page-header>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">{{ session('error') }}</div>
    @endif
    @if(session('warning'))
        <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 text-yellow-700 rounded-lg text-sm">{{ session('warning') }}</div>
    @endif

    {{-- Encabezado nómina --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5 mb-5">
        <div class="flex flex-wrap gap-6 items-start justify-between">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <h2 class="text-lg font-bold text-slate-800">{{ $payroll->folio }}</h2>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $payroll->status_color }}">{{ $payroll->status_label }}</span>
                </div>
                <p class="text-sm text-slate-500">{{ \App\Models\HrPayroll::PERIOD_TYPES[$payroll->period_type] ?? '' }} · {{ $payroll->period_label }}</p>
                <p class="text-xs text-slate-400 mt-1">Creada por {{ $payroll->createdBy?->name ?? '—' }} el {{ $payroll->created_at->format('d/m/Y H:i') }}</p>
                @if($payroll->approvedBy)
                <p class="text-xs text-slate-400">Aprobada por {{ $payroll->approvedBy->name }} · {{ $payroll->approved_at?->format('d/m/Y H:i') }}</p>
                @endif
            </div>
            <div class="flex flex-wrap gap-2">
                @can('edit hr')
                @if($payroll->status === 'calculated')
                    @if(!$confirmApprove)
                    <button wire:click="$set('confirmApprove', true)"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg">
                        Aprobar nómina
                    </button>
                    @else
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-slate-600">¿Confirmar aprobación?</span>
                        <button wire:click="approve" class="px-3 py-1.5 bg-green-600 text-white text-xs rounded-lg hover:bg-green-700">Sí, aprobar</button>
                        <button wire:click="$set('confirmApprove', false)" class="px-3 py-1.5 bg-slate-200 text-slate-600 text-xs rounded-lg">Cancelar</button>
                    </div>
                    @endif
                @endif

                @if($payroll->status === 'approved')
                    @if(!$confirmPay)
                    <button wire:click="$set('confirmPay', true)"
                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg">
                        Marcar como pagada
                    </button>
                    @else
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-slate-600">¿Confirmar pago?</span>
                        <button wire:click="markPaid" class="px-3 py-1.5 bg-green-600 text-white text-xs rounded-lg hover:bg-green-700">Sí, pagada</button>
                        <button wire:click="$set('confirmPay', false)" class="px-3 py-1.5 bg-slate-200 text-slate-600 text-xs rounded-lg">Cancelar</button>
                    </div>
                    @endif
                @endif
                @endcan

                @can('stamp invoices')
                @if(in_array($payroll->status, ['approved', 'paid']))
                <button wire:click="stampWithFacturapi"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg">
                    <span wire:loading.remove wire:target="stampWithFacturapi">Timbrar CFDIs nómina</span>
                    <span wire:loading wire:target="stampWithFacturapi">Timbrando...</span>
                </button>
                @endif
                @endcan
            </div>
        </div>
    </div>

    {{-- Totales --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-5">
        <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
            <p class="text-2xl font-bold text-slate-800">{{ $payroll->total_employees }}</p>
            <p class="text-xs text-slate-500">Empleados</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
            <p class="text-lg font-bold text-slate-700">$ {{ number_format($payroll->total_gross, 2) }}</p>
            <p class="text-xs text-slate-500">Total bruto</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
            <p class="text-lg font-bold text-red-600">$ {{ number_format($payroll->total_deductions, 2) }}</p>
            <p class="text-xs text-slate-500">Deducciones</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
            <p class="text-lg font-bold text-green-600">$ {{ number_format($payroll->total_net, 2) }}</p>
            <p class="text-xs text-slate-500">Total neto</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
            <p class="text-lg font-bold text-orange-600">$ {{ number_format($payroll->total_employer_imss, 2) }}</p>
            <p class="text-xs text-slate-500">IMSS patronal</p>
        </div>
    </div>

    {{-- Detalle por empleado --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-x-auto">
        <div class="p-4 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-slate-700">Detalle por empleado</h3>
        </div>
        <table class="w-full text-xs">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/60">
                    <th class="text-left px-3 py-2.5 font-semibold text-slate-500 uppercase">Empleado</th>
                    <th class="text-right px-3 py-2.5 font-semibold text-slate-500 uppercase">Días</th>
                    <th class="text-right px-3 py-2.5 font-semibold text-slate-500 uppercase">Bruto</th>
                    <th class="text-right px-3 py-2.5 font-semibold text-slate-500 uppercase">ISR</th>
                    <th class="text-right px-3 py-2.5 font-semibold text-slate-500 uppercase">IMSS</th>
                    <th class="text-right px-3 py-2.5 font-semibold text-slate-500 uppercase">Deducciones</th>
                    <th class="text-right px-3 py-2.5 font-semibold text-slate-500 uppercase">Neto</th>
                    <th class="text-center px-3 py-2.5 font-semibold text-slate-500 uppercase">CFDI</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($payroll->items as $item)
                <tr class="hover:bg-slate-50/50">
                    <td class="px-3 py-2.5">
                        <p class="font-medium text-slate-800">{{ $item->employee?->full_name ?? '—' }}</p>
                        <p class="text-slate-400">{{ $item->employee?->department?->name ?? '' }}</p>
                    </td>
                    <td class="px-3 py-2.5 text-right text-slate-600">{{ $item->days_worked }}</td>
                    <td class="px-3 py-2.5 text-right font-medium text-blue-700">$ {{ number_format($item->gross_salary, 2) }}</td>
                    <td class="px-3 py-2.5 text-right text-red-600">$ {{ number_format($item->ispt, 2) }}</td>
                    <td class="px-3 py-2.5 text-right text-red-600">$ {{ number_format($item->imss_employee, 2) }}</td>
                    <td class="px-3 py-2.5 text-right text-red-700">$ {{ number_format($item->total_deductions, 2) }}</td>
                    <td class="px-3 py-2.5 text-right font-bold text-green-700">$ {{ number_format($item->net_salary, 2) }}</td>
                    <td class="px-3 py-2.5 text-center">
                        @if($item->status === 'stamped')
                            <span class="px-2 py-0.5 rounded-full text-xs bg-emerald-100 text-emerald-700">Timbrado</span>
                        @elseif($item->status === 'error')
                            <span class="px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-700" title="{{ $item->stamp_error }}">Error</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-500">Pendiente</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
