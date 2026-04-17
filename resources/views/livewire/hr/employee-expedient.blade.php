<div>
    {{-- Modal container --}}
    <div x-data="{ show: false }" 
         x-on:show-expedient-modal.window="show = true"
         x-on:hide-expedient-modal.window="show = false"
         x-show="show" 
         class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between mb-4 border-b pb-3">
                        <h3 class="text-lg font-bold text-slate-800">
                            @if($type === 'document') Agregar Documento @endif
                            @if($type === 'education') Agregar Historial Académico @endif
                            @if($type === 'training') Registrar Capacitación @endif
                        </h3>
                        <button @click="show = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Form: Document --}}
                    @if($type === 'document')
                    <form wire:submit.prevent="saveDocument" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1 uppercase">Tipo de Documento (INE, RFC, etc.)</label>
                            <input wire:model="doc_type" type="text" placeholder="Ej: INE Anverso" required
                                   class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1 uppercase">Archivo Digital (PDF o Imagen)</label>
                            <input wire:model="doc_file" type="file" required
                                   class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            @error('doc_file') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1 uppercase">Fecha de Vencimiento (Si aplica)</label>
                            <input wire:model="doc_expiry" type="date"
                                   class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1 uppercase">Notas adicionales</label>
                            <textarea wire:model="doc_notes" rows="2"
                                      class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30"></textarea>
                        </div>
                        <div class="pt-3 flex justify-end gap-3">
                            <button type="button" @click="show = false" class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 rounded-lg transition-colors">Cancelar</button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors">Guardar Documento</button>
                        </div>
                    </form>
                    @endif

                    {{-- Form: Education --}}
                    @if($type === 'education')
                    <form wire:submit.prevent="saveEducation" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1 uppercase">Institución</label>
                            <input wire:model="edu_institution" type="text" placeholder="Ej: Universidad Nacional Autonoma de México" required
                                   class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1 uppercase">Grado / Título</label>
                            <input wire:model="edu_degree" type="text" placeholder="Ej: Licenciatura" required
                                   class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1 uppercase">Campo de estudio</label>
                            <input wire:model="edu_field" type="text" placeholder="Ej: Administración de Empresas"
                                   class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1 uppercase">Fecha Inicio</label>
                                <input wire:model="edu_start" type="date"
                                       class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1 uppercase">Fecha Fin</label>
                                <input wire:model="edu_end" type="date"
                                       class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                            </div>
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input wire:model="edu_completed" type="checkbox" class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                            <span class="text-sm text-slate-700 font-medium">¿Estudios concluidos?</span>
                        </label>
                        <div class="pt-3 flex justify-end gap-3">
                            <button type="button" @click="show = false" class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 rounded-lg transition-colors">Cancelar</button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors">Guardar Educación</button>
                        </div>
                    </form>
                    @endif

                    {{-- Form: Training --}}
                    @if($type === 'training')
                    <form wire:submit.prevent="saveTraining" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1 uppercase">Curso / Capacitación</label>
                            <select wire:model="training_course_id" required
                                    class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                                <option value="">Seleccionar curso</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}">{{ $course->name }} ({{ $course->provider }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1 uppercase">Fecha de Término</label>
                                <input wire:model="training_date" type="date"
                                       class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1 uppercase">Fecha Vencimiento (DC3)</label>
                                <input wire:model="training_expiry" type="date"
                                       class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1 uppercase">Certificado / DC3 (PDF/Imagen)</label>
                            <input wire:model="training_file" type="file"
                                   class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>
                        <div class="pt-3 flex justify-end gap-3">
                            <button type="button" @click="show = false" class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 rounded-lg transition-colors">Cancelar</button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors">Registrar Capacitación</button>
                        </div>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
