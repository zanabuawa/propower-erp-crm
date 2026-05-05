<div class="flex flex-col bg-slate-100 -m-4 sm:-m-6 lg:-m-8 overflow-hidden" style="height:100vh;">

    {{-- ══ TOOLBAR ══════════════════════════════════════════════════════════ --}}
    <div class="shrink-0 bg-white border-b border-slate-200 px-4 py-2.5 flex items-center gap-3 z-20 shadow-sm">
        <a wire:navigate href="{{ route('inventory.warehouses.index') }}"
           class="group flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all shrink-0">
            <svg class="w-4 h-4 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="h-5 w-px bg-slate-200 shrink-0"></div>
        <div class="min-w-0 shrink-0">
            <p class="text-sm font-bold text-slate-800 leading-tight">{{ $warehouse->name }}</p>
            <p class="text-[10px] text-slate-400 font-medium uppercase tracking-widest">Asignación de ubicaciones</p>
        </div>
        <div class="h-5 w-px bg-slate-200 shrink-0"></div>
        <p class="text-xs text-slate-400 hidden md:block">
            <span class="font-semibold text-slate-600">1.</span> Haz click en un elemento del plano ·
            <span class="font-semibold text-slate-600">2.</span> Selecciona nivel/sección ·
            <span class="font-semibold text-slate-600">3.</span> Asigna productos del catálogo →
        </p>
        <div class="flex-1"></div>
        @if($layout)
        <a wire:navigate href="{{ route('inventory.warehouses.layout', $warehouse) }}"
           class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Editar plano
        </a>
        @endif
        <button wire:click="saveAll"
                class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-colors shadow-sm">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Guardar cambios
        </button>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,3000)"
         x-transition:leave="transition ease-in duration-300" x-transition:leave-end="opacity-0"
         class="absolute top-16 left-1/2 -translate-x-1/2 z-50 flex items-center gap-2 px-4 py-2.5 bg-emerald-600 text-white text-sm font-semibold rounded-2xl shadow-xl pointer-events-none">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- ══ MAIN 3-PANEL LAYOUT ═══════════════════════════════════════════ --}}
    <div class="flex flex-1 overflow-hidden">

        {{-- ── LEFT: Spot levels & assignments ────────────────────────────── --}}
        <div class="w-80 shrink-0 bg-white border-r border-slate-200 flex flex-col overflow-hidden"
             wire:key="left-panel-{{ $selectedSpotId ?? 0 }}">
            @if(!$selectedSpot)
                <div class="flex-1 flex flex-col items-center justify-center gap-3 p-8 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center">
                        <svg class="w-7 h-7 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-500">Selecciona un elemento</p>
                        <p class="text-xs text-slate-400 mt-1">Haz click en una estantería,<br>rack u otro elemento del plano</p>
                    </div>
                </div>
            @else
                @php
                    $sectionsCount = $selectedSpot->sections_count ?? 1;
                    $selectedLevel = $spotLevels->firstWhere('id', $selectedLevelId);
                    $cellProducts  = $selectedLevel
                        ? $selectedLevel->levelProducts->where('section', $selectedSection)->values()
                        : collect();
                @endphp

                {{-- Spot header --}}
                <div class="shrink-0 px-4 py-3 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-[10px] font-black shrink-0"
                             style="background-color: {{ $selectedSpot->color }}">
                            {{ strtoupper(substr($selectedSpot->type, 0, 2)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-bold text-slate-800 truncate">{{ $selectedSpot->label }}</p>
                            <p class="text-[10px] text-slate-400 font-medium">
                                {{ \App\Models\WarehouseSpot::TYPES[$selectedSpot->type] ?? '' }}
                                · {{ $selectedSpot->levels_count }} niv.
                                @if($sectionsCount > 1) · {{ $sectionsCount }} sec. @endif
                            </p>
                        </div>
                        <button wire:click="$set('showInventoryModal', true)"
                                class="flex items-center gap-1.5 px-2.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 text-[10px] font-bold rounded-xl transition-colors shadow-sm shrink-0 border border-indigo-100">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Ver Inventario
                        </button>
                        <button wire:click="$set('selectedSpotId', null)"
                            class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-300 hover:text-slate-500 hover:bg-slate-100 transition-colors shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                {{-- ── Scrollable body: sections list + products ────────────── --}}
                <div class="flex-1 overflow-y-auto">

                {{-- Sections List --}}
                <div class="px-3 pt-3 pb-2 space-y-2">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 flex items-center gap-2">
                        Secciones del elemento
                    </p>

                    @foreach(range(1, $sectionsCount) as $s)
                    @php
                        $sectionProdsCount = $spotLevels->sum(fn($l) => $l->levelProducts->where('section', $s)->count());
                        $isSectionActive = $selectedSection === $s;
                    @endphp
                    <div @class([
                        'border rounded-xl transition-all overflow-hidden',
                        'bg-indigo-50 border-indigo-200 shadow-sm' => $isSectionActive,
                        'bg-white border-slate-100 hover:border-slate-200' => !$isSectionActive
                    ])>
                        <button wire:click="$set('selectedSection', {{ $s }})"
                                class="w-full text-left px-3 py-2.5 flex items-center justify-between group">
                            <div class="flex items-center gap-2">
                                <div @class([
                                    'w-6 h-6 rounded-lg flex items-center justify-center text-[10px] font-bold transition-colors',
                                    'bg-indigo-600 text-white' => $isSectionActive,
                                    'bg-slate-100 text-slate-400 group-hover:bg-slate-200' => !$isSectionActive
                                ])>
                                    {{ $s }}
                                </div>
                                <span @class([
                                    'text-xs font-bold transition-colors',
                                    'text-indigo-700' => $isSectionActive,
                                    'text-slate-600'  => !$isSectionActive
                                ])>Sección {{ $s }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($sectionProdsCount > 0)
                                <span @class([
                                    'text-[9px] font-bold px-1.5 py-0.5 rounded-full',
                                    'bg-indigo-200 text-indigo-700' => $isSectionActive,
                                    'bg-emerald-50 text-emerald-600' => !$isSectionActive
                                ])>
                                    {{ $sectionProdsCount }} prod.
                                </span>
                                @endif
                                <svg @class([
                                    'w-3.5 h-3.5 transition-transform',
                                    'rotate-90 text-indigo-400' => $isSectionActive,
                                    'text-slate-300' => !$isSectionActive
                                ]) fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </button>

                        @if($isSectionActive)
                        <div class="px-2 pb-2 space-y-1 bg-white/50 border-t border-indigo-100 pt-1.5 mx-1 mb-1 rounded-b-lg">
                            <p class="text-[9px] font-bold text-indigo-400 uppercase tracking-widest px-2 mb-1">Niveles</p>
                            @foreach($spotLevels->sortByDesc('level_number') as $level)
                            @php
                                $cellCount = $level->levelProducts->where('section', $s)->count();
                                $isActiveCell = $selectedLevelId === $level->id && $selectedSection === $s;
                            @endphp
                            <button wire:click="selectLevelSection({{ $level->id }}, {{ $s }})"
                                    @class([
                                        'w-full flex items-center justify-between px-2.5 py-2 rounded-lg text-left transition-all border',
                                        'bg-indigo-600 border-indigo-500 text-white shadow-sm' => $isActiveCell,
                                        'bg-white border-slate-100 text-slate-600 hover:bg-slate-50' => !$isActiveCell
                                    ])>
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-bold">Nivel {{ $level->level_number }}</span>
                                    @if($loop->first)<span class="text-[8px] opacity-50 font-medium">alto</span>@endif
                                    @if($loop->last)<span class="text-[8px] opacity-50 font-medium">bajo</span>@endif
                                </div>
                                @if($cellCount > 0)
                                <span @class([
                                    'text-[9px] font-bold',
                                    'text-indigo-200' => $isActiveCell,
                                    'text-emerald-600' => !$isActiveCell
                                ])>{{ $cellCount }} prod.</span>
                                @else
                                <span class="text-[9px] opacity-30 italic">vacío</span>
                                @endif
                            </button>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endforeach

                    {{-- Active cell label --}}
                    @if($selectedLevelId && $selectedLevel)
                    <div class="mt-2 p-2 bg-indigo-50 rounded-xl border border-indigo-100">
                        <p class="text-[10px] text-indigo-600 font-bold text-center">
                            Editando: {{ $selectedLevel->display_label }} · Sección {{ $selectedSection }}
                        </p>
                    </div>
                    @else
                    <p class="mt-2 text-[10px] text-amber-500 font-medium text-center flex items-center justify-center gap-1">
                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Selecciona un nivel para asignar productos
                    </p>
                    @endif
                </div>

                {{-- ── Products in selected cell ─────────────────────────── --}}
                <div x-data="{ editingId: null, editingQty: 0 }">

                    @if($cellProducts->count() > 0)
                    <div class="divide-y divide-slate-50">
                        @foreach($cellProducts as $lp)
                        <div class="px-3 py-2.5 group">

                            {{-- Product row --}}
                            <div class="flex items-center gap-2">
                                @if($lp->product->primaryImage)
                                    <img src="{{ Storage::url($lp->product->primaryImage->path) }}"
                                         class="w-8 h-8 rounded-lg object-cover shrink-0 border border-slate-100">
                                @else
                                    <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                    </div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="text-[11px] font-semibold text-slate-700 truncate leading-tight">{{ $lp->product->name }}</p>
                                    <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                        <span class="text-[9px] font-mono text-slate-400">{{ $lp->product->sku }}</span>
                                        @if($lp->assigned_qty)
                                        <span class="text-[9px] font-bold text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded-full">
                                            {{ number_format($lp->assigned_qty) }} {{ $lp->product->unitOfMeasure?->abbreviation ?? 'u' }}
                                        </span>
                                        @endif
                                    </div>
                                </div>
                                {{-- Edit qty button --}}
                                <button @click="editingId = (editingId === {{ $lp->id }} ? null : {{ $lp->id }}); editingQty = {{ $lp->assigned_qty ?? 0 }}"
                                    class="w-6 h-6 flex items-center justify-center rounded-md text-slate-300 hover:text-indigo-500 hover:bg-indigo-50 transition-colors opacity-0 group-hover:opacity-100 shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                {{-- Remove button --}}
                                <button wire:click="removeProduct({{ $lp->id }})" wire:confirm="¿Quitar este producto de la celda? Permanecerá en el almacén."
                                    class="w-6 h-6 flex items-center justify-center rounded-md text-slate-300 hover:text-red-400 hover:bg-red-50 transition-colors opacity-0 group-hover:opacity-100 shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                            {{-- Inline qty editor --}}
                            <div x-show="editingId === {{ $lp->id }}"
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 -translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="mt-2 flex items-center gap-2 px-2 py-2 bg-indigo-50 rounded-xl border border-indigo-200">
                                <label class="text-[10px] font-bold text-indigo-600 shrink-0">Cant.</label>
                                <input type="number" x-model.number="editingQty" min="0"
                                       class="flex-1 w-0 px-2 py-1 rounded-lg border border-indigo-300 bg-white text-sm font-bold text-slate-700 text-center focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                <span class="text-[9px] text-indigo-500 shrink-0">{{ $lp->product->unitOfMeasure?->abbreviation ?? 'u' }}</span>
                                <button @click="$wire.updateProductQty(editingId, editingQty); editingId = null"
                                    class="px-2.5 py-1 bg-indigo-600 hover:bg-indigo-700 text-white text-[10px] font-bold rounded-lg transition-colors shrink-0">
                                    OK
                                </button>
                                <button @click="editingId = null"
                                    class="w-6 h-6 flex items-center justify-center text-slate-400 hover:text-slate-600 shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                        </div>
                        @endforeach
                    </div>
                    @elseif($selectedLevelId)
                    <div class="flex flex-col items-center justify-center gap-2 py-8 text-center px-4">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                        </div>
                        <p class="text-xs text-slate-400 font-medium">Celda vacía — asigna productos<br>desde el catálogo →</p>
                    </div>
                    @endif
                </div>{{-- /x-data products --}}

                </div>{{-- /scrollable body --}}
            @endif
        </div>

        {{-- ── CENTER: Floor plan (read-only) ──────────────────────────────── --}}
        <div class="flex-1 overflow-auto bg-slate-300/50 p-8 flex items-start justify-center">
            @if(!$layout)
                <div class="flex flex-col items-center justify-center gap-4 mt-24 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-white/80 flex items-center justify-center shadow-sm">
                        <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-slate-600 font-bold">Este almacén no tiene plano</p>
                        <p class="text-sm text-slate-400 mt-1">Diseña el plano primero para poder asignar ubicaciones</p>
                    </div>
                    <a wire:navigate href="{{ route('inventory.warehouses.layout', $warehouse) }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition-colors shadow-lg shadow-indigo-500/25">
                        Diseñar plano
                    </a>
                </div>
            @else
                {{-- Canvas --}}
                <div wire:ignore
                     x-data="warehouseAssignmentCanvas({
                         gridCols:    {{ $gridCols }},
                         gridRows:    {{ $gridRows }},
                         ppc:         36,
                         polygons:    @js($polygons),
                         spots:       @js($spots->values()->toArray()),
                         wallColor:   @js($wallColor),
                         floorColor:  @js($floorColor),
                         selectedSpotId: {{ $selectedSpotId ?? 'null' }},
                     })"
                     class="relative inline-block shadow-2xl rounded-lg border border-slate-300 select-none cursor-pointer"
                     :style="`width:${gridCols*ppc}px; height:${gridRows*ppc}px; background:#FAFBFC;`">

                    {{-- SVG: grid + zone borders --}}
                    <svg class="absolute inset-0 pointer-events-none rounded-lg overflow-hidden"
                         :width="gridCols*ppc" :height="gridRows*ppc">
                        <defs>
                            <pattern id="wa-minor" x="0" y="0" :width="ppc" :height="ppc" patternUnits="userSpaceOnUse">
                                <path :d="`M ${ppc} 0 L 0 0 0 ${ppc}`" fill="none" stroke="#CBD5E1" stroke-width="0.5" opacity="0.6"/>
                            </pattern>
                            <pattern id="wa-major" x="0" y="0" :width="ppc*5" :height="ppc*5" patternUnits="userSpaceOnUse">
                                <rect :width="ppc*5" :height="ppc*5" fill="url(#wa-minor)"/>
                                <path :d="`M ${ppc*5} 0 L 0 0 0 ${ppc*5}`" fill="none" stroke="#94A3B8" stroke-width="1" opacity="0.4"/>
                            </pattern>
                        </defs>
                        <rect :width="gridCols*ppc" :height="gridRows*ppc" fill="url(#wa-major)" opacity="0.4"/>
                        <rect x-show="polygons.length > 0" :width="gridCols*ppc" :height="gridRows*ppc" fill="rgba(15,23,42,0.22)"/>
                        <path x-show="polygons.length > 0"
                              :d="closedZonesBorderPath"
                              fill="none" :stroke="wallColor" stroke-width="3" stroke-linejoin="round"/>
                    </svg>

                    {{-- Zone fills --}}
                    <template x-for="zone in polygons" :key="'waz-'+zone.id">
                        <div class="absolute inset-0 pointer-events-none"
                             :style="`clip-path:polygon(${zone.points.map(p=>p.col*ppc+'px '+p.row*ppc+'px').join(',')});background-color:${zone.floorColor||floorColor};`">
                        </div>
                    </template>

                    {{-- Spots --}}
                    <template x-for="spot in spots" :key="'was-'+spot.id">
                        <div class="absolute rounded flex flex-col items-center justify-center overflow-hidden transition-shadow"
                             @click="selectSpot(spot)"
                             :style="spotStyle(spot)">

                            {{-- Section dividers (vertical) --}}
                            <template x-for="si in Array.from({length:Math.max(0,(spot.sections_count||1)-1)},(_,i)=>i+1)" :key="'wsec-'+spot.id+'-'+si">
                                <div class="absolute top-0 bottom-0 pointer-events-none"
                                     :style="`left:${(si/(spot.sections_count||1))*100}%;width:1px;background:${spot.color};opacity:0.35;`"></div>
                            </template>

                            {{-- Level dividers (horizontal) --}}
                            <template x-for="li in Array.from({length:Math.max(0,(spot.levels_count||1)-1)},(_,i)=>i+1)" :key="'wlv-'+spot.id+'-'+li">
                                <div class="absolute left-0 right-0 pointer-events-none"
                                     :style="`top:${(li/(spot.levels_count||1))*100}%;height:1px;background:${spot.color};opacity:0.22;`"></div>
                            </template>

                            <span class="truncate px-1 w-full text-center pointer-events-none relative z-10"
                                  :style="`font-size:${Math.min(11,ppc*0.28)}px;font-weight:700;color:${spot.color};`"
                                  x-text="spot.label"></span>

                            {{-- Indicator dot: has products --}}
                            <template x-if="spot.has_products">
                                <span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-emerald-400 shadow-sm z-10"></span>
                            </template>
                        </div>
                    </template>
                </div>

                {{-- Legend --}}
                <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex items-center gap-4 px-4 py-2 bg-white/80 backdrop-blur-sm rounded-full border border-slate-200 shadow-sm text-[11px] text-slate-500 pointer-events-none">
                    <span class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 inline-block"></span>
                        Con productos asignados
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-slate-200 border border-slate-300 inline-block"></span>
                        Sin asignaciones
                    </span>
                </div>
            @endif
        </div>

        {{-- ── RIGHT: Product catalog ───────────────────────────────────────── --}}
        <div class="w-80 shrink-0 bg-white border-l border-slate-200 flex flex-col overflow-hidden"
             x-data="{ pendingId: null, pendingQty: 1 }">

            {{-- Search + Filter --}}
            <div class="shrink-0 px-4 py-3 border-b border-slate-100 space-y-2.5">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Catálogo de productos</p>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input wire:model.live.debounce.300ms="productSearch" type="text" placeholder="Buscar producto..."
                        class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500/10 text-sm outline-none transition-all">
                </div>
                <select wire:model.live="categoryFilter"
                    class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-indigo-400 text-sm text-slate-600 outline-none transition-all">
                    <option value="">Todas las categorías</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                @if(!$selectedLevelId)
                <p class="text-[10px] text-amber-500 flex items-center gap-1">
                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Selecciona una celda en la cuadrícula (← izquierda)
                </p>
                @endif
            </div>

            {{-- Product list --}}
            <div class="flex-1 overflow-y-auto" @click.outside="pendingId = null">

                {{-- Available (unplaced) products --}}
                @if($availableProducts->count())
                <div class="px-4 pt-3 pb-1">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                        Disponibles
                        <span class="bg-slate-100 text-slate-500 rounded-full px-1.5 py-0.5 text-[9px] font-black">{{ $availableProducts->count() }}</span>
                    </p>
                </div>
                <div class="px-3 pb-3 space-y-1">
                    @foreach($availableProducts as $product)
                    @php
                        $stock        = $product->stocks->first();
                        $pid          = $product->id;
                        $unit         = $product->unitOfMeasure?->abbreviation ?? 'u';
                        $totalQty     = (int)($stock?->quantity ?? 0);
                        $qty          = number_format($totalQty, 0);
                        $locatedQty   = $locatedQtys[$pid] ?? 0;
                        $unlocatedQty = max(0, $totalQty - $locatedQty);
                        $locationPct  = $totalQty > 0 ? min(100, round($locatedQty / $totalQty * 100)) : 0;
                        $defaultQty   = max(1, $unlocatedQty);
                    @endphp
                    <div x-data @click.stop>

                        {{-- Product row --}}
                        <button @click="{{ ($selectedLevelId && $unlocatedQty > 0) ? "pendingId = (pendingId === $pid ? null : $pid); pendingQty = $defaultQty" : '' }}"
                            @class([
                                'w-full flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-left transition-all',
                                'hover:bg-indigo-50 cursor-pointer' => (bool)$selectedLevelId && $unlocatedQty > 0,
                                'opacity-40 cursor-not-allowed'     => !$selectedLevelId || $unlocatedQty <= 0,
                            ])
                            :class="{{ $selectedLevelId ? "pendingId === $pid ? 'bg-indigo-50 ring-2 ring-indigo-200' : ''" : "''" }}">

                            {{-- Image / initials --}}
                            @if($product->primaryImage)
                                <img src="{{ Storage::url($product->primaryImage->path) }}"
                                     class="w-10 h-10 rounded-xl object-cover shrink-0 border border-slate-100 shadow-sm">
                            @else
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center shrink-0">
                                    <span class="text-[10px] font-black text-slate-400">{{ strtoupper(substr($product->name, 0, 2)) }}</span>
                                </div>
                            @endif

                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-semibold text-slate-700 truncate leading-tight">{{ $product->name }}</p>
                                <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                    <p class="text-[9px] font-mono text-slate-400">{{ $product->sku }}</p>
                                    @if($locationPct >= 100)
                                    <span class="inline-flex items-center gap-0.5 text-[8px] font-bold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded-full border border-emerald-100">
                                        <svg class="w-2 h-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        100% ubicado
                                    </span>
                                    @elseif($locatedQty > 0)
                                    <span class="inline-flex items-center gap-0.5 text-[8px] font-bold text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded-full border border-amber-100">
                                        {{ $locationPct }}% ubicado
                                    </span>
                                    @endif
                                </div>
                                {{-- Location progress bar --}}
                                @if($totalQty > 0)
                                <div class="mt-1.5 flex items-center gap-1.5">
                                    <div class="flex-1 h-1 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full transition-all {{ $locationPct >= 100 ? 'bg-emerald-400' : ($locationPct > 0 ? 'bg-amber-400' : 'bg-slate-200') }}"
                                             style="width:{{ $locationPct }}%"></div>
                                    </div>
                                    <span class="text-[8px] font-semibold text-slate-400 shrink-0 tabular-nums">
                                        {{ number_format($locatedQty) }}/{{ $qty }}
                                    </span>
                                </div>
                                @endif
                            </div>
                            <div class="text-right shrink-0">
                                @if($unlocatedQty > 0)
                                <p class="text-sm font-black text-amber-500">{{ number_format($unlocatedQty) }}</p>
                                <p class="text-[8px] text-amber-400 font-medium">sin ubicar</p>
                                @else
                                <p class="text-sm font-black text-emerald-500">{{ $qty }}</p>
                                <p class="text-[9px] text-slate-400">{{ $unit }}</p>
                                @endif
                            </div>
                        </button>

                        {{-- Inline quantity form (expands when this product is pending) --}}
                        @if($selectedLevelId && $unlocatedQty > 0)
                        <div x-show="pendingId === {{ $pid }}"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="mx-1 mb-1 px-3 py-2.5 bg-indigo-50 border border-indigo-200 rounded-xl space-y-2">
                            <p class="text-[10px] font-bold text-indigo-700 truncate">{{ $product->name }}</p>
                            {{-- Location summary --}}
                            <div class="flex items-center justify-between text-[9px] px-0.5 -mb-0.5">
                                <span class="text-indigo-400">Stock: {{ $qty }} {{ $unit }}</span>
                                @if($unlocatedQty > 0)
                                <span class="font-bold text-amber-500">{{ number_format($unlocatedQty) }} sin ubicar</span>
                                @else
                                <span class="font-bold text-emerald-500">✓ Todo ubicado</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="text-[10px] text-indigo-600 font-semibold shrink-0">Cant.</label>
                                <input type="number" x-model.number="pendingQty" min="1" max="{{ $unlocatedQty }}"
                                    class="flex-1 px-2 py-1.5 rounded-lg border border-indigo-300 bg-white text-sm font-bold text-slate-700 text-center focus:outline-none focus:ring-2 focus:ring-indigo-400 w-0">
                                <span class="text-[10px] text-indigo-500 shrink-0">{{ $unit }}</span>
                            </div>
                            <div class="flex gap-2">
                                <button @click="$wire.assignProduct({{ $pid }}, pendingQty); pendingId = null"
                                    class="flex-1 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg transition-colors">
                                    Asignar
                                </button>
                                <button @click="pendingId = null"
                                    class="px-3 py-1.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-500 text-xs font-semibold rounded-lg transition-colors">
                                    Cancelar
                                </button>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @elseif(!$productSearch && !$categoryFilter)
                <div class="px-4 py-8 text-center">
                    <p class="text-sm font-medium text-slate-400">Sin stock disponible en este almacén</p>
                </div>
                @else
                <div class="px-4 py-8 text-center">
                    <p class="text-sm font-medium text-slate-400">Sin resultados</p>
                </div>
                @endif


            </div>
        </div>
        </div>{{-- /main --}}

        {{-- ══ INVENTORY VISUALIZATION MODAL ══════════════════════════════════ --}}
        <div x-cloak x-show="$wire.showInventoryModal"
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100">

        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="$wire.showInventoryModal = false"></div>

        <div class="relative bg-white w-full max-w-5xl max-h-[90vh] rounded-3xl shadow-2xl flex flex-col overflow-hidden"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0">

            @if($selectedSpot)
            <div class="shrink-0 px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Vista Detallada: {{ $selectedSpot->label }}</h3>
                    <p class="text-xs text-slate-400 font-medium uppercase tracking-widest">Inventario asignado por ubicación</p>
                </div>
                <button @click="$wire.showInventoryModal = false" class="w-10 h-10 flex items-center justify-center rounded-xl hover:bg-slate-200 transition-colors">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div x-data="{ activeCell: null }" class="flex-1 flex overflow-hidden min-h-0">

            {{-- Grid table --}}
            <div class="flex-1 overflow-auto p-6 bg-slate-100/50">
                <div class="inline-block min-w-full">
                    <table class="border-separate" style="border-spacing: 8px;">
                        <thead>
                            <tr>
                                <th class="sticky left-0 z-10 p-2 text-[10px] font-black text-slate-400 bg-slate-100/50 backdrop-blur-sm rounded-lg">NIVEL</th>
                                @foreach(range(1, $sectionsCount) as $s)
                                <th class="p-3 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-600 min-w-[200px]">
                                    Sección {{ $s }}
                                </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($spotLevels->sortByDesc('level_number') as $level)
                            <tr>
                                <td class="sticky left-0 z-10 p-2 bg-slate-100/50 backdrop-blur-sm rounded-lg">
                                    <div class="text-sm font-black text-slate-700 text-center">N{{ $level->level_number }}</div>
                                </td>
                                @foreach(range(1, $sectionsCount) as $s)
                                @php
                                    $cellProds = $level->levelProducts->where('section', $s);
                                    $cellJson  = json_encode([
                                        'level'    => $level->level_number,
                                        'section'  => $s,
                                        'products' => $cellProds->map(fn($lp) => [
                                            'name'         => $lp->product->name,
                                            'sku'          => $lp->product->sku,
                                            'assigned_qty' => $lp->assigned_qty,
                                            'unit'         => $lp->product->unitOfMeasure?->abbreviation ?? 'u',
                                            'image'        => $lp->product->primaryImage ? Storage::url($lp->product->primaryImage->path) : null,
                                            'category'     => $lp->product->category?->name,
                                        ])->values()->toArray(),
                                    ]);
                                @endphp
                                <td class="p-3 bg-white border border-slate-100 rounded-2xl shadow-sm align-top cursor-pointer transition-all"
                                    :class="activeCell?.level === {{ $level->level_number }} && activeCell?.section === {{ $s }}
                                        ? 'ring-2 ring-indigo-400 bg-indigo-50/40 shadow-indigo-100'
                                        : 'hover:bg-slate-50 hover:shadow-md'"
                                    data-cell="{{ $cellJson }}"
                                    @click="activeCell = JSON.parse($el.dataset.cell)">
                                    @if($cellProds->count() > 0)
                                        <div class="space-y-1.5">
                                            @foreach($cellProds as $lp)
                                            <div class="flex items-center gap-2 p-1.5 bg-slate-50 rounded-xl border border-slate-100 pointer-events-none">
                                                @if($lp->product->primaryImage)
                                                    <img src="{{ Storage::url($lp->product->primaryImage->path) }}" class="w-7 h-7 rounded-lg object-cover shrink-0 border border-slate-200">
                                                @else
                                                    <div class="w-7 h-7 rounded-lg bg-white flex items-center justify-center shrink-0 border border-slate-100">
                                                        <svg class="w-3.5 h-3.5 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                                    </div>
                                                @endif
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-[10px] font-bold text-slate-700 leading-tight truncate">{{ $lp->product->name }}</p>
                                                    @if($lp->assigned_qty)
                                                    <p class="text-[9px] font-black text-indigo-500">{{ number_format($lp->assigned_qty) }} {{ $lp->product->unitOfMeasure?->abbreviation }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="py-4 flex flex-col items-center justify-center opacity-20 pointer-events-none">
                                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                            <span class="text-[8px] font-bold mt-1">VACÍO</span>
                                        </div>
                                    @endif
                                </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Detail side panel --}}
            <div x-show="activeCell !== null"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-x-4"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 translate-x-4"
                 class="w-72 shrink-0 bg-white border-l border-slate-200 flex flex-col overflow-hidden">
                <template x-if="activeCell">
                    <div class="flex flex-col h-full">

                        {{-- Panel header --}}
                        <div class="shrink-0 px-4 py-3.5 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-bold text-slate-700"
                                   x-text="`Nivel ${activeCell.level}  ·  Sección ${activeCell.section}`"></p>
                                <p class="text-[10px] text-slate-400 font-medium mt-0.5"
                                   x-text="activeCell.products.length === 0 ? 'Celda vacía' : `${activeCell.products.length} producto(s) asignado(s)`"></p>
                            </div>
                            <button @click="activeCell = null"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-300 hover:text-slate-500 hover:bg-slate-200 transition-colors shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        {{-- Product list --}}
                        <div class="flex-1 overflow-y-auto p-3 space-y-2">

                            <template x-if="activeCell.products.length === 0">
                                <div class="flex flex-col items-center justify-center gap-3 py-12 opacity-40">
                                    <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                    <p class="text-xs text-slate-400 font-medium text-center">Sin productos asignados<br>a esta celda</p>
                                </div>
                            </template>

                            <template x-for="(prod, idx) in activeCell.products" :key="idx">
                                <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 space-y-3">

                                    {{-- Product header row --}}
                                    <div class="flex items-start gap-3">
                                        <template x-if="prod.image">
                                            <img :src="prod.image" class="w-14 h-14 rounded-xl object-cover shrink-0 border border-slate-200 shadow-sm">
                                        </template>
                                        <template x-if="!prod.image">
                                            <div class="w-14 h-14 rounded-xl bg-white flex items-center justify-center shrink-0 border border-slate-200">
                                                <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                            </div>
                                        </template>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-bold text-slate-800 leading-snug" x-text="prod.name"></p>
                                            <p class="text-[10px] font-mono text-slate-400 mt-0.5" x-text="prod.sku"></p>
                                            <template x-if="prod.category">
                                                <p class="text-[9px] text-slate-400 mt-1" x-text="prod.category"></p>
                                            </template>
                                        </div>
                                    </div>

                                    {{-- Quantity badge --}}
                                    <div class="flex items-center justify-between px-3 py-2 bg-white rounded-xl border border-slate-100">
                                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wide">Cantidad asignada</span>
                                        <template x-if="prod.assigned_qty">
                                            <span class="text-sm font-black text-indigo-600"
                                                  x-text="`${prod.assigned_qty} ${prod.unit}`"></span>
                                        </template>
                                        <template x-if="!prod.assigned_qty">
                                            <span class="text-xs text-slate-300 italic">—</span>
                                        </template>
                                    </div>

                                </div>
                            </template>

                        </div>
                    </div>
                </template>
            </div>

            </div>{{-- /x-data modal body --}}
            @endif
        </div>
        </div>

        </div>

        @script
<script>
Alpine.data('warehouseAssignmentCanvas', (cfg) => ({

    ppc:          cfg.ppc      || 36,
    gridCols:     cfg.gridCols || 24,
    gridRows:     cfg.gridRows || 20,
    polygons:     cfg.polygons  || [],
    spots:        cfg.spots     || [],
    wallColor:    cfg.wallColor  || '#1E293B',
    floorColor:   cfg.floorColor || '#FFFFFF',
    selectedSpotId: cfg.selectedSpotId || null,

    init() {
        // Keep Alpine in sync when Livewire changes selectedSpotId server-side
        this.$wire.watch('selectedSpotId', id => { this.selectedSpotId = id; });

        // Update has_products badge after assignment/removal
        this.$wire.on('spot-assignment-updated', ({ spotId, hasProducts }) => {
            const i = this.spots.findIndex(s => s.id === spotId);
            if (i !== -1) {
                this.spots[i] = { ...this.spots[i], has_products: hasProducts };
                this.spots = [...this.spots];
            }
        });
    },

    get closedZonesBorderPath() {
        return this.polygons
            .filter(z => z.points && z.points.length >= 3)
            .map(z => z.points.map((p, i) => `${i===0?'M':'L'}${p.col*this.ppc},${p.row*this.ppc}`).join(' ') + 'Z')
            .join(' ');
    },

    selectSpot(spot) {
        this.selectedSpotId = spot.id;
        this.$wire.selectSpot(spot.id);
    },

    hexToRgba(hex, alpha) {
        const r = parseInt(hex.slice(1,3),16), g = parseInt(hex.slice(3,5),16), b = parseInt(hex.slice(5,7),16);
        return `rgba(${r},${g},${b},${alpha})`;
    },

    spotStyle(spot) {
        const selected = this.selectedSpotId === spot.id;
        const x = spot.col * this.ppc + 1;
        const y = spot.row * this.ppc + 1;
        const w = spot.width_cells * this.ppc - 2;
        const h = spot.depth_cells * this.ppc - 2;
        const border  = selected ? `2.5px solid ${spot.color}` : `1.5px solid ${spot.color}88`;
        const shadow  = selected
            ? `0 0 0 3px ${this.hexToRgba(spot.color, 0.35)}, 0 4px 12px ${this.hexToRgba(spot.color, 0.4)}`
            : '1px 1px 0 rgba(0,0,0,0.08)';
        const bg      = selected ? `${spot.color}30` : `${spot.color}18`;
        return `left:${x}px;top:${y}px;width:${w}px;height:${h}px;`
             + `background-color:${bg};border:${border};box-shadow:${shadow};`
             + `z-index:${selected?5:1};position:absolute;`;
    },

}));
</script>
@endscript
