<div>
    <x-page-header :title="'Prospecto: ' . $prospect->full_name" :description="'Expediente y trazabilidad del candidato'">
        <x-slot:actions>
            <div class="flex gap-2">
                <a href="{{ route('hr.prospects.index') }}" wire:navigate
                   class="inline-flex items-center gap-2 px-3 py-2 text-sm text-slate-600 hover:text-slate-800 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                    ← Volver al listado
                </a>
                @can('edit hr')
                <a href="{{ route('hr.prospects.edit', $prospect) }}" wire:navigate
                   class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M11 5a2 2 0 002 2h2a2 2 0 002-2M11 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    Editar datos
                </a>
                @endcan
            </div>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- COLUMNA IZQUIERDA: INFORMACIÓN --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- Card Perfil --}}
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
                <div class="p-6 text-center border-b border-slate-100 bg-slate-50/50">
                    <div class="w-20 h-20 rounded-full bg-indigo-100 flex items-center justify-center mx-auto mb-4 border-4 border-white shadow-sm">
                        <span class="text-2xl font-bold text-indigo-600">
                            {{ strtoupper(substr($prospect->first_name,0,1) . substr($prospect->last_name,0,1)) }}
                        </span>
                    </div>
                    <h2 class="text-lg font-bold text-slate-800">{{ $prospect->full_name }}</h2>
                    <p class="text-sm text-indigo-600 font-medium">{{ $prospect->position?->name ?? 'Sin puesto asignado' }}</p>
                    <div class="mt-4">
                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $prospect->status_color }}">
                            {{ $prospect->status_label }}
                        </span>
                    </div>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center gap-3 text-sm">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span class="text-slate-600">{{ $prospect->email ?? 'Sin correo' }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <span class="text-slate-600">{{ $prospect->phone ?? 'Sin teléfono' }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-slate-600">Fuente: {{ $prospect->source_label }}</span>
                    </div>
                    @if($prospect->cv_path)
                    <div class="pt-2">
                        <a href="{{ Storage::url($prospect->cv_path) }}" target="_blank"
                           class="flex items-center justify-center gap-2 w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.586-.897L13.828 5.686A1 1 0 0013.103 5.5H7a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            VER CURRICULUM VITAE
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Añadir Nota --}}
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                <h3 class="text-sm font-semibold text-slate-700 mb-3">Añadir comentario interno</h3>
                <form wire:submit="addNote">
                    <textarea wire:model="newNote" rows="3" 
                              class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30 resize-none"
                              placeholder="Escribe un comentario para el equipo..."></textarea>
                    <div class="flex justify-end mt-2">
                        <button type="submit" class="px-4 py-1.5 bg-indigo-600 text-white text-xs font-bold rounded-lg hover:bg-indigo-700">
                            Guardar Nota
                        </button>
                    </div>
                </form>
                @if(session('note_success'))
                    <p class="text-[10px] text-green-600 mt-2 font-medium">{{ session('note_success') }}</p>
                @endif
            </div>
        </div>

        {{-- COLUMNA DERECHA: TIMELINE / TRAZABILIDAD --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm min-h-[600px]">
                <div class="p-4 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wider">Línea de tiempo y Actividad</h3>
                </div>

                <div class="p-6">
                    <div class="relative">
                        {{-- Línea central --}}
                        <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-slate-100"></div>

                        <div class="space-y-8 relative">
                            @forelse($timeline as $item)
                            <div class="flex gap-6 items-start">
                                {{-- Icono --}}
                                <div class="relative z-10 w-8 h-8 rounded-full flex items-center justify-center shadow-sm
                                    {{ $item['type'] === 'status' ? 'bg-blue-100 text-blue-600' : '' }}
                                    {{ $item['type'] === 'interview' ? 'bg-purple-100 text-purple-600' : '' }}
                                    {{ $item['type'] === 'note' ? 'bg-amber-100 text-amber-600' : '' }}
                                    {{ $item['type'] === 'evaluation' ? 'bg-emerald-100 text-emerald-600' : '' }}
                                ">
                                    @if($item['icon'] === 'status')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @elseif($item['icon'] === 'interview')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    @elseif($item['icon'] === 'note')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                    @elseif($item['icon'] === 'evaluation')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2"/></svg>
                                    @endif
                                </div>

                                {{-- Contenido --}}
                                <div class="flex-1 bg-slate-50/50 rounded-xl border border-slate-100 p-4 hover:bg-slate-50 transition-colors shadow-sm">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <h4 class="text-sm font-bold text-slate-800">{{ $item['title'] }}</h4>
                                            <p class="text-[11px] text-slate-400">{{ $item['date']->format('d/m/Y H:i') }} · Por: {{ $item['user'] ?? 'Sistema' }}</p>
                                        </div>
                                    </div>
                                    <p class="text-sm text-slate-600 leading-relaxed">
                                        {{ $item['content'] }}
                                    </p>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-20">
                                <p class="text-slate-400 text-sm">No hay actividad registrada para este prospecto.</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
