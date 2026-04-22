<div>
    <x-page-header :title="$employee->full_name" description="Expediente del empleado">
        <x-slot:actions>
            @can('edit hr')
            <a href="{{ route('hr.employees.edit', $employee) }}" wire:navigate
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                Editar
            </a>
            @endcan
            <a href="{{ route('hr.employees.index') }}" wire:navigate
               class="inline-flex items-center gap-2 px-3 py-2 text-sm text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50">
                ← Volver
            </a>
        </x-slot:actions>
    </x-page-header>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    {{-- Header card --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5 mb-5">
        <div class="flex flex-wrap gap-5 items-start">
            {{-- Avatar --}}
            @if($employee->photo_url)
                <img src="{{ $employee->photo_url }}" alt="" class="w-20 h-20 rounded-xl object-cover">
            @else
                <div class="w-20 h-20 rounded-xl bg-indigo-100 flex items-center justify-center">
                    <span class="text-2xl font-bold text-indigo-600">
                        {{ strtoupper(substr($employee->first_name,0,1).substr($employee->last_name,0,1)) }}
                    </span>
                </div>
            @endif
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-3 mb-1">
                    <h2 class="text-xl font-bold text-slate-800">{{ $employee->full_name }}</h2>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $employee->status_color }}">
                        {{ $employee->status_label }}
                    </span>
                </div>
                <p class="text-sm text-slate-500">
                    {{ $employee->position?->name ?? '—' }}
                    @if($employee->department) · {{ $employee->department->name }} @endif
                    @if($employee->employee_number) · #{{ $employee->employee_number }} @endif
                </p>
                <div class="flex flex-wrap gap-4 mt-3 text-sm text-slate-600">
                    @if($employee->email)
                    <span class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        {{ $employee->email }}
                    </span>
                    @endif
                    @if($employee->phone)
                    <span class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        {{ $employee->phone }}
                    </span>
                    @endif
                    <span class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Ingreso: {{ $employee->hire_date->format('d/m/Y') }} ({{ $employee->antiquity_years }} años)
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex flex-wrap gap-1 mb-5 bg-white rounded-xl border border-slate-200 p-1.5">
        @foreach([
            'info' => 'Información',
            'contracts' => 'Contratos',
            'education' => 'Educación',
            'training' => 'Capacitación',
            'documents' => 'Documentos',
            'projects' => 'Proyectos',
            'leaves' => 'Permisos',
            'incidents' => 'Incidencias',
            'evaluations' => 'Evaluaciones',
            'payroll' => 'Nóminas',
            'movements' => 'Movimientos',
            'loans' => 'Préstamos/Bonos',
        ] as $tab => $label)
        <button wire:click="setTab('{{ $tab }}')"
                class="px-3 py-1.5 text-sm rounded-lg transition-colors
                       {{ $activeTab === $tab ? 'bg-indigo-600 text-white font-medium' : 'text-slate-600 hover:bg-slate-100' }}">
            {{ $label }}
        </button>
        @endforeach
    </div>

    {{-- Tab: Información --}}
    @if($activeTab === 'info')
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-4">Datos personales</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">CURP</dt><dd class="font-mono text-slate-700">{{ $employee->curp ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">RFC</dt><dd class="font-mono text-slate-700">{{ $employee->rfc ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">NSS</dt><dd class="font-mono text-slate-700">{{ $employee->nss ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Nacimiento</dt><dd class="text-slate-700">{{ $employee->birth_date?->format('d/m/Y') ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Domicilio</dt><dd class="text-slate-700 text-right max-w-[60%]">{{ collect([$employee->address, $employee->city, $employee->state, $employee->postal_code])->filter()->join(', ') ?: '—' }}</dd></div>
            </dl>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-4">Datos laborales</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">Tipo contrato</dt><dd class="text-slate-700">{{ \App\Models\HrEmployee::CONTRACT_TYPES[$employee->contract_type] ?? $employee->contract_type }}</dd></div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Clasificación</dt>
                    <dd class="text-slate-700">
                        @if($employee->is_external)
                            <span class="px-2 py-0.5 bg-orange-100 text-orange-700 rounded text-[10px] font-bold">EXTERNO</span>
                        @else
                            <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded text-[10px] font-bold">INTERNO</span>
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Jefe directo</dt>
                    <dd class="text-slate-700">{{ $employee->supervisor?->full_name ?? '—' }}</dd>
                </div>
                <div class="flex justify-between"><dt class="text-slate-500">Salario</dt><dd class="text-slate-700 font-medium">$ {{ number_format($employee->salary, 2) }} / {{ \App\Models\HrEmployee::SALARY_PERIODS[$employee->salary_period] ?? $employee->salary_period }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Salario diario</dt><dd class="text-slate-700">$ {{ number_format($employee->daily_salary, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Turno</dt><dd class="text-slate-700">{{ \App\Models\HrEmployee::WORK_SHIFTS[$employee->work_shift] ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Sucursal</dt><dd class="text-slate-700">{{ $employee->branch?->name ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">SDI (IMSS)</dt><dd class="text-slate-700">$ {{ number_format($employee->daily_salary_imss ?? 0, 2) }}</dd></div>
            </dl>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-4">Datos bancarios</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">Forma de pago</dt><dd class="text-slate-700">{{ \App\Models\HrEmployee::PAYMENT_METHODS[$employee->payment_method] ?? $employee->payment_method }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Banco</dt><dd class="text-slate-700">{{ $employee->bank ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Cuenta</dt><dd class="font-mono text-slate-700">{{ $employee->bank_account ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">CLABE</dt><dd class="font-mono text-slate-700">{{ $employee->clabe ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">INFONAVIT</dt><dd class="text-slate-700">{{ $employee->infonavit_credit ?? '—' }}</dd></div>
            </dl>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-4">Contacto de emergencia</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">Nombre</dt><dd class="text-slate-700">{{ $employee->emergency_contact_name ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Teléfono</dt><dd class="text-slate-700">{{ $employee->emergency_contact_phone ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Parentesco</dt><dd class="text-slate-700">{{ $employee->emergency_contact_relationship ?? '—' }}</dd></div>
            </dl>
            @if($employee->vacationBalances->isNotEmpty())
            <div class="mt-4 pt-4 border-t border-slate-100">
                <h3 class="text-sm font-semibold text-slate-700 mb-3">Saldo de vacaciones {{ now()->year }}</h3>
                @php $vb = $employee->vacationBalances->first(); @endphp
                <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="bg-green-50 rounded-lg p-2">
                        <p class="text-lg font-bold text-green-700">{{ $vb->days_earned }}</p>
                        <p class="text-xs text-green-600">Ganados</p>
                    </div>
                    <div class="bg-red-50 rounded-lg p-2">
                        <p class="text-lg font-bold text-red-700">{{ $vb->days_used }}</p>
                        <p class="text-xs text-red-600">Usados</p>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-2">
                        <p class="text-lg font-bold text-blue-700">{{ $vb->days_available }}</p>
                        <p class="text-xs text-blue-600">Disponibles</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Tab: Contratos --}}
    @if($activeTab === 'contracts')
    <div class="bg-white rounded-xl border border-slate-200">
        <div class="flex items-center justify-between p-4 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-slate-700">Historial de contratos</h3>
            @can('create hr')
            <a href="{{ route('hr.contracts.index') }}" wire:navigate
               class="text-xs text-indigo-600 hover:text-indigo-800">Ver todos →</a>
            @endcan
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($employee->contracts as $contract)
            <div class="px-4 py-3 flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-slate-700">
                        {{ \App\Models\HrContract::TYPES[$contract->type] ?? $contract->type }}
                        @if($contract->contract_number) <span class="text-slate-400">#{{ $contract->contract_number }}</span> @endif
                    </p>
                    <p class="text-xs text-slate-400">{{ $contract->start_date->format('d/m/Y') }} {{ $contract->end_date ? '→ '.$contract->end_date->format('d/m/Y') : '(indefinido)' }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">$ {{ number_format($contract->salary, 2) }} / {{ \App\Models\HrEmployee::SALARY_PERIODS[$contract->salary_period] ?? $contract->salary_period }}</p>
                </div>
                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $contract->status_color }}">{{ $contract->status_label }}</span>
            </div>
            @empty
            <div class="px-4 py-8 text-center text-sm text-slate-400">Sin contratos registrados</div>
            @endforelse
        </div>
    </div>
    @endif

    {{-- Tab: Proyectos --}}
    @if($activeTab === 'projects')
    <div class="bg-white rounded-xl border border-slate-200">
        <div class="p-4 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-slate-700">Proyectos asignados</h3>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($employee->projects as $project)
            @php $statusColors = ['borrador'=>'bg-gray-100 text-gray-600','activo'=>'bg-green-100 text-green-700','pausado'=>'bg-yellow-100 text-yellow-700','completado'=>'bg-blue-100 text-blue-700','cancelado'=>'bg-red-100 text-red-700']; @endphp
            <div class="px-4 py-3 flex items-start justify-between gap-3">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <a href="{{ route('projects.show', $project) }}" wire:navigate
                           class="text-sm font-medium text-indigo-600 hover:text-indigo-800">{{ $project->name }}</a>
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$project->status] ?? 'bg-gray-100 text-gray-600' }} capitalize">
                            {{ $project->status }}
                        </span>
                        @if(!$project->pivot->is_active)
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">Inactivo</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $project->code }}</p>
                    @if($project->pivot->role)
                    <p class="text-xs text-slate-600 mt-0.5">Rol: {{ $project->pivot->role }}</p>
                    @endif
                    <p class="text-xs text-slate-400 mt-0.5">
                        @if($project->pivot->start_date)
                            {{ \Carbon\Carbon::parse($project->pivot->start_date)->format('d/m/Y') }}
                            @if($project->pivot->end_date) → {{ \Carbon\Carbon::parse($project->pivot->end_date)->format('d/m/Y') }} @endif
                        @endif
                        @if($project->pivot->hours_assigned)
                            · {{ number_format($project->pivot->hours_assigned, 1) }} hrs asignadas
                        @endif
                    </p>
                </div>
                <div class="text-right flex-shrink-0">
                    <div class="w-12 h-12 relative">
                        <svg class="w-12 h-12 -rotate-90" viewBox="0 0 36 36">
                            <circle cx="18" cy="18" r="15.9" fill="none" stroke="#e2e8f0" stroke-width="3"/>
                            <circle cx="18" cy="18" r="15.9" fill="none" stroke="#6366f1" stroke-width="3"
                                    stroke-dasharray="{{ $project->progress }}, 100"
                                    stroke-linecap="round"/>
                        </svg>
                        <span class="absolute inset-0 flex items-center justify-center text-xs font-bold text-slate-700">
                            {{ $project->progress }}%
                        </span>
                    </div>
                </div>
            </div>
            @empty
            <div class="px-4 py-8 text-center text-sm text-slate-400">Sin proyectos asignados</div>
            @endforelse
        </div>
    </div>
    @endif

    {{-- Tab: Permisos --}}
    @if($activeTab === 'leaves')
    <div class="bg-white rounded-xl border border-slate-200">
        <div class="flex items-center justify-between p-4 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-slate-700">Permisos y bajas temporales</h3>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($employee->leaves as $leave)
            <div class="px-4 py-3 flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-slate-700">{{ \App\Models\HrLeave::TYPES[$leave->type] ?? $leave->type }}</p>
                    <p class="text-xs text-slate-400">{{ $leave->start_date->format('d/m/Y') }} → {{ $leave->end_date->format('d/m/Y') }} ({{ $leave->business_days }} días hábiles)</p>
                    @if($leave->reason) <p class="text-xs text-slate-500">{{ $leave->reason }}</p> @endif
                </div>
                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $leave->status_color }}">{{ \App\Models\HrLeave::STATUSES[$leave->status] ?? $leave->status }}</span>
            </div>
            @empty
            <div class="px-4 py-8 text-center text-sm text-slate-400">Sin permisos registrados</div>
            @endforelse
        </div>
    </div>
    @endif

    {{-- Tab: Incidencias --}}
    @if($activeTab === 'incidents')
    <div class="bg-white rounded-xl border border-slate-200">
        <div class="p-4 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-slate-700">Registro de incidencias</h3>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($employee->incidents as $incident)
            <div class="px-4 py-3 flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-slate-700">{{ $incident->type_label }}</p>
                    <p class="text-xs text-slate-400">{{ $incident->incident_date->format('d/m/Y') }}</p>
                    <p class="text-xs text-slate-600 mt-0.5">{{ Str::limit($incident->description, 100) }}</p>
                </div>
                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $incident->severity_color }}">
                    {{ \App\Models\HrIncident::SEVERITIES[$incident->severity] ?? $incident->severity }}
                </span>
            </div>
            @empty
            <div class="px-4 py-8 text-center text-sm text-slate-400">Sin incidencias registradas</div>
            @endforelse
        </div>
    </div>
    @endif

    {{-- Tab: Evaluaciones --}}
    @if($activeTab === 'evaluations')
    <div class="bg-white rounded-xl border border-slate-200">
        <div class="p-4 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-slate-700">Evaluaciones de desempeño</h3>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($employee->evaluations as $ev)
            <div class="px-4 py-3 flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-slate-700">Periodo: {{ $ev->period }}</p>
                    <p class="text-xs text-slate-400">{{ $ev->evaluation_date->format('d/m/Y') }} · Evaluador: {{ $ev->evaluator?->name ?? '—' }}</p>
                </div>
                <div class="text-right">
                    <p class="text-lg font-bold {{ $ev->score_color }}">{{ $ev->overall_score }}</p>
                    <p class="text-xs text-slate-400">{{ $ev->score_label }}</p>
                </div>
            </div>
            @empty
            <div class="px-4 py-8 text-center text-sm text-slate-400">Sin evaluaciones registradas</div>
            @endforelse
        </div>
    </div>
    @endif

    {{-- Tab: Nóminas --}}
    @if($activeTab === 'payroll')
    <div class="bg-white rounded-xl border border-slate-200">
        <div class="p-4 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-slate-700">Historial de nómina</h3>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($recentPayrolls as $item)
            <div class="px-4 py-3 flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-slate-700">{{ $item->payroll->period_label }}</p>
                    <p class="text-xs text-slate-400">{{ $item->payroll->folio }} · {{ \App\Models\HrPayroll::PERIOD_TYPES[$item->payroll->period_type] ?? '' }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-semibold text-slate-800">$ {{ number_format($item->net_salary, 2) }}</p>
                    <p class="text-xs text-slate-400">Bruto: ${{ number_format($item->gross_salary, 2) }}</p>
                </div>
            </div>
            @empty
            <div class="px-4 py-8 text-center text-sm text-slate-400">Sin nóminas registradas</div>
            @endforelse
        </div>
    </div>
    @endif

    {{-- Tab: Educación --}}
    @if($activeTab === 'education')
    <div class="bg-white rounded-xl border border-slate-200">
        <div class="p-4 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-sm font-semibold text-slate-700">Historial Académico</h3>
            <button wire:click="$dispatchTo('h-r.employee-expedient', 'openExpedientModal', { type: 'education' })"
                    class="text-xs font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 px-2 py-1 rounded">
                + AGREGAR
            </button>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($employee->education as $edu)
            <div class="px-4 py-3 flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-slate-700">{{ $edu->degree }} - {{ $edu->field_of_study }}</p>
                    <p class="text-xs text-slate-500">{{ $edu->institution }}</p>
                    <p class="text-xs text-slate-400">
                        {{ $edu->start_date?->format('Y') ?? '?' }} - {{ $edu->is_completed ? ($edu->end_date?->format('Y') ?? 'Finalizado') : 'En curso' }}
                    </p>
                </div>
                @if($edu->certificate_path)
                <a href="{{ Storage::url($edu->certificate_path) }}" target="_blank" class="text-xs text-indigo-600 hover:underline">Ver Certificado</a>
                @endif
            </div>
            @empty
            <div class="px-4 py-8 text-center text-sm text-slate-400">Sin registros académicos</div>
            @endforelse
        </div>
    </div>
    @endif

    {{-- Tab: Capacitación --}}
    @if($activeTab === 'training')
    <div class="bg-white rounded-xl border border-slate-200">
        <div class="p-4 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-sm font-semibold text-slate-700">Historial de Capacitación</h3>
            <button wire:click="$dispatchTo('h-r.employee-expedient', 'openExpedientModal', { type: 'training' })"
                    class="text-xs font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 px-2 py-1 rounded">
                + REGISTRAR
            </button>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($employee->trainings as $training)
            <div class="px-4 py-3 flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-slate-700">{{ $training->course->name }}</p>
                    <p class="text-xs text-slate-500">{{ $training->course->provider }} · {{ $training->course->duration_hours }} hrs</p>
                    <p class="text-xs text-slate-400">Completado: {{ $training->completion_date?->format('d/m/Y') ?? '—' }}</p>
                    @if($training->expiry_date)
                    <p class="text-xs {{ $training->expiry_date->isPast() ? 'text-red-500 font-bold' : 'text-orange-500' }}">
                        Vence: {{ $training->expiry_date->format('d/m/Y') }}
                    </p>
                    @endif
                </div>
                <div class="text-right">
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-700 uppercase">{{ $training->status }}</span>
                    @if($training->certificate_path)
                    <div class="mt-2 text-xs text-indigo-600 hover:underline">
                        <a href="{{ Storage::url($training->certificate_path) }}" target="_blank">Certificado / DC3</a>
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="px-4 py-8 text-center text-sm text-slate-400">Sin capacitaciones registradas</div>
            @endforelse
        </div>
    </div>
    @endif

    {{-- Tab: Documentos --}}
    @if($activeTab === 'documents')
    <div class="bg-white rounded-xl border border-slate-200">
        <div class="p-4 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-sm font-semibold text-slate-700">Expediente Digital (Adjuntos)</h3>
            <button wire:click="$dispatchTo('h-r.employee-expedient', 'openExpedientModal', { type: 'document' })"
                    class="text-xs font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 px-2 py-1 rounded">
                + SUBIR DOCUMENTO
            </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-4">
            @forelse($employee->documents as $doc)
            <div class="p-3 border border-slate-100 rounded-lg bg-slate-50 flex items-start gap-3">
                <div class="w-10 h-10 rounded bg-indigo-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-slate-700 truncate">{{ $doc->document_type }}</p>
                    @if($doc->expiry_date)
                    <p class="text-[10px] {{ $doc->expiry_date->isPast() ? 'text-red-500 font-bold' : 'text-slate-400' }}">
                        Vence: {{ $doc->expiry_date->format('d/m/Y') }}
                    </p>
                    @endif
                    <div class="mt-2 flex gap-2">
                        <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="text-xs text-indigo-600 hover:underline font-medium">Ver</a>
                        <a href="{{ Storage::url($doc->file_path) }}" download class="text-xs text-slate-500 hover:underline">Descargar</a>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full py-8 text-center text-sm text-slate-400">Sin documentos digitales cargados</div>
            @endforelse
        </div>
    </div>
    @endif

    {{-- Tab: Movimientos --}}
    @if($activeTab === 'movements')
    <div class="bg-white rounded-xl border border-slate-200">
        <div class="p-4 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-sm font-semibold text-slate-700">Historial de movimientos laborales</h3>
            @can('edit hr')
            <button wire:click="openMovementModal"
                    class="text-xs font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 px-2 py-1 rounded">
                + REGISTRAR MOVIMIENTO
            </button>
            @endcan
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($employee->movements->sortByDesc('effective_date') as $mov)
            <div class="px-4 py-3 flex items-start gap-4">
                {{-- Línea de tiempo --}}
                <div class="flex flex-col items-center pt-1">
                    <div class="w-2.5 h-2.5 rounded-full bg-indigo-400 flex-shrink-0"></div>
                    @if(!$loop->last)
                    <div class="w-px flex-1 bg-slate-200 mt-1 min-h-[24px]"></div>
                    @endif
                </div>
                <div class="flex-1 pb-2">
                    <div class="flex flex-wrap items-center gap-2 mb-0.5">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                            {{ \App\Models\HrEmployeeMovement::TYPE_COLORS[$mov->movement_type] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ \App\Models\HrEmployeeMovement::TYPES[$mov->movement_type] ?? $mov->movement_type }}
                        </span>
                        <span class="text-xs text-slate-500">{{ $mov->effective_date->format('d/m/Y') }}</span>
                        @if($mov->registeredBy)
                        <span class="text-xs text-slate-400">· por {{ $mov->registeredBy->name }}</span>
                        @endif
                    </div>

                    {{-- Detalle de cambios --}}
                    @if($mov->previous_value || $mov->new_value)
                    <div class="text-xs text-slate-500 mt-1 space-y-0.5">
                        @if(isset($mov->new_value['position_name']))
                        <p>Puesto: <span class="text-slate-400 line-through">{{ $mov->previous_value['position_name'] ?? '—' }}</span>
                           → <span class="font-medium text-slate-700">{{ $mov->new_value['position_name'] }}</span></p>
                        @endif
                        @if(isset($mov->new_value['department_name']))
                        <p>Área: <span class="text-slate-400 line-through">{{ $mov->previous_value['department_name'] ?? '—' }}</span>
                           → <span class="font-medium text-slate-700">{{ $mov->new_value['department_name'] }}</span></p>
                        @endif
                        @if(isset($mov->new_value['salary']))
                        <p>Salario: <span class="text-slate-400 line-through">${{ number_format($mov->previous_value['salary'] ?? 0, 2) }}</span>
                           → <span class="font-medium text-slate-700">${{ number_format($mov->new_value['salary'], 2) }}</span></p>
                        @endif
                        @if(isset($mov->new_value['status']))
                        <p>Estatus: <span class="text-slate-400 line-through">{{ \App\Models\HrEmployee::STATUSES[$mov->previous_value['status'] ?? ''] ?? '—' }}</span>
                           → <span class="font-medium text-slate-700">{{ \App\Models\HrEmployee::STATUSES[$mov->new_value['status']] ?? $mov->new_value['status'] }}</span></p>
                        @endif
                        @if(isset($mov->new_value['contract_type']))
                        <p>Contrato: <span class="text-slate-400 line-through">{{ \App\Models\HrEmployee::CONTRACT_TYPES[$mov->previous_value['contract_type'] ?? ''] ?? '—' }}</span>
                           → <span class="font-medium text-slate-700">{{ \App\Models\HrEmployee::CONTRACT_TYPES[$mov->new_value['contract_type']] ?? $mov->new_value['contract_type'] }}</span></p>
                        @endif
                    </div>
                    @endif

                    @if($mov->notes)
                    <p class="text-xs text-slate-400 mt-1 italic">{{ $mov->notes }}</p>
                    @endif
                </div>
            </div>
            @empty
            <div class="px-4 py-8 text-center text-sm text-slate-400">Sin movimientos registrados</div>
            @endforelse
        </div>
    </div>
    @endif

    {{-- Modal: Registrar movimiento --}}
    @if($showMovementModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-slate-800">Registrar movimiento laboral</h3>
                <button wire:click="$set('showMovementModal', false)" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Tipo de movimiento <span class="text-red-400">*</span></label>
                        <select wire:model.live="movementType"
                                class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                            @foreach(\App\Models\HrEmployeeMovement::TYPES as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Fecha efectiva <span class="text-red-400">*</span></label>
                        <input wire:model="movementDate" type="date"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    </div>
                </div>

                {{-- Campos según tipo --}}
                @if(in_array($movementType, ['ascenso', 'descenso', 'traslado']))
                <div class="grid grid-cols-2 gap-4 p-3 bg-slate-50 rounded-lg">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Nuevo puesto</label>
                        <select wire:model="newPositionId"
                                class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                            <option value="">Sin cambio</option>
                            @foreach($positionOptions as $p)
                            <option value="{{ $p['id'] }}">{{ $p['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Nueva área</label>
                        <select wire:model="newDepartmentId"
                                class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                            <option value="">Sin cambio</option>
                            @foreach($departmentOptions as $d)
                            <option value="{{ $d['id'] }}">{{ $d['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                @if($movementType === 'cambio_salario')
                <div class="p-3 bg-slate-50 rounded-lg">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Nuevo salario</label>
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-slate-500">Actual: ${{ number_format($employee->salary, 2) }}</span>
                        <span class="text-slate-300">→</span>
                        <input wire:model="newSalary" type="number" min="0" step="0.01"
                               class="flex-1 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                               placeholder="Nuevo monto">
                    </div>
                </div>
                @endif

                @if($movementType === 'cambio_contrato')
                <div class="p-3 bg-slate-50 rounded-lg">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Nuevo tipo de contrato</label>
                    <select wire:model="newContractType"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                        <option value="">Seleccionar...</option>
                        @foreach(\App\Models\HrEmployee::CONTRACT_TYPES as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Notas / Motivo</label>
                    <textarea wire:model="movementNotes" rows="2"
                              class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 resize-none"
                              placeholder="Motivo del movimiento, referencias, etc."></textarea>
                </div>

                @error('movementType') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                @error('movementDate') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3 mt-5">
                <button wire:click="saveMovement" wire:loading.attr="disabled"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    <span wire:loading.remove wire:target="saveMovement">Guardar movimiento</span>
                    <span wire:loading wire:target="saveMovement">Guardando...</span>
                </button>
                <button wire:click="$set('showMovementModal', false)"
                        class="flex-1 text-slate-600 text-sm font-medium px-4 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 transition">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Tab: Préstamos y Bonos --}}
    @if($activeTab === 'loans')
    <livewire:h-r.employee-loan-bonus :employee="$employee" />
    @endif

    <livewire:h-r.employee-expedient :employee="$employee" />
</div>
