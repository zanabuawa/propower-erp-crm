<div class="w-full" x-data="{}">

    {{-- Upload spinner --}}
    <div wire:loading wire:target="uploadFile"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-[2px]">
        <div class="bg-white rounded-xl shadow-xl px-8 py-6 flex items-center gap-4">
            <svg class="animate-spin w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
            <span class="text-sm font-semibold text-slate-700">Subiendo imagen…</span>
        </div>
    </div>

    {{-- Toast --}}
    <div x-data="{ show: false, msg: '' }"
         x-on:notify.window="show = true; msg = $event.detail.message; setTimeout(() => show = false, 3000)"
         x-show="show" x-transition
         class="fixed top-5 right-5 z-50 bg-emerald-500 text-white text-sm font-semibold px-5 py-3 rounded-xl shadow-lg">
        ✓ <span x-text="msg"></span>
    </div>

    {{-- Header --}}
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Editor de Galería</h1>
            <p class="text-sm text-slate-500 mt-0.5">Administra las categorías e imágenes de la página de galería.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="/galeria" target="_blank" rel="noopener"
               class="inline-flex items-center gap-2 px-4 py-2 bg-slate-900 text-white text-sm font-semibold rounded-lg hover:bg-slate-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                Ver galería
            </a>
            <button wire:click="saveAll"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Guardar todo
            </button>
        </div>
    </div>

    {{-- Two-column layout --}}
    <div class="flex gap-5 items-start">

        {{-- Sidebar --}}
        <div class="w-52 shrink-0 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden sticky top-4">
            <div class="px-3 pt-3 pb-1">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest px-2">Categorías</p>
            </div>

            @foreach($categories as $ci => $cat)
            <button wire:click="$set('tab','{{ $ci }}')"
                    class="w-full flex items-center gap-2.5 px-3 py-2.5 text-sm transition-colors text-left
                           {{ $tab == $ci
                              ? 'bg-indigo-50 text-indigo-700 border-r-2 border-indigo-500 font-semibold'
                              : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 border-r-2 border-transparent font-medium' }}">
                <svg class="w-3.5 h-3.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span class="truncate">{{ $cat['short'] ?: ('Cat. ' . ($ci + 1)) }}</span>
                <span class="ml-auto text-xs text-slate-400 shrink-0">{{ count($cat['images']) }}</span>
            </button>
            @endforeach

            <div class="p-3 border-t border-slate-100 mt-1">
                <button wire:click="addCategory"
                        class="w-full flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nueva categoría
                </button>
            </div>
        </div>

        {{-- Content panel --}}
        <div class="flex-1 min-w-0 bg-white rounded-xl border border-slate-200 shadow-sm">
        <div class="p-6 space-y-6">

            @php $ci = (int)$tab; $cat = $categories[$ci] ?? null; @endphp

            @if($cat !== null)

            {{-- Info de la categoría --}}
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-bold text-slate-800">Información de la categoría</h2>
                    <button wire:click="removeCategory({{ $ci }})"
                            onclick="return confirm('¿Eliminar esta categoría y todas sus imágenes?')"
                            class="flex items-center gap-1 text-xs font-semibold text-red-500 hover:text-red-700 hover:bg-red-50 px-2 py-1 rounded transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Eliminar categoría
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="label-xs">Título completo</label>
                        <input wire:model="categories.{{ $ci }}.title" type="text" class="field"
                               placeholder="Ej. Baja Tensión">
                    </div>
                    <div>
                        <label class="label-xs">Nombre corto (filtro)</label>
                        <input wire:model="categories.{{ $ci }}.short" type="text" class="field"
                               placeholder="Ej. Baja Tensión">
                    </div>
                    <div>
                        <label class="label-xs">ID / Slug</label>
                        <input wire:model="categories.{{ $ci }}.id" type="text" class="field"
                               placeholder="Ej. baja-tension">
                    </div>
                    <div>
                        <label class="label-xs">Sector</label>
                        <select wire:model="categories.{{ $ci }}.sector" class="field">
                            @foreach(['Industria','Minería','Ingeniería','Mantenimiento','Comercial'] as $s)
                            <option value="{{ $s }}">{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="label-xs">Descripción</label>
                        <textarea wire:model="categories.{{ $ci }}.desc" rows="2" class="field"
                                  placeholder="Descripción breve de esta categoría…"></textarea>
                    </div>
                </div>
            </div>

            {{-- Imágenes --}}
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-base font-bold text-slate-800">
                        Imágenes
                        <span class="ml-2 text-sm font-normal text-slate-400">{{ count($cat['images']) }} fotos</span>
                    </h2>
                    <div class="flex items-center gap-3">
                        <button wire:click="addImage({{ $ci }}); $nextTick(() => $wire.openPicker('{{ $ci }}.' + ({{ count($cat['images']) }})))"
                                class="btn-add flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Agregar imagen
                        </button>
                    </div>
                </div>

                @php $imgs = $cat['images'] ?? []; @endphp

                @if(count($imgs) === 0)
                <div class="text-center py-12 border-2 border-dashed border-slate-200 rounded-xl text-slate-400 text-sm">
                    Sin imágenes. Haz clic en "Agregar imagen" para comenzar.
                </div>
                @else
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                    @foreach($imgs as $ii => $imgSrc)
                    <div class="relative group">
                        {{-- Preview --}}
                        @if($imgSrc)
                        <img src="{{ $imgSrc }}" alt=""
                             class="w-full h-32 object-cover rounded-lg border border-slate-200">
                        @else
                        <div class="w-full h-32 rounded-lg border-2 border-dashed border-slate-200 bg-slate-50 flex items-center justify-center text-slate-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        @endif

                        {{-- Overlay actions --}}
                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex flex-col items-center justify-center gap-2">
                            <button wire:click="openPicker('{{ $ci }}.{{ $ii }}')"
                                    class="px-3 py-1.5 bg-white text-slate-800 text-xs font-semibold rounded-md hover:bg-slate-100 transition-colors">
                                Elegir foto
                            </button>
                            <button wire:click="removeImage({{ $ci }}, {{ $ii }})"
                                    class="px-3 py-1.5 bg-red-500 text-white text-xs font-semibold rounded-md hover:bg-red-600 transition-colors">
                                Eliminar
                            </button>
                        </div>

                        {{-- Path input --}}
                        <input wire:model="categories.{{ $ci }}.images.{{ $ii }}" type="text"
                               class="mt-1.5 w-full text-xs px-2 py-1.5 border border-slate-200 rounded bg-slate-50 text-slate-600"
                               placeholder="/assets/img/...">
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Save button --}}
            <div class="save-row">
                <button wire:click="saveCategory({{ $ci }})" class="btn-save">
                    Guardar categoría
                </button>
            </div>

            @else
            <div class="text-center py-16 text-slate-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-sm">Sin categorías. Crea una con el botón de la barra lateral.</p>
            </div>
            @endif

        </div>
        </div>{{-- /content panel --}}
    </div>{{-- /flex --}}

    @include('livewire.landing.partials.media-picker', ['withUpload' => true])
</div>{{-- /w-full --}}

@push('scripts')
<style>
    [x-cloak] { display: none !important; }
    .label-xs {
        display: block; font-size: 0.7rem; font-weight: 600;
        text-transform: uppercase; letter-spacing: 0.08em;
        color: rgb(100 116 139); margin-bottom: 0.375rem;
    }
    .field {
        width: 100%; padding: 0.5rem 0.75rem;
        border: 1px solid rgb(203 213 225); border-radius: 0.5rem;
        font-size: 0.875rem; color: rgb(15 23 42); background: #fff;
        transition: border-color 0.15s, box-shadow 0.15s; outline: none;
    }
    .field:focus {
        border-color: rgb(99 102 241);
        box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
    }
    .btn-add {
        font-size: 0.75rem; font-weight: 600; color: rgb(99 102 241);
        cursor: pointer; background: none; border: none; padding: 0;
    }
    .btn-add:hover { color: rgb(67 56 202); }
    .btn-save {
        padding: 0.625rem 1.5rem; background: rgb(99 102 241);
        color: #fff; font-size: 0.875rem; font-weight: 600;
        border-radius: 0.5rem; border: none; cursor: pointer;
        transition: background 0.15s;
    }
    .btn-save:hover { background: rgb(79 70 229); }
    .save-row {
        display: flex; justify-content: flex-end;
        padding-top: 1rem; border-top: 1px solid rgb(241 245 249);
    }
</style>
@endpush
