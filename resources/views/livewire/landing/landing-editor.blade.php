<div class="w-full" x-data="{}">

    {{-- ── Global hidden file input ─────────────────────────────────────── --}}
    <input type="file" x-ref="uploadInput" class="hidden" accept="image/*" wire:model="uploadFile">

    {{-- ── Upload spinner overlay ───────────────────────────────────────── --}}
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

    {{-- ── Toast notification ───────────────────────────────────────────── --}}
    <div x-data="{ show: false, msg: '' }"
         x-on:notify.window="show = true; msg = $event.detail.message; setTimeout(() => show = false, 3000)"
         x-show="show" x-transition
         class="fixed top-5 right-5 z-50 bg-emerald-500 text-white text-sm font-semibold px-5 py-3 rounded-xl shadow-lg">
        ✓ <span x-text="msg"></span>
    </div>

    {{-- ── Header ───────────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Editor de Landing Page</h1>
            <p class="text-sm text-slate-500 mt-0.5">Los cambios se reflejan en el sitio al guardar cada sección.</p>
        </div>
        <a href="/" target="_blank" rel="noopener"
           class="inline-flex items-center gap-2 px-4 py-2 bg-slate-900 text-white text-sm font-semibold rounded-lg hover:bg-slate-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
            Ver sitio
        </a>
    </div>

    {{-- ── Two-column layout ────────────────────────────────────────────── --}}
    <div class="flex gap-5 items-start">

        {{-- Sidebar tabs --}}
        <div class="w-44 shrink-0 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden sticky top-4">
            <div class="px-3 pt-3 pb-1">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest px-2">Secciones</p>
            </div>
            @foreach([
                'hero'      => ['Hero',      'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                'oferta'    => ['Oferta',    'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
                'nosotros'  => ['Nosotros',  'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                'servicios' => ['Servicios', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                'galeria'   => ['Galería',   'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'],
                'contacto'  => ['Contacto',  'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                'footer'    => ['Footer',    'M4 6h16M4 12h16M4 18h7'],
            ] as $key => [$label, $icon])
            <button wire:click="$set('tab','{{ $key }}')"
                    class="w-full flex items-center gap-2.5 px-3 py-2.5 text-sm font-medium transition-colors
                           {{ $tab === $key
                              ? 'bg-indigo-50 text-indigo-700 border-r-2 border-indigo-500'
                              : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 border-r-2 border-transparent' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $icon }}"/>
                </svg>
                {{ $label }}
            </button>
            @endforeach
        </div>

        {{-- Content panel --}}
        <div class="flex-1 min-w-0 bg-white rounded-xl border border-slate-200 shadow-sm">
        <div class="p-6 space-y-6">

            {{-- ══════════════════ HERO ══════════════════ --}}
            @if($tab === 'hero')
            <div class="space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="label-xs">Eyebrow (texto pequeño sobre el título)</label>
                        <input wire:model="hero.eyebrow" type="text" class="field">
                    </div>
                    <div>
                        <label class="label-xs">Botón principal (CTA)</label>
                        <input wire:model="hero.cta_text" type="text" class="field">
                    </div>
                    <div>
                        <label class="label-xs">Enlace secundario (CTA sub)</label>
                        <input wire:model="hero.cta_sub" type="text" class="field">
                    </div>
                </div>

                {{-- Stats --}}
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="label-xs">Estadísticas</span>
                        <button wire:click="addStat" class="btn-add">+ Agregar</button>
                    </div>
                    <div class="space-y-2">
                        @foreach(($hero['stats'] ?? []) as $i => $stat)
                        <div class="flex gap-3 items-center">
                            <input wire:model="hero.stats.{{ $i }}.value" placeholder="Ej. 200+" class="field w-32">
                            <input wire:model="hero.stats.{{ $i }}.label" placeholder="Proyectos entregados" class="field flex-1">
                            <button wire:click="removeStat({{ $i }})" class="btn-remove">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Hero carousel images --}}
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="label-xs">Imágenes del carrusel</span>
                        <button wire:click="addHeroImage" class="btn-add">+ Agregar</button>
                    </div>
                    <div class="space-y-3">
                        @foreach(($hero['images'] ?? []) as $i => $img)
                        <div class="flex gap-3 items-center">
                            <span class="text-xs text-slate-400 w-5 text-right shrink-0">{{ $i + 1 }}</span>
                            @include('livewire.landing.partials.img-field', [
                                'src'    => $img,
                                'model'  => "hero.images.{$i}",
                                'target' => "hero.images.{$i}",
                            ])
                            <button wire:click="removeHeroImage({{ $i }})" class="btn-remove shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="save-row">
                    <button wire:click="save('hero')" class="btn-save">Guardar Hero</button>
                </div>
            </div>
            @endif

            {{-- ══════════════════ OFERTA ══════════════════ --}}
            @if($tab === 'oferta')
            <div class="space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="label-xs">Eyebrow</label>
                        <input wire:model="oferta.eyebrow" type="text" class="field">
                    </div>
                    <div>
                        <label class="label-xs">Título de sección</label>
                        <input wire:model="oferta.title" type="text" class="field">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="label-xs">Sectores (tarjetas)</span>
                        <button wire:click="addSector" class="btn-add">+ Agregar</button>
                    </div>
                    <div class="space-y-4">
                        @foreach(($oferta['sectors'] ?? []) as $i => $sector)
                        <div class="border border-slate-200 rounded-lg p-4 space-y-4 bg-slate-50/50">
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Sector {{ $i + 1 }}</span>
                                <button wire:click="removeSector({{ $i }})" class="btn-remove">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                            {{-- Image with preview --}}
                            <div>
                                <label class="label-xs">Imagen de fondo</label>
                                @include('livewire.landing.partials.img-field', [
                                    'src'    => $sector['image'] ?? '',
                                    'model'  => "oferta.sectors.{$i}.image",
                                    'target' => "oferta.sectors.{$i}.image",
                                    'tall'   => true,
                                ])
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="label-xs">Título</label>
                                    <input wire:model="oferta.sectors.{{ $i }}.title" type="text" class="field">
                                </div>
                                <div>
                                    <label class="label-xs">Descripción</label>
                                    <input wire:model="oferta.sectors.{{ $i }}.desc" type="text" class="field">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="label-xs">Tags (separados por coma)</label>
                                    <input type="text"
                                           value="{{ implode(', ', $sector['tags'] ?? []) }}"
                                           x-on:change="$wire.call('updateSectorTags', {{ $i }}, $event.target.value)"
                                           class="field" placeholder="Subestaciones, Tableros, Automatización">
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="save-row">
                    <button wire:click="save('oferta')" class="btn-save">Guardar Oferta</button>
                </div>
            </div>
            @endif

            {{-- ══════════════════ NOSOTROS ══════════════════ --}}
            @if($tab === 'nosotros')
            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="label-xs">Eyebrow</label>
                        <input wire:model="nosotros.eyebrow" type="text" class="field">
                    </div>
                    <div>
                        <label class="label-xs">Subtítulo (Desde 2018 · …)</label>
                        <input wire:model="nosotros.since" type="text" class="field">
                    </div>
                </div>
                <div>
                    <label class="label-xs">Párrafo principal</label>
                    <textarea wire:model="nosotros.body1" rows="3" class="field"></textarea>
                </div>
                <div>
                    <label class="label-xs">Párrafo secundario</label>
                    <textarea wire:model="nosotros.body2" rows="3" class="field"></textarea>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="label-xs">Misión</label>
                        <textarea wire:model="nosotros.mision" rows="4" class="field"></textarea>
                    </div>
                    <div>
                        <label class="label-xs">Visión</label>
                        <textarea wire:model="nosotros.vision" rows="4" class="field"></textarea>
                    </div>
                    <div>
                        <label class="label-xs">Valores</label>
                        <textarea wire:model="nosotros.valores" rows="4" class="field"></textarea>
                    </div>
                </div>
                <div class="save-row">
                    <button wire:click="save('nosotros')" class="btn-save">Guardar Nosotros</button>
                </div>
            </div>
            @endif

            {{-- ══════════════════ SERVICIOS ══════════════════ --}}
            @if($tab === 'servicios')
            <div class="space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="label-xs">Eyebrow</label>
                        <input wire:model="servicios.eyebrow" type="text" class="field">
                    </div>
                    <div>
                        <label class="label-xs">Título</label>
                        <input wire:model="servicios.title" type="text" class="field">
                    </div>
                </div>

                {{-- Filter bar --}}
                <div class="flex flex-wrap gap-2 mb-4">
                    <button type="button" wire:click="$set('serviciosFilter','Todos')"
                            class="px-3 py-1 text-xs font-semibold border rounded-full transition-colors
                                   {{ $serviciosFilter === 'Todos' ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-400' }}">
                        Todos
                    </button>
                    <button type="button" wire:click="$set('serviciosFilter','industria')"
                            class="px-3 py-1 text-xs font-semibold border rounded-full transition-colors
                                   {{ $serviciosFilter === 'industria' ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-400' }}">
                        Industria
                    </button>
                    <button type="button" wire:click="$set('serviciosFilter','mineria')"
                            class="px-3 py-1 text-xs font-semibold border rounded-full transition-colors
                                   {{ $serviciosFilter === 'mineria' ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-400' }}">
                        Minería
                    </button>
                    <button type="button" wire:click="$set('serviciosFilter','ingenieria')"
                            class="px-3 py-1 text-xs font-semibold border rounded-full transition-colors
                                   {{ $serviciosFilter === 'ingenieria' ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-400' }}">
                        Ingeniería
                    </button>
                    <button type="button" wire:click="$set('serviciosFilter','__landing__')"
                            class="px-3 py-1 text-xs font-semibold border rounded-full transition-colors
                                   {{ $serviciosFilter === '__landing__' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-emerald-600 border-emerald-300 hover:border-emerald-500' }}">
                        En landing
                    </button>
                </div>

                @foreach(['industria' => 'Industria', 'mineria' => 'Minería', 'ingenieria' => 'Ingeniería'] as $cat => $catLabel)
                @if($serviciosFilter === 'Todos' || $serviciosFilter === '__landing__' || $serviciosFilter === $cat)
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="label-xs">{{ $catLabel }} — {{ count($servicios[$cat] ?? []) }} servicios</span>
                        <button wire:click="addService('{{ $cat }}')" class="btn-add">+ Agregar</button>
                    </div>
                    <div class="space-y-3 max-h-96 overflow-y-auto pr-1">
                        @foreach(($servicios[$cat] ?? []) as $i => $svc)
                        @if($serviciosFilter === 'Todos' || ($svc['on_landing'] ?? true))
                        <div class="flex gap-3 items-center p-2 rounded-lg border transition-colors
                                    {{ ($svc['on_landing'] ?? true) ? 'bg-slate-50 border-slate-100' : 'bg-slate-50/40 border-dashed border-slate-200 opacity-60' }}">
                            <span class="text-xs text-slate-400 w-5 text-right shrink-0">{{ $i + 1 }}</span>
                            @include('livewire.landing.partials.img-field', [
                                'src'    => $svc['img'] ?? '',
                                'model'  => "servicios.{$cat}.{$i}.img",
                                'target' => "servicios.{$cat}.{$i}.img",
                                'compact' => true,
                            ])
                            <input wire:model="servicios.{{ $cat }}.{{ $i }}.t" type="text"
                                   placeholder="Nombre del servicio" class="field flex-1 text-sm">
                            {{-- Toggle visible en landing --}}
                            <button wire:click="toggleServiceOnLanding('{{ $cat }}', {{ $i }})"
                                    title="{{ ($svc['on_landing'] ?? true) ? 'Quitar de la landing' : 'Mostrar en la landing' }}"
                                    class="shrink-0 transition-colors {{ ($svc['on_landing'] ?? true) ? 'text-emerald-500 hover:text-slate-400' : 'text-slate-300 hover:text-emerald-500' }}">
                                @if($svc['on_landing'] ?? true)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                @endif
                            </button>
                            <button wire:click="removeService('{{ $cat }}', {{ $i }})" class="btn-remove shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
                @endif
                @endforeach

                <div class="save-row">
                    <button wire:click="save('servicios')" class="btn-save">Guardar Servicios</button>
                </div>
            </div>
            @endif

            {{-- ══════════════════ GALERÍA ══════════════════ --}}
            @if($tab === 'galeria')
            <div class="space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="label-xs">Eyebrow</label>
                        <input wire:model="galeria.eyebrow" type="text" class="field">
                    </div>
                    <div>
                        <label class="label-xs">Título</label>
                        <input wire:model="galeria.title" type="text" class="field">
                    </div>
                </div>

                {{-- Sector tabs --}}
                @php $sectors = ['Industria', 'Minería', 'Ingeniería']; @endphp
                <div>
                    <div class="flex gap-2 mb-5">
                        @foreach($sectors as $s)
                        @php $cnt = count(array_filter($galeria['items'] ?? [], fn($it) => ($it['sector'] ?? '') === $s)); @endphp
                        <button type="button" wire:click="$set('galeriaFilter','{{ $s }}')"
                                class="px-4 py-2 text-sm font-semibold border rounded-lg transition-colors
                                       {{ $galeriaFilter === $s ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-400' }}">
                            {{ $s }}
                            <span class="ml-1.5 text-xs {{ $galeriaFilter === $s ? 'text-white/60' : 'text-slate-400' }}">{{ $cnt }}</span>
                        </button>
                        @endforeach
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                        @foreach(($galeria['items'] ?? []) as $ii => $item)
                        @if(($item['sector'] ?? '') === $galeriaFilter)
                        <div class="relative group">
                            @if(!empty($item['img']))
                            <img src="{{ $item['img'] }}" alt=""
                                 class="w-full h-32 object-cover rounded-lg border border-slate-200">
                            @else
                            <div class="w-full h-32 rounded-lg border-2 border-dashed border-slate-200 bg-slate-50 flex items-center justify-center text-slate-300">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            @endif
                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex flex-col items-center justify-center gap-2">
                                <button wire:click="openPicker('galeria.items.{{ $ii }}.img')"
                                        class="px-3 py-1.5 bg-white text-slate-800 text-xs font-semibold rounded-md hover:bg-slate-100">
                                    Cambiar
                                </button>
                                <button wire:click="removeItem({{ $ii }})"
                                        class="px-3 py-1.5 bg-red-500 text-white text-xs font-semibold rounded-md hover:bg-red-600">
                                    Eliminar
                                </button>
                            </div>
                        </div>
                        @endif
                        @endforeach

                        {{-- Add button --}}
                        <button wire:click="addItemAndPick('{{ $galeriaFilter }}')"
                                class="w-full h-32 rounded-lg border-2 border-dashed border-indigo-200 bg-indigo-50/50 flex flex-col items-center justify-center gap-1.5 text-indigo-500 hover:border-indigo-400 hover:bg-indigo-50 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span class="text-xs font-semibold">Agregar foto</span>
                        </button>
                    </div>
                </div>

                <div class="save-row">
                    <button wire:click="save('galeria')" class="btn-save">Guardar Galería</button>
                </div>
            </div>
            @endif

            {{-- ══════════════════ CONTACTO ══════════════════ --}}
            @if($tab === 'contacto')
            <div class="space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="label-xs">Teléfono</label>
                        <input wire:model="contacto.phone" type="text" class="field">
                    </div>
                    <div>
                        <label class="label-xs">Correo electrónico</label>
                        <input wire:model="contacto.email" type="email" class="field">
                    </div>
                    <div>
                        <label class="label-xs">Horario</label>
                        <input wire:model="contacto.hours" type="text" class="field">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="label-xs">Eyebrow</label>
                        <input wire:model="contacto.eyebrow" type="text" class="field">
                    </div>
                    <div>
                        <label class="label-xs">Título</label>
                        <input wire:model="contacto.title" type="text" class="field">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="label-xs">Descripción</label>
                        <textarea wire:model="contacto.body" rows="2" class="field"></textarea>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="label-xs">Sucursales (mapas)</span>
                        <button wire:click="addSucursal" class="btn-add">+ Agregar</button>
                    </div>
                    <div class="space-y-4">
                        @foreach(($contacto['sucursales'] ?? []) as $i => $suc)
                        <div class="border border-slate-200 rounded-lg p-4 bg-slate-50/50 space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-bold text-slate-500">Sucursal {{ $i + 1 }}</span>
                                <button wire:click="removeSucursal({{ $i }})" class="btn-remove">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            <div>
                                <label class="label-xs">Nombre</label>
                                <input wire:model="contacto.sucursales.{{ $i }}.title" type="text"
                                       placeholder="Sucursal Chihuahua" class="field">
                            </div>
                            <div>
                                <label class="label-xs">URL embed de Google Maps</label>
                                <textarea wire:model="contacto.sucursales.{{ $i }}.embed" rows="3"
                                          placeholder="https://www.google.com/maps/embed?..." class="field font-mono text-xs"></textarea>
                                <p class="text-xs text-slate-400 mt-1">
                                    Google Maps → Compartir → Insertar un mapa → copia el valor del atributo <code>src</code>.
                                </p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="save-row">
                    <button wire:click="save('contacto')" class="btn-save">Guardar Contacto</button>
                </div>
            </div>
            @endif

            {{-- ══════════════════ FOOTER ══════════════════ --}}
            @if($tab === 'footer')
            <div class="space-y-4">
                <div>
                    <label class="label-xs">Descripción (bajo el logo)</label>
                    <textarea wire:model="footer.description" rows="3" class="field"></textarea>
                </div>
                <div>
                    <label class="label-xs">Texto de copyright</label>
                    <input wire:model="footer.copyright" type="text" class="field">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="label-xs">WhatsApp (URL con código de país)</label>
                        <input wire:model="footer.whatsapp" type="url" class="field" placeholder="https://wa.me/52614…">
                    </div>
                    <div>
                        <label class="label-xs">Facebook (URL del perfil)</label>
                        <input wire:model="footer.facebook" type="url" class="field" placeholder="https://www.facebook.com/…">
                    </div>
                    <div>
                        <label class="label-xs">Teléfono (texto visible)</label>
                        <input wire:model="footer.phone" type="text" class="field">
                    </div>
                    <div>
                        <label class="label-xs">Correo electrónico</label>
                        <input wire:model="footer.email" type="email" class="field">
                    </div>
                </div>
                <div class="save-row">
                    <button wire:click="save('footer')" class="btn-save">Guardar Footer</button>
                </div>
            </div>
            @endif

        </div>
        </div>{{-- /flex-1 content panel --}}
    </div>{{-- /flex gap-5 --}}

    @include('livewire.landing.partials.media-picker')
</div>{{-- /w-full --}}

@push('scripts')
<style>
    [x-cloak] { display: none !important; }
    .label-xs {
        display: block;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: rgb(100 116 139);
        margin-bottom: 0.375rem;
    }
    .field {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid rgb(203 213 225);
        border-radius: 0.5rem;
        font-size: 0.875rem;
        color: rgb(15 23 42);
        background: #fff;
        transition: border-color 0.15s, box-shadow 0.15s;
        outline: none;
    }
    .field:focus {
        border-color: rgb(99 102 241);
        box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
    }
    .btn-add {
        font-size: 0.75rem;
        font-weight: 600;
        color: rgb(99 102 241);
        cursor: pointer;
        background: none;
        border: none;
        padding: 0;
    }
    .btn-add:hover { color: rgb(67 56 202); }
    .btn-remove {
        color: rgb(148 163 184);
        cursor: pointer;
        background: none;
        border: none;
        padding: 0.25rem;
        display: flex;
        align-items: center;
        border-radius: 0.375rem;
    }
    .btn-remove:hover { color: rgb(239 68 68); background: rgb(254 242 242); }
    .btn-save {
        padding: 0.625rem 1.5rem;
        background: rgb(99 102 241);
        color: #fff;
        font-size: 0.875rem;
        font-weight: 600;
        border-radius: 0.5rem;
        border: none;
        cursor: pointer;
        transition: background 0.15s;
    }
    .btn-save:hover { background: rgb(79 70 229); }
    .save-row {
        display: flex;
        justify-content: flex-end;
        padding-top: 1rem;
        border-top: 1px solid rgb(241 245 249);
    }
    .img-thumb {
        width: 4rem;
        height: 3rem;
        object-fit: cover;
        border-radius: 0.375rem;
        border: 1px solid rgb(226 232 240);
        background: rgb(248 250 252);
        flex-shrink: 0;
    }
    .img-thumb-tall {
        width: 100%;
        height: 8rem;
        object-fit: cover;
        border-radius: 0.5rem;
        border: 1px solid rgb(226 232 240);
    }
    .img-placeholder {
        width: 4rem;
        height: 3rem;
        border-radius: 0.375rem;
        border: 1.5px dashed rgb(203 213 225);
        background: rgb(248 250 252);
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .btn-upload {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.7rem;
        font-weight: 600;
        color: rgb(99 102 241);
        background: rgb(238 242 255);
        border: 1px solid rgb(199 210 254);
        padding: 0.3rem 0.6rem;
        border-radius: 0.375rem;
        cursor: pointer;
        white-space: nowrap;
        transition: background 0.15s;
    }
    .btn-upload:hover { background: rgb(224 231 255); }
</style>
@endpush
