<div>
    <x-page-header title="Nueva nómina" description="Calculadora de nómina con ISPT e IMSS">
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

    {{-- Configuración del periodo --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5 mb-5">
        <h3 class="text-sm font-semibold text-slate-700 mb-4">Periodo de nómina</h3>
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Tipo de nómina</label>
                <select wire:model.live="period_type"
                        class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    @foreach(\App\Models\HrPayroll::PERIOD_TYPES as $k => $v)
                        <option value="{{ $k }}">{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Fecha inicio <span class="text-red-500">*</span></label>
                <input wire:model="period_start" type="date"
                       class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                @error('period_start') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Fecha fin <span class="text-red-500">*</span></label>
                <input wire:model="period_end" type="date"
                       class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                @error('period_end') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex items-end">
                <button wire:click="calculate"
                        class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <span wire:loading.remove wire:target="calculate">Calcular nómina</span>
                    <span wire:loading wire:target="calculate">Calculando...</span>
                </button>
            </div>
        </div>
        <div class="mt-3">
            <label class="block text-xs font-medium text-slate-600 mb-1">Notas</label>
            <input wire:model="notes" type="text" placeholder="Observaciones internas..."
                   class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
        </div>
    </div>

    @if($calculated && !empty($items))

    {{-- Resumen --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">
        <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
            <p class="text-2xl font-bold text-slate-800">{{ $totals['employees'] }}</p>
            <p class="text-xs text-slate-500">Empleados</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
            <p class="text-lg font-bold text-slate-700">$ {{ number_format($totals['gross'], 2) }}</p>
            <p class="text-xs text-slate-500">Total bruto</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
            <p class="text-lg font-bold text-red-600">- $ {{ number_format($totals['deductions'], 2) }}</p>
            <p class="text-xs text-slate-500">Total deducciones</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
            <p class="text-lg font-bold text-green-600">$ {{ number_format($totals['net'], 2) }}</p>
            <p class="text-xs text-slate-500">Total neto a pagar</p>
        </div>
    </div>

    {{-- Tabla editable de empleados --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-x-auto mb-5">
        <table class="w-full text-xs">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/60">
                    <th class="text-left px-3 py-2.5 font-semibold text-slate-500 uppercase sticky left-0 bg-slate-50/60">Empleado</th>
                    <th class="text-right px-3 py-2.5 font-semibold text-slate-500 uppercase">Días</th>
                    <th class="text-right px-3 py-2.5 font-semibold text-slate-500 uppercase">Sal. diario</th>
                    <th class="text-right px-3 py-2.5 font-semibold text-slate-500 uppercase">Horas extra</th>
                    <th class="text-right px-3 py-2.5 font-semibold text-slate-500 uppercase">Vales</th>
                    <th class="text-right px-3 py-2.5 font-semibold text-slate-500 uppercase text-blue-600">Bruto</th>
                    <th class="text-right px-3 py-2.5 font-semibold text-slate-500 uppercase">ISR</th>
                    <th class="text-right px-3 py-2.5 font-semibold text-slate-500 uppercase">IMSS</th>
                    <th class="text-right px-3 py-2.5 font-semibold text-slate-500 uppercase">INFONAVIT</th>
                    <th class="text-right px-3 py-2.5 font-semibold text-slate-500 uppercase text-green-600">Neto</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($items as $empId => $item)
                <tr class="hover:bg-slate-50/50">
                    <td class="px-3 py-2.5 sticky left-0 bg-white">
                        <p class="font-medium text-slate-800 whitespace-nowrap">{{ $item['full_name'] }}</p>
                        <p class="text-slate-400">{{ $item['department'] }} · {{ $item['position'] }}</p>
                    </td>
                    <td class="px-3 py-2.5 text-right">
                        <input wire:change="updateItemField({{ $empId }}, 'days_worked', $event.target.value)"
                               type="number" step="0.5" min="0" value="{{ $item['days_worked'] }}"
                               class="w-14 text-right text-xs px-1.5 py-1 border border-slate-200 rounded focus:outline-none focus:ring-1 focus:ring-indigo-400">
                    </td>
                    <td class="px-3 py-2.5 text-right text-slate-600">${{ number_format($item['daily_salary'], 2) }}</td>
                    <td class="px-3 py-2.5 text-right">
                        <input wire:change="updateItemField({{ $empId }}, 'overtime_hours', $event.target.value)"
                               type="number" step="0.5" min="0" value="{{ $item['overtime_hours'] }}"
                               class="w-14 text-right text-xs px-1.5 py-1 border border-slate-200 rounded focus:outline-none focus:ring-1 focus:ring-indigo-400">
                    </td>
                    <td class="px-3 py-2.5 text-right">
                        <input wire:change="updateItemField({{ $empId }}, 'food_voucher', $event.target.value)"
                               type="number" step="0.01" min="0" value="{{ $item['food_voucher'] }}"
                               class="w-16 text-right text-xs px-1.5 py-1 border border-slate-200 rounded focus:outline-none focus:ring-1 focus:ring-indigo-400">
                    </td>
                    <td class="px-3 py-2.5 text-right font-semibold text-blue-700">${{ number_format($item['gross_salary'], 2) }}</td>
                    <td class="px-3 py-2.5 text-right text-red-600">
                        <input wire:change="updateItemField({{ $empId }}, 'ispt', $event.target.value)"
                               type="number" step="0.01" min="0" value="{{ $item['ispt'] }}"
                               class="w-16 text-right text-xs px-1.5 py-1 border border-slate-200 rounded focus:outline-none focus:ring-1 focus:ring-indigo-400">
                    </td>
                    <td class="px-3 py-2.5 text-right text-red-600">
                        <input wire:change="updateItemField({{ $empId }}, 'imss_employee', $event.target.value)"
                               type="number" step="0.01" min="0" value="{{ $item['imss_employee'] }}"
                               class="w-16 text-right text-xs px-1.5 py-1 border border-slate-200 rounded focus:outline-none focus:ring-1 focus:ring-indigo-400">
                    </td>
                    <td class="px-3 py-2.5 text-right text-red-600">
                        <input wire:change="updateItemField({{ $empId }}, 'infonavit_payment', $event.target.value)"
                               type="number" step="0.01" min="0" value="{{ $item['infonavit_payment'] }}"
                               class="w-16 text-right text-xs px-1.5 py-1 border border-slate-200 rounded focus:outline-none focus:ring-1 focus:ring-indigo-400">
                    </td>
                    <td class="px-3 py-2.5 text-right font-bold text-green-700">${{ number_format($item['net_salary'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="flex justify-end gap-3">
        <button wire:click="calculate"
                class="px-4 py-2 text-sm text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-50">
            Recalcular
        </button>
        <button wire:click="save"
                class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
            <span wire:loading.remove wire:target="save">Guardar nómina</span>
            <span wire:loading wire:target="save">Guardando...</span>
        </button>
    </div>

    @elseif($calculated)
    <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 rounded-xl p-4 text-sm">
        No hay empleados activos para calcular nómina.
    </div>
    @endif

    @error('items')
    <div class="mt-3 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">{{ $message }}</div>
    @enderror
</div>
