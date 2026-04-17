<div>
    <x-page-header title="Pipeline de ventas" description="Seguimiento de oportunidades por etapa">
        <x-slot:actions>
            <a wire:navigate href="{{ route('sales.crm.opportunities.create') }}"
                class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-medium px-5 py-2 rounded-xl transition-all shadow-lg shadow-indigo-500/25">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nueva oportunidad
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    {{-- Resumen --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-200 p-4 shadow-sm">
            <p class="text-2xl font-bold text-gray-700">{{ $summary['count'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Oportunidades activas</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4 shadow-sm">
            <p class="text-2xl font-bold text-indigo-600">${{ number_format($summary['total_value'], 0) }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Valor total del pipeline</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4 shadow-sm">
            <p class="text-2xl font-bold text-blue-600">${{ number_format($summary['weighted_value'], 0) }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Valor ponderado</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4 shadow-sm">
            <p class="text-2xl font-bold text-emerald-600">${{ number_format($summary['won_this_month'], 0) }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Ganado este mes</p>
        </div>
    </div>

    {{-- Filtros y controles --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div class="flex items-center gap-2">
            <select wire:model.live="filterUser" class="px-3 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Todos los vendedores</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
            </select>
            <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                <input type="checkbox" wire:model.live="showWonLost" class="rounded">
                Mostrar ganadas/perdidas
            </label>
        </div>
    </div>

    {{-- Kanban --}}
    <div class="flex gap-4 overflow-x-auto pb-4">
        @foreach($stages as $stage)
            @php
                $stageLabel  = \App\Models\SalesOpportunity::STAGES[$stage] ?? $stage;
                $stageColor  = \App\Models\SalesOpportunity::STAGE_COLORS[$stage] ?? 'bg-gray-100 text-gray-600 border-gray-200';
                $cards       = $byStage[$stage] ?? collect();
                $stageTotal  = $cards->sum('estimated_value');
            @endphp
            <div class="flex-shrink-0 w-72">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-medium border {{ $stageColor }}">{{ $stageLabel }}</span>
                        <span class="text-xs text-gray-400">({{ $cards->count() }})</span>
                    </div>
                    <span class="text-xs text-gray-500">${{ number_format($stageTotal, 0) }}</span>
                </div>
                <div class="space-y-3 min-h-[120px]">
                    @forelse($cards as $opp)
                        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm hover:shadow-md transition-shadow">
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <p class="text-sm font-medium text-gray-900 leading-snug">{{ $opp->title }}</p>
                                <a wire:navigate href="{{ route('sales.crm.opportunities.edit', $opp) }}" class="text-gray-400 hover:text-gray-600 shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 012.828 2.828L11.828 15.828a4 4 0 01-2.828 1.172H7v-2a4 4 0 011.172-2.828z"/></svg>
                                </a>
                            </div>
                            <p class="text-xs text-gray-400 mb-2">{{ $opp->linked_name }}</p>
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-gray-800">${{ number_format($opp->estimated_value, 0) }}</p>
                                <span class="text-xs text-gray-400">{{ $opp->probability }}%</span>
                            </div>
                            @if($opp->expected_close_date)
                                <p class="text-xs text-gray-400 mt-1.5">Cierre: {{ $opp->expected_close_date->format('d/m/Y') }}</p>
                            @endif
                            {{-- Botones de avance rápido --}}
                            @if($opp->isActive())
                                <div class="flex gap-1 mt-3 pt-2 border-t border-gray-100">
                                    @php
                                        $stageKeys = array_keys(\App\Models\SalesOpportunity::STAGES);
                                        $currentIdx = array_search($opp->stage, $stageKeys);
                                        $nextStage  = $stageKeys[$currentIdx + 1] ?? null;
                                    @endphp
                                    @if($nextStage && $nextStage !== 'lost')
                                        <button wire:click="moveStage({{ $opp->id }}, '{{ $nextStage }}')"
                                            class="flex-1 text-xs text-indigo-600 hover:bg-indigo-50 px-2 py-1 rounded-lg transition">
                                            → {{ \App\Models\SalesOpportunity::STAGES[$nextStage] }}
                                        </button>
                                    @endif
                                    <button wire:click="moveStage({{ $opp->id }}, 'won')"
                                        class="text-xs text-emerald-600 hover:bg-emerald-50 px-2 py-1 rounded-lg transition">✓</button>
                                    <button wire:click="moveStage({{ $opp->id }}, 'lost')"
                                        class="text-xs text-red-400 hover:bg-red-50 px-2 py-1 rounded-lg transition">✕</button>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="border-2 border-dashed border-gray-100 rounded-xl h-16 flex items-center justify-center text-xs text-gray-300">
                            Sin oportunidades
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</div>
