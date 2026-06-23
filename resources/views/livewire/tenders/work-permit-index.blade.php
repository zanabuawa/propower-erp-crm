<div class="{{ $embedded ? 'space-y-4' : 'min-h-screen bg-slate-50/50 -m-4 lg:-m-6' }}">
    @if(!$embedded)
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-bold text-slate-800">Permisos de Trabajo</h1>
                <p class="text-[11px] text-slate-400 uppercase tracking-wider">Documentos escaneados de obra</p>
            </div>
            @can('manage work permits')
            <button type="button" wire:click="openModal()"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-4 py-2 rounded-xl transition-all shadow-lg shadow-indigo-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Subir documento
            </button>
            @endcan
        </div>
    </div>
    @else
    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-sm font-semibold text-slate-800">Permisos de trabajo</h2>
            <p class="text-xs text-slate-400">Sube el documento escaneado solicitado por el supervisor.</p>
        </div>
        @can('manage work permits')
        <button type="button" wire:click="openModal()"
            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-4 py-2 rounded-xl transition-all">
            Subir documento
        </button>
        @endcan
    </div>
    @endif

    <div class="{{ $embedded ? 'space-y-4' : 'max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 space-y-4' }}">
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl text-sm font-bold">{{ session('success') }}</div>
        @endif

        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar documento..."
                class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500">
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="text-left px-4 py-3 text-[10px] font-black text-slate-400 uppercase">Documento</th>
                        @if(!$embedded)
                        <th class="text-left px-4 py-3 text-[10px] font-black text-slate-400 uppercase">Proyecto</th>
                        @endif
                        <th class="text-left px-4 py-3 text-[10px] font-black text-slate-400 uppercase">Subido por</th>
                        <th class="text-left px-4 py-3 text-[10px] font-black text-slate-400 uppercase">Fecha documento</th>
                        <th class="text-left px-4 py-3 text-[10px] font-black text-slate-400 uppercase">Subido</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($permits as $permit)
                    <tr class="hover:bg-slate-50/50">
                        <td class="px-4 py-3">
                            <p class="text-xs font-bold text-slate-700 truncate max-w-xs">{{ $permit->description }}</p>
                            <p class="text-[10px] text-slate-400 truncate max-w-xs">{{ $permit->document_original_name ?? 'Documento escaneado' }}</p>
                        </td>
                        @if(!$embedded)
                        <td class="px-4 py-3 text-xs text-slate-500">{{ $permit->project?->name }}</td>
                        @endif
                        <td class="px-4 py-3 text-xs text-slate-500">{{ $permit->issuedBy?->name ?? 'Sistema' }}</td>
                        <td class="px-4 py-3 text-xs text-slate-500">{{ $permit->valid_from?->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-xs text-slate-500">{{ $permit->created_at?->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap items-center justify-end gap-2">
                                @if($permit->document_url)
                                <a href="{{ $permit->document_url }}" target="_blank"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-indigo-100 bg-indigo-50 text-xs font-bold text-indigo-700 hover:bg-indigo-100 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0A9 9 0 113 12a9 9 0 0118 0z"/></svg>
                                    Ver
                                </a>
                                @endif
                                @can('manage work permits')
                                <button type="button" wire:click="openModal({{ $permit->id }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-amber-100 bg-amber-50 text-xs font-bold text-amber-700 hover:bg-amber-100 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 7.125L16.875 4.5"/>
                                    </svg>
                                    Editar
                                </button>
                                <button type="button" wire:click="delete({{ $permit->id }})" wire:confirm="Eliminar documento de permiso?"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-red-100 bg-red-50 text-xs font-bold text-red-700 hover:bg-red-100 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    Eliminar
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $embedded ? 5 : 6 }}" class="px-4 py-12 text-center text-slate-400 text-sm">
                            Sin documentos de permisos de trabajo.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($permits->hasPages())
                <div class="px-4 py-3 border-t border-slate-100">{{ $permits->links() }}</div>
            @endif
        </div>
    </div>

    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg p-6 space-y-4 max-h-[90vh] overflow-y-auto">
            <h3 class="text-base font-black text-slate-800">{{ $editingId ? 'Editar documento de permiso' : 'Subir documento de permiso' }}</h3>
            <div class="space-y-4">
                @if(!$contextProjectId)
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Proyecto *</label>
                    <select wire:model="project_id" class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                        <option value="">Seleccionar proyecto</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    </select>
                    @error('project_id') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                @endif

                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Nombre del documento *</label>
                    <input wire:model="document_name" type="text" placeholder="Ej. Permiso de trabajo 09/06/2026"
                        class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                    @error('document_name') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Fecha del documento *</label>
                    <input wire:model="document_date" type="date"
                        class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                    @error('document_date') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Documento escaneado {{ $editingId ? '(opcional para reemplazar)' : '*' }}</label>
                    <input wire:model="document" type="file" accept=".pdf,image/*"
                        class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-600 file:text-xs file:font-bold">
                    <p class="mt-1 text-[10px] text-slate-400">Formatos permitidos: PDF, JPG, PNG o WEBP. Max. 10 MB.</p>
                    @if($currentDocumentName)
                        <p class="mt-1 text-[10px] text-slate-500">Actual: {{ $currentDocumentName }}</p>
                    @endif
                    @error('document') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" wire:click="save" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black py-3 rounded-xl transition-all">
                    {{ $editingId ? 'Guardar cambios' : 'Guardar documento' }}
                </button>
                <button type="button" wire:click="$set('showModal', false)" class="px-4 py-3 border border-slate-200 text-slate-600 text-xs font-bold rounded-xl hover:bg-slate-50">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
