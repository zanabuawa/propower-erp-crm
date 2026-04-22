<div>
    <x-page-header title="Organigrama" description="Estructura jerárquica de la organización">
        <x-slot:actions>
            <div class="flex items-center gap-2">
                {{-- Toggle empleados (solo en vista departamentos) --}}
                @if($viewMode === 'departments')
                <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer select-none">
                    <div class="relative">
                        <input wire:model.live="showEmployees" type="checkbox" class="sr-only peer">
                        <div class="w-9 h-5 bg-slate-200 peer-checked:bg-indigo-500 rounded-full transition-colors"></div>
                        <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-4"></div>
                    </div>
                    Mostrar empleados
                </label>
                @endif

                {{-- Mode switcher --}}
                <div class="inline-flex rounded-lg border border-slate-200 overflow-hidden text-sm">
                    <button wire:click="$set('viewMode','departments')"
                            class="px-3 py-1.5 font-medium transition
                                {{ $viewMode === 'departments' ? 'bg-indigo-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-50' }}">
                        Por área
                    </button>
                    <button wire:click="$set('viewMode','supervisors')"
                            class="px-3 py-1.5 font-medium transition border-l border-slate-200
                                {{ $viewMode === 'supervisors' ? 'bg-indigo-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-50' }}">
                        Por supervisor
                    </button>
                </div>
            </div>
        </x-slot:actions>
    </x-page-header>

    {{-- CSS org chart lines --}}
    <style>
        .org-tree { display: flex; flex-direction: column; align-items: center; }
        .org-tree ul {
            display: flex;
            flex-direction: row;
            justify-content: center;
            padding: 0;
            margin: 0;
            list-style: none;
            position: relative;
            padding-top: 28px;
        }
        .org-tree ul::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 2px;
            height: 28px;
            background: #e2e8f0;
        }
        /* Root ul has no top connector */
        .org-tree > ul::before { display: none; }

        .org-tree li {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            padding: 0 12px;
        }
        /* Horizontal connector */
        .org-tree li::before,
        .org-tree li::after {
            content: '';
            position: absolute;
            top: 0;
            width: 50%;
            height: 2px;
            background: #e2e8f0;
        }
        .org-tree li::before { right: 50%; }
        .org-tree li::after  { left: 50%; }
        .org-tree li:only-child::before,
        .org-tree li:only-child::after { display: none; }
        .org-tree li:first-child::before { display: none; }
        .org-tree li:last-child::after  { display: none; }

        /* Vertical line from horizontal bar down to card */
        .org-card-wrap {
            position: relative;
            padding-top: 14px;
        }
        .org-card-wrap::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 2px;
            height: 14px;
            background: #e2e8f0;
        }
        /* Root node: no top line */
        .org-root > .org-card-wrap::before { display: none; }
    </style>

    <div class="overflow-x-auto pb-10 pt-4">
        @if($viewMode === 'departments')
            @if($roots->isEmpty())
            <div class="text-center py-16 text-slate-400 text-sm">
                No hay departamentos activos configurados.
                <a href="{{ route('hr.departments.index') }}" class="text-indigo-600 hover:underline ml-1">Crear departamentos</a>
            </div>
            @else
            <div class="org-tree min-w-max mx-auto px-8">
                <ul>
                    @foreach($roots as $dept)
                        @include('livewire.hr.partials.org-dept-node', ['dept' => $dept, 'showEmployees' => $showEmployees])
                    @endforeach
                </ul>
            </div>

            {{-- Employees with no department --}}
            @if($noDept->isNotEmpty() && $showEmployees)
            <div class="mt-8 max-w-3xl mx-auto">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3 px-4">Sin departamento asignado</p>
                <div class="flex flex-wrap gap-2 px-4">
                    @foreach($noDept as $emp)
                    <div class="inline-flex items-center gap-2 bg-white border border-slate-200 rounded-lg px-3 py-1.5 text-sm">
                        <div class="w-5 h-5 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 text-[9px] font-bold uppercase">
                            {{ substr($emp->first_name,0,1) }}{{ substr($emp->last_name,0,1) }}
                        </div>
                        <span class="text-slate-700">{{ $emp->first_name }} {{ $emp->last_name }}</span>
                        @if($emp->position)
                        <span class="text-slate-400 text-xs">{{ $emp->position->name }}</span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            @endif

        @else
            {{-- Supervisor view --}}
            @if($rootEmployees->isEmpty())
            <div class="text-center py-16 text-slate-400 text-sm">
                No hay empleados activos sin supervisor (raíces de la jerarquía).
            </div>
            @else
            <div class="org-tree min-w-max mx-auto px-8">
                <ul>
                    @foreach($rootEmployees as $emp)
                        @include('livewire.hr.partials.org-emp-node', ['emp' => $emp])
                    @endforeach
                </ul>
            </div>
            @endif
        @endif
    </div>
</div>
