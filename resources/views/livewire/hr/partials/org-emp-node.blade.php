{{-- Variable: $emp (HrEmployee with subordinates loaded) --}}
@php $hasSubs = $emp->subordinates->isNotEmpty(); @endphp

<li>
    <div x-data="{ open: true }">
        <div class="org-card-wrap">
            <div class="org-card bg-white border border-slate-200 rounded-xl shadow-sm w-44 overflow-hidden text-center">
                <div class="h-1.5 {{ $hasSubs ? 'bg-violet-500' : 'bg-slate-200' }}"></div>
                <div class="p-3">
                    <div class="w-10 h-10 rounded-full bg-violet-100 flex items-center justify-center text-violet-600 font-bold text-sm mx-auto mb-1.5">
                        {{ strtoupper(substr($emp->first_name,0,1)) }}{{ strtoupper(substr($emp->last_name,0,1)) }}
                    </div>
                    <p class="text-sm font-semibold text-slate-800 leading-tight truncate" title="{{ $emp->full_name }}">
                        {{ $emp->first_name }} {{ $emp->last_name }}
                    </p>
                    @if($emp->position)
                    <p class="text-[10px] text-indigo-500 mt-0.5 truncate">{{ $emp->position->name }}</p>
                    @endif
                    @if($emp->department)
                    <p class="text-[10px] text-slate-400 truncate">{{ $emp->department->name }}</p>
                    @endif

                    @if($hasSubs)
                    <button @click="open = !open"
                            class="mt-2 text-[10px] text-violet-400 hover:text-violet-600 font-medium border-t border-slate-100 pt-1.5 w-full">
                        <span x-text="open ? '▲ Ocultar' : '▼ {{ $emp->subordinates->count() }} reportes'"></span>
                    </button>
                    @endif
                </div>
            </div>
        </div>

        @if($hasSubs)
        <ul x-show="open" x-transition>
            @foreach($emp->subordinates as $sub)
                @include('livewire.hr.partials.org-emp-node', ['emp' => $sub])
            @endforeach
        </ul>
        @endif
    </div>
</li>
