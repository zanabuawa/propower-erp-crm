<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">Equipo de Trabajo</h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">{{ $members->count() }} Colaboradores Asignados</p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <button type="button" wire:click="openForm"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Asignar Miembro</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto p-4 sm:p-6 lg:p-8 space-y-8">
        <x-alert />

        {{-- ── FORMULARIO INLINE ────────────────────────────────────────── --}}
        @if ($showForm)
            <div class="bg-white rounded-[2.5rem] border-2 border-indigo-100 p-8 lg:p-10 shadow-xl shadow-indigo-50/50 animate-in fade-in slide-in-from-top-4 duration-300 relative overflow-hidden group">
                <div class="absolute -right-20 -top-20 w-64 h-64 bg-indigo-500/5 rounded-full blur-3xl transition-transform group-hover:scale-110"></div>
                
                <div class="relative z-10 space-y-8">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        </div>
                        <h3 class="text-lg font-black text-indigo-900 uppercase tracking-widest">{{ $editingId ? 'Actualizar Miembro' : 'Nueva Asignación de Equipo' }}</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">Colaborador / Usuario *</label>
                            <div class="relative">
                                <select wire:model="user_id" {{ $editingId ? 'disabled' : '' }}
                                    class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 appearance-none cursor-pointer transition-all disabled:opacity-50">
                                    <option value="">— Seleccionar —</option>
                                    @foreach ($availableUsers as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                            @error('user_id') <p class="text-[10px] text-rose-500 font-bold">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">Rol en el Proyecto *</label>
                            <select wire:model="role" class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 appearance-none cursor-pointer transition-all">
                                <option value="lider">Líder / Gerente</option>
                                <option value="desarrollador">Operativo / Ejecutor</option>
                                <option value="diseñador">Creativo / Diseño</option>
                                <option value="qa">QA / Calidad</option>
                                <option value="observador">Auditor / Observador</option>
                                <option value="otro">Otro Perfil</option>
                            </select>
                            @error('role') <p class="text-[10px] text-rose-500 font-bold">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex items-center gap-3 pt-6">
                            <label class="flex items-center gap-3 cursor-pointer group/toggle">
                                <div class="relative inline-flex items-center">
                                    <input type="checkbox" wire:model="is_active" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:bg-emerald-500 relative transition-all after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5 shadow-inner"></div>
                                </div>
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-500 peer-checked:text-emerald-600 transition-colors">Estatus Activo</span>
                            </label>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">Fecha de Ingreso</label>
                            <input type="date" wire:model="joined_at"
                                   class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">Fecha Estimada de Salida</label>
                            <input type="date" wire:model="left_at"
                                   class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                        </div>

                        <div class="md:col-span-2 space-y-2">
                            <label class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">Responsabilidades / Notas</label>
                            <textarea wire:model="notes" rows="1" placeholder="Ej. Encargado de la supervisión eléctrica..."
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-indigo-500/10 transition-all resize-none"></textarea>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 pt-4 border-t border-indigo-50">
                        <button wire:click="save" wire:loading.attr="disabled"
                            class="px-8 py-4 bg-indigo-600 text-white rounded-2xl text-xs font-black uppercase tracking-[0.2em] shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 hover:scale-[1.02] active:scale-[0.98] transition-all">
                            <span wire:loading.remove wire:target="save">{{ $editingId ? 'Actualizar Miembro' : 'Asignar Miembro' }}</span>
                            <span wire:loading wire:target="save" class="animate-pulse">Procesando...</span>
                        </button>
                        <button wire:click="resetForm"
                            class="px-6 py-4 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-colors">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- ── TABLA DE MIEMBROS ────────────────────────────────────────── --}}
        <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
            @if ($members->isEmpty())
                <div class="py-24 text-center">
                    <div class="w-20 h-20 rounded-full bg-slate-50 flex items-center justify-center text-slate-200 mx-auto mb-6 border-2 border-dashed border-slate-200">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2h5M12 12a4 4 0 100-8 4 4 0 000 8z"/></svg>
                    </div>
                    <h3 class="text-xl font-black text-slate-800 tracking-tight">Sin Miembros Registrados</h3>
                    <p class="text-sm font-medium text-slate-400 mt-2 max-w-sm mx-auto">Asigna colaboradores al proyecto para gestionar tareas y responsabilidades.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left border-b border-slate-100 bg-slate-50/30">
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Colaborador</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Rol en Proyecto</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Período de Asignación</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Estado</th>
                                <th class="px-8 py-4"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($members as $member)
                                @php
                                    $roleStyle = match($member->role) {
                                        'lider'         => 'bg-purple-50 text-purple-700 border-purple-100',
                                        'desarrollador' => 'bg-blue-50 text-blue-700 border-blue-100',
                                        'diseñador'     => 'bg-pink-50 text-pink-700 border-pink-100',
                                        'qa'            => 'bg-amber-50 text-amber-700 border-amber-100',
                                        default         => 'bg-slate-50 text-slate-600 border-slate-100',
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50/50 transition-colors group {{ !$member->is_active ? 'opacity-60 grayscale-[50%]' : '' }}">
                                    <td class="px-8 py-5">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-xl bg-slate-900 flex items-center justify-center text-white text-xs font-black shadow-lg shadow-slate-200">
                                                {{ strtoupper(substr($member->user->name, 0, 2)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-sm font-black text-slate-800 truncate">{{ $member->user->name }}</p>
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ $member->user->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <span class="inline-flex px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest border {{ $roleStyle }}">
                                            {{ $member->role }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="flex flex-col gap-0.5">
                                            <span class="text-[10px] font-black text-slate-700">Desde: {{ $member->joined_at?->format('d/m/Y') ?? 'Inicio' }}</span>
                                            @if($member->left_at)
                                                <span class="text-[10px] font-bold text-slate-400">Hasta: {{ $member->left_at->format('d/m/Y') }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-widest {{ $member->is_active ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-slate-100 text-slate-400 border border-slate-200' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $member->is_active ? 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]' : 'bg-slate-400' }}"></span>
                                            {{ $member->is_active ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-5 text-right w-32">
                                        <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-all">
                                            <button wire:click="toggleActive({{ $member->id }})" title="{{ $member->is_active ? 'Baja Temporal' : 'Reactivar' }}"
                                                class="p-2 bg-white rounded-lg text-slate-400 hover:text-indigo-600 border border-slate-100 shadow-sm transition-all">
                                                @if ($member->is_active)
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                                @else
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                @endif
                                            </button>
                                            <button wire:click="edit({{ $member->id }})" class="p-2 bg-white rounded-lg text-slate-400 hover:text-indigo-600 border border-slate-100 shadow-sm transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                            <button wire:click="delete({{ $member->id }})" wire:confirm="¿Desasignar definitivamente a este miembro?" class="p-2 bg-white rounded-lg text-slate-400 hover:text-rose-600 border border-slate-100 shadow-sm transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>