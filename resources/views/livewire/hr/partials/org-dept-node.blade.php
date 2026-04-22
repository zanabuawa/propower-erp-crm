{{-- Variables: $dept (HrDepartment), $showEmployees (bool) --}}
@php
    $filledCount = $dept->employees->count();
    $hasChildren = $dept->children->isNotEmpty();
    $hasEmps     = $showEmployees && $filledCount > 0;
@endphp

<li>
    <div x-data="{ open: true }">
        {{-- Vertical connector + Card --}}
        <div class="org-card-wrap">
            <div class="org-card bg-white border border-slate-200 rounded-xl shadow-sm w-52 text-left overflow-hidden">
                <div class="h-1.5 bg-indigo-500"></div>
                <div class="p-3">
                    {{-- Name + headcount badge --}}
                    <div class="flex items-start justify-between gap-1 mb-1">
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-slate-800 leading-tight truncate" title="{{ $dept->name }}">
                                {{ $dept->name }}
                            </p>
                            @if($dept->code)
                            <p class="text-[10px] text-slate-400 uppercase tracking-wide">{{ $dept->code }}</p>
                            @endif
                        </div>
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-indigo-50 text-indigo-600 text-xs font-bold flex items-center justify-center">
                            {{ $filledCount }}
                        </span>
                    </div>

                    {{-- Manager --}}
                    @if($dept->manager)
                    <div class="flex items-center gap-1.5 mt-2 pt-2 border-t border-slate-100">
                        <div class="w-5 h-5 rounded-full bg-indigo-100 flex-shrink-0 flex items-center justify-center text-indigo-600 text-[9px] font-bold uppercase">
                            {{ substr($dept->manager->first_name,0,1) }}{{ substr($dept->manager->last_name,0,1) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] text-slate-400 leading-none">Responsable</p>
                            <p class="text-[11px] font-medium text-slate-600 truncate">
                                {{ $dept->manager->first_name }} {{ $dept->manager->last_name }}
                            </p>
                        </div>
                    </div>
                    @endif

                    {{-- Employee list --}}
                    @if($hasEmps)
                    <div class="mt-2 pt-2 border-t border-slate-100 space-y-1">
                        @foreach($dept->employees->take(5) as $emp)
                        <div class="flex items-center gap-1.5">
                            <div class="w-4 h-4 rounded-full bg-slate-100 flex-shrink-0 flex items-center justify-center text-slate-500 text-[8px] font-bold uppercase">
                                {{ substr($emp->first_name,0,1) }}
                            </div>
                            <p class="text-[11px] text-slate-600 truncate leading-tight">
                                {{ $emp->first_name }} {{ $emp->last_name }}
                                @if($emp->position)
                                <span class="text-slate-400">· {{ $emp->position->name }}</span>
                                @endif
                            </p>
                        </div>
                        @endforeach
                        @if($filledCount > 5)
                        <p class="text-[10px] text-slate-400 pl-5">+{{ $filledCount - 5 }} más</p>
                        @endif
                    </div>
                    @endif

                    {{-- Collapse toggle --}}
                    @if($hasChildren)
                    <button @click="open = !open"
                            class="mt-2 w-full text-center text-[10px] text-indigo-400 hover:text-indigo-600 font-medium pt-1.5 border-t border-slate-100">
                        <span x-text="open ? '▲ Colapsar' : '▼ Ver subdepartamentos ({{ $dept->children->count() }})'"></span>
                    </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Children --}}
        @if($hasChildren)
        <ul x-show="open" x-transition>
            @foreach($dept->children as $child)
                @include('livewire.hr.partials.org-dept-node', ['dept' => $child, 'showEmployees' => $showEmployees])
            @endforeach
        </ul>
        @endif
    </div>
</li>
