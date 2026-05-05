<div
    x-data="warehouseEditor({
        gridCols:   {{ $this->gridCols }},
        gridRows:   {{ $this->gridRows }},
        ppc:        36,
        polygons:   @js($this->polygons),
        spots:      @js($spots),
        wallColor:  @js($this->wallColor),
        floorColor: @js($this->floorColor),
        bgColor:    @js($this->bgColor),
    })"
    class="flex flex-col bg-slate-100 -m-4 sm:-m-6 lg:-m-8 overflow-hidden"
    style="height: 100vh;"
    @keydown.escape.window="mode = 'select'; placingType = null; if(editingZoneVertices){ stopEditVertices() } else { deselectZone() } if(selectedSpotId){ selectedSpotId = null; $wire.closeSpotPanel() }"
>

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
            <p class="text-[10px] text-slate-400 font-medium uppercase tracking-widest">Editor de plano</p>
        </div>

        <div class="h-5 w-px bg-slate-200 shrink-0"></div>

        {{-- Tool buttons --}}
        <div class="flex items-center gap-1 p-1 bg-slate-100 rounded-xl">
            <button type="button" @click="mode = 'select'; placingType = null; if(editingZoneVertices) stopEditVertices()"
                :class="mode === 'select' ? 'bg-white shadow-sm text-indigo-600' : 'text-slate-500 hover:text-slate-700'"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5"/>
                </svg>
                <span class="hidden md:inline">Seleccionar</span>
            </button>
            <button type="button" @click="mode = 'draw'; placingType = null; deselectZone(); if(selectedSpotId){ selectedSpotId = null; $wire.closeSpotPanel() }"
                :class="mode === 'draw' ? 'bg-white shadow-sm text-amber-600' : 'text-slate-500 hover:text-slate-700'"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
                <span class="hidden md:inline">Dibujar</span>
            </button>
        </div>

        <div x-show="mode === 'draw'" class="flex items-center gap-1.5 flex-wrap">
            <input x-model="drawingLabel" :placeholder="nextZoneLabel" type="text"
                class="px-2.5 py-1.5 text-xs font-semibold border border-amber-200 rounded-lg w-28 focus:outline-none focus:border-amber-400 bg-amber-50 text-amber-800 placeholder-amber-400"/>
            <span class="px-2 py-1 bg-amber-50 border border-amber-200 text-amber-700 rounded-lg text-xs font-bold">
                <span x-text="drawingPoints.length"></span> pts
            </span>
            <button @click="undoLastPoint" :disabled="drawingPoints.length === 0"
                class="px-2.5 py-1.5 bg-white border border-slate-200 text-slate-600 rounded-lg hover:bg-slate-50 disabled:opacity-40 text-xs font-medium">↩</button>
            <button @click="clearPolygon"
                class="px-2.5 py-1.5 bg-white border border-slate-200 text-red-500 rounded-lg hover:bg-red-50 text-xs font-medium">Limpiar</button>
            <button @click="canClosePolygon && closePolygon()"
                :disabled="!canClosePolygon"
                :title="canClosePolygon ? 'Cerrar zona' : 'Vuelve al punto inicial (●) para poder cerrar'"
                :class="canClosePolygon
                    ? 'bg-emerald-600 hover:bg-emerald-700 text-white border-emerald-600'
                    : 'bg-white text-slate-400 border-slate-200 cursor-not-allowed opacity-50'"
                class="px-2.5 py-1.5 border rounded-lg text-xs font-bold transition-all">
                ✓ Cerrar zona
            </button>
            <span class="text-slate-400 text-xs hidden lg:inline">· Dibuja libremente · vuelve al punto rojo <span class="text-red-400 font-bold">●</span> para cerrar</span>
        </div>

        <div x-show="mode === 'place'" class="flex items-center gap-1.5">
            <span class="px-2 py-1 bg-indigo-50 border border-indigo-200 text-indigo-700 rounded-lg text-xs font-bold">
                Colocando: <span x-text="placingType" class="capitalize"></span>
            </span>
            <span class="text-slate-400 text-xs hidden md:inline">· Click en el canvas · Esc para cancelar</span>
        </div>

        <div class="flex-1"></div>

        <button wire:click="$toggle('showGridPanel')"
            class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Cuadrícula
        </button>

        <button @click="saveAll" wire:loading.attr="disabled"
            class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-xs font-bold rounded-xl transition-all shadow-lg shadow-indigo-500/25 hover:scale-[1.02] active:scale-[0.98] disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:scale-100">
            <svg wire:loading.remove class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            <svg wire:loading class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            Guardar plano
        </button>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
             x-transition:leave="transition ease-in duration-300" x-transition:leave-end="opacity-0"
             class="absolute top-16 left-1/2 -translate-x-1/2 z-50 flex items-center gap-2 px-4 py-2.5 bg-emerald-600 text-white text-sm font-semibold rounded-2xl shadow-xl pointer-events-none">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- ══ MAIN LAYOUT ══════════════════════════════════════════════════════ --}}
    <div class="flex flex-1 overflow-hidden">

        {{-- ── LEFT SIDEBAR ────────────────────────────────────────────────── --}}
        <div class="w-60 shrink-0 bg-white border-r border-slate-200 flex flex-col overflow-y-auto">
            <div class="px-6 pt-7 pb-4">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Elementos</p>
                <p class="text-[10px] text-slate-400 mt-2 leading-relaxed">Click → selecciona tipo<br>Click en canvas → coloca</p>
            </div>
            @php
                $palette = [
                    ['type'=>'estanteria','label'=>'Estantería',  'color'=>'#6366F1','desc'=>'Niveles de almacenaje'],
                    ['type'=>'rack',      'label'=>'Rack',        'color'=>'#F59E0B','desc'=>'Pallets industriales'],
                    ['type'=>'armario',   'label'=>'Armario',     'color'=>'#10B981','desc'=>'Cerrado / herramienta'],
                    ['type'=>'mesa',      'label'=>'Mesa trabajo','color'=>'#3B82F6','desc'=>'Área de trabajo'],
                    ['type'=>'area',      'label'=>'Área',        'color'=>'#94A3B8','desc'=>'Zona delimitada'],
                    ['type'=>'otro',      'label'=>'Otro',        'color'=>'#8B5CF6','desc'=>'Elemento genérico'],
                ];
            @endphp
            <div class="px-5 pb-7 space-y-3">
                @foreach($palette as $el)
                <button type="button" @click="setPlacingMode('{{ $el['type'] }}')"
                    class="w-full flex items-center gap-3.5 px-4 py-3.5 rounded-2xl border transition-all text-left"
                    :class="placingType === '{{ $el['type'] }}' ? 'border-indigo-300 bg-indigo-50 ring-2 ring-indigo-200' : 'border-slate-200 hover:bg-slate-50'">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 text-white text-[11px] font-black shadow-sm"
                         style="background-color: {{ $el['color'] }}">{{ strtoupper(substr($el['type'],0,2)) }}</div>
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-slate-700 leading-tight">{{ $el['label'] }}</p>
                        <p class="text-[10px] text-slate-400 leading-tight mt-0.5">{{ $el['desc'] }}</p>
                    </div>
                </button>
                @endforeach
            </div>
            {{-- Zones list --}}
            <div x-show="polygons.length > 0" class="px-5 pb-4 border-t border-slate-100 pt-4">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2.5">Zonas</p>
                <template x-for="zone in polygons" :key="'zl-'+zone.id">
                    <div class="flex items-center justify-between py-2 px-2.5 rounded-xl group cursor-pointer transition-colors"
                         :class="selectedZoneId === zone.id ? 'bg-blue-50 ring-1 ring-blue-200' : 'hover:bg-slate-50'"
                         @click.stop="selectZone(zone.id)">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-3.5 h-3.5 rounded-full shrink-0 border border-slate-200 shadow-sm"
                                 :style="`background-color: ${zone.floorColor || '#FFFFFF'}`"></div>
                            <span class="text-xs font-semibold text-slate-700 truncate" x-text="zone.label"></span>
                        </div>
                        <button @click.stop="deleteZone(zone.id); if(selectedZoneId === zone.id) deselectZone()"
                            class="w-6 h-6 flex items-center justify-center rounded-lg text-slate-300 hover:text-red-400 hover:bg-red-50 transition-colors opacity-0 group-hover:opacity-100 shrink-0">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>
            </div>

            <div class="mt-auto border-t border-slate-100 px-6 py-5">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Zoom</p>
                <div class="flex items-center gap-2">
                    <button @click="ppc = Math.max(20, ppc - 4)" class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold transition-colors">−</button>
                    <span class="flex-1 text-center text-xs font-bold text-slate-600" x-text="ppc + 'px'"></span>
                    <button @click="ppc = Math.min(64, ppc + 4)" class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold transition-colors">+</button>
                </div>
            </div>
        </div>

        {{-- ── CANVAS AREA ──────────────────────────────────────────────────── --}}
        <div class="flex-1 overflow-auto bg-slate-300/50 p-8">

            {{--
                Canvas container: fixed pixel dimensions so the scroll-parent scrolls.
                Two child layers stacked absolutely:
                  1. SVG  — background, grid, polygon, draw helpers (pointer-events:none)
                  2. DIVs — spots via Alpine x-for (works perfectly outside SVG)
            --}}
            {{-- wire:ignore: Livewire must not morph canvas children.       --}}
            {{-- Without it, Livewire removes Alpine's style="display:none"  --}}
            {{-- from x-show elements during re-renders (phantom ghost bug).  --}}
            <div wire:ignore
                 class="relative inline-block shadow-2xl rounded-lg border border-slate-300 select-none"
                 x-ref="canvas"
                 :style="`width:${gridCols*ppc}px; height:${gridRows*ppc}px; background:#FAFBFC;`"
                 :class="{
                     'cursor-crosshair': mode === 'draw',
                     'cursor-cell':      mode === 'place',
                     'cursor-default':   mode === 'select',
                 }"
                 @mousemove="onMouseMove($event)"
                 @mousedown.prevent="onCanvasMouseDown($event)"
                 @mouseup="onMouseUp($event)"
                 @mouseleave="isDragging && !isDraggingVertex && onMouseUp($event)"
            >

                {{-- ── LAYER 1: SVG (decorative + draw helpers) ────────────── --}}
                {{-- wire:ignore: Livewire must NEVER morph SVG children.        --}}
                {{-- <template x-for> inside SVG has no .content (not an HTML    --}}
                {{-- template node), so document.importNode() throws during morph.--}}
                {{-- Alpine still manages this SVG reactively; Livewire stays out.--}}
                <svg wire:ignore
                     class="absolute inset-0 pointer-events-none rounded-lg overflow-hidden"
                     :width="gridCols*ppc" :height="gridRows*ppc">
                    <defs>
                        <pattern id="wl-minor" x="0" y="0" :width="ppc" :height="ppc" patternUnits="userSpaceOnUse">
                            <path :d="`M ${ppc} 0 L 0 0 0 ${ppc}`" fill="none" stroke="#CBD5E1" stroke-width="0.5" opacity="0.7"/>
                        </pattern>
                        <pattern id="wl-major" x="0" y="0" :width="ppc*5" :height="ppc*5" patternUnits="userSpaceOnUse">
                            <rect :width="ppc*5" :height="ppc*5" fill="url(#wl-minor)"/>
                            <path :d="`M ${ppc*5} 0 L 0 0 0 ${ppc*5}`" fill="none" stroke="#94A3B8" stroke-width="1" opacity="0.5"/>
                        </pattern>
                        <pattern id="wl-dots" x="0" y="0" :width="ppc" :height="ppc" patternUnits="userSpaceOnUse">
                            <circle cx="0" cy="0" r="2.5" fill="#475569" opacity="0.35"/>
                        </pattern>
                    </defs>

                    {{-- Grid --}}
                    <rect :width="gridCols*ppc" :height="gridRows*ppc" fill="url(#wl-major)" :opacity="mode==='select'?0.35:0.8"/>
                    {{-- Dot overlay (draw mode) --}}
                    <rect x-show="mode==='draw'" :width="gridCols*ppc" :height="gridRows*ppc" fill="url(#wl-dots)"/>

                    {{-- Dark veil — dims outside all closed zones (HTML fills paint over it) --}}
                    <rect x-show="polygons.length > 0 && mode !== 'draw'"
                        :width="gridCols*ppc" :height="gridRows*ppc"
                        fill="rgba(15,23,42,0.28)" pointer-events="none"/>

                    {{-- Borders of all closed zones as one SVG path (no x-for in SVG) --}}
                    <path x-show="polygons.length > 0 && mode !== 'draw'"
                        :d="closedZonesBorderPath"
                        fill="none" :stroke="wallColor"
                        stroke-width="3" stroke-linejoin="round"/>

                    {{-- Selected zone highlight --}}
                    <path x-show="selectedZoneId && !editingZoneVertices"
                        :d="selectedZoneBorderPath"
                        fill="none" stroke="#3B82F6"
                        stroke-width="2.5" stroke-dasharray="9,5" opacity="0.9" pointer-events="none"/>
                    {{-- Selected zone highlight (vertex edit mode) --}}
                    <path x-show="editingZoneVertices"
                        :d="selectedZoneBorderPath"
                        fill="none" stroke="#F59E0B"
                        stroke-width="2.5" stroke-dasharray="6,4" opacity="0.85" pointer-events="none"/>

                    {{-- Open polyline for the zone currently being drawn --}}
                    <polyline x-show="mode==='draw' && drawingPoints.length >= 2"
                        :points="drawingPolylinePoints"
                        fill="none" :stroke="wallColor" stroke-width="3" stroke-linejoin="round"/>

                    {{-- Preview line: last drawing point → cursor --}}
                    <line x-show="mode==='draw' && drawingPoints.length >= 1"
                        :x1="drawingPoints.length ? drawingPoints[drawingPoints.length-1].col*ppc : 0"
                        :y1="drawingPoints.length ? drawingPoints[drawingPoints.length-1].row*ppc : 0"
                        :x2="mouseGridCol*ppc" :y2="mouseGridRow*ppc"
                        :stroke="wallColor" stroke-width="2" stroke-dasharray="6,4" opacity="0.45"/>

                    {{-- First point indicator (close-target) --}}
                    <circle x-show="mode==='draw' && drawingPoints.length >= 3"
                        :cx="drawingPoints.length ? drawingPoints[0].col*ppc : 0"
                        :cy="drawingPoints.length ? drawingPoints[0].row*ppc : 0"
                        r="8" fill="#EF4444" opacity="0.25"/>
                    <circle x-show="mode==='draw' && drawingPoints.length >= 1"
                        :cx="drawingPoints.length ? drawingPoints[0].col*ppc : 0"
                        :cy="drawingPoints.length ? drawingPoints[0].row*ppc : 0"
                        :r="drawingPoints.length >= 3 ? 6 : 4"
                        :fill="drawingPoints.length >= 3 ? '#EF4444' : wallColor"
                        stroke="white" stroke-width="2"/>

                    {{-- Vertex dots for current drawing (no x-for in SVG) --}}
                    <path x-show="mode==='draw' && drawingPoints.length > 1"
                          :d="drawingVertexCirclesPath"
                          :fill="wallColor"
                          stroke="white" stroke-width="2"/>
                </svg>

                {{-- ── LAYER 2: Zone fills via clip-path (x-for works in HTML) ── --}}
                {{-- clip-path: polygon(...) renders each zone's floor color.      --}}
                {{-- Combined with the SVG dark veil, outside zones looks darker.  --}}
                <template x-for="zone in polygons" :key="'zone-'+zone.id">
                    <div class="absolute inset-0 pointer-events-none"
                         :style="`clip-path: polygon(${zone.points.map(p => p.col*ppc+'px '+p.row*ppc+'px').join(', ')}); background-color: ${zone.floorColor || floorColor};`">
                    </div>
                </template>

                {{-- ── LAYER 2b: Zone vertex handles (visible in vertex-edit mode) ── --}}
                <template x-if="editingZoneVertices && selectedZone">
                    <div class="absolute inset-0 pointer-events-none">
                        <template x-for="(pt, idx) in (selectedZone ? selectedZone.points : [])" :key="'vh-'+idx">
                            <div class="absolute rounded-full border-2 border-white cursor-move pointer-events-auto z-20 transition-transform hover:scale-125"
                                 @mousedown.stop.prevent="startDragVertex($event, selectedZoneId, idx)"
                                 :style="`left:${pt.col*ppc-7}px;top:${pt.row*ppc-7}px;width:14px;height:14px;background-color:${selectedZone ? (selectedZone.wallColor || wallColor) : wallColor};box-shadow:0 2px 6px rgba(0,0,0,0.35);`">
                            </div>
                        </template>
                    </div>
                </template>

                {{-- ── LAYER 3: Spots as HTML divs (Alpine x-for works here) ── --}}
                <template x-for="spot in spots" :key="'s-'+spot.id">
                    <div class="absolute rounded flex items-center justify-center overflow-hidden"
                         @mousedown.stop.prevent="startDragSpot($event, spot)"
                         @mouseup.stop="onSpotMouseUp($event, spot)"
                         :style="spotStyle(spot)">

                        {{-- Section dividers (vertical lines along width) --}}
                        <template x-for="si in Array.from({length: Math.max(0,(spot.sections_count||1)-1)},(_,i)=>i+1)" :key="'sec-'+spot.id+'-'+si">
                            <div class="absolute top-0 bottom-0 pointer-events-none"
                                 :style="`left:${(si/(spot.sections_count||1))*100}%;width:1px;background:${spot.color};opacity:0.35;`"></div>
                        </template>

                        {{-- Level dividers (horizontal lines along depth) --}}
                        <template x-for="li in Array.from({length: Math.max(0,(spot.levels_count||1)-1)},(_,i)=>i+1)" :key="'lv-'+spot.id+'-'+li">
                            <div class="absolute left-0 right-0 pointer-events-none"
                                 :style="`top:${(li/(spot.levels_count||1))*100}%;height:1px;background:${spot.color};opacity:0.22;`"></div>
                        </template>

                        <span x-text="spot.label" class="truncate px-1 w-full text-center pointer-events-none relative z-10"
                              :style="`font-size:${Math.min(11,ppc*0.28)}px;font-weight:700;color:${spot.color}`"></span>

                        {{-- Resize handles — visible only when selected, in select mode --}}
                        <template x-if="selectedSpotId === spot.id && mode === 'select' && !spot.is_locked">
                            <div class="absolute inset-0 pointer-events-none" style="overflow:visible;">
                                {{-- Right edge (width) --}}
                                <div class="absolute pointer-events-auto cursor-e-resize rounded-sm bg-white shadow border"
                                     :style="`right:-5px;top:50%;transform:translateY(-50%);width:10px;height:20px;border-color:${spot.color};z-index:30;`"
                                     @mousedown.stop.prevent="startResizeSpot($event,spot,'e')">
                                </div>
                                {{-- Bottom edge (depth) --}}
                                <div class="absolute pointer-events-auto cursor-s-resize rounded-sm bg-white shadow border"
                                     :style="`bottom:-5px;left:50%;transform:translateX(-50%);width:20px;height:10px;border-color:${spot.color};z-index:30;`"
                                     @mousedown.stop.prevent="startResizeSpot($event,spot,'s')">
                                </div>
                                {{-- SE corner (width + depth) --}}
                                <div class="absolute pointer-events-auto cursor-se-resize rounded-sm bg-white shadow border"
                                     :style="`right:-5px;bottom:-5px;width:12px;height:12px;border-color:${spot.color};z-index:30;`"
                                     @mousedown.stop.prevent="startResizeSpot($event,spot,'se')">
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                {{-- Ghost: dragging existing spot --}}
                <template x-if="isDragging && dragSpotId !== null">
                    <div class="absolute rounded pointer-events-none"
                         :style="`left:${dragGhostCol*ppc+1}px;top:${dragGhostRow*ppc+1}px;width:${dragSpotW*ppc-2}px;height:${dragSpotH*ppc-2}px;background:rgba(99,102,241,0.15);border:2px dashed #6366F1`">
                    </div>
                </template>

                {{-- Ghost: placing new element (colored like the element type) --}}
                <template x-if="mode==='place' && placingType">
                    <div class="absolute rounded pointer-events-none"
                         :style="`left:${mouseGridCol*ppc+1}px;top:${mouseGridRow*ppc+1}px;width:${placingWidth*ppc-2}px;height:${placingDepth*ppc-2}px;background:${placingColor}28;border:2px dashed ${placingColor}`">
                    </div>
                </template>

                {{-- Cursor dot: wall color in draw mode, element color in place mode --}}
                <template x-if="mode !== 'select'">
                    <div class="absolute rounded-full pointer-events-none opacity-80"
                         :style="`left:${mouseGridCol*ppc-5}px;top:${mouseGridRow*ppc-5}px;width:10px;height:10px;background-color:${mode==='place' ? placingColor : wallColor}`">
                    </div>
                </template>

            </div>{{-- /canvas --}}
        </div>

        {{-- ── RIGHT PANEL ──────────────────────────────────────────────────── --}}
        @if($showSpotPanel && $selectedSpotId)
        <div x-show="!selectedZoneId" class="w-72 shrink-0 bg-white border-l border-slate-200 flex flex-col overflow-hidden">

            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between shrink-0">
                <div>
                    <p class="text-sm font-bold text-slate-800">{{ $spotLabel ?: 'Elemento' }}</p>
                    <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wider">
                        {{ \App\Models\WarehouseSpot::TYPES[$spotType] ?? '' }}
                        @if($spotCode) · <span class="font-mono">{{ $spotCode }}</span> @endif
                    </p>
                </div>
                <button wire:click="closeSpotPanel"
                    class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="overflow-y-auto flex-1">
                <div class="p-4 space-y-3 border-b border-slate-100">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Configuración</p>

                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Nombre</label>
                        <input wire:model="spotLabel" type="text"
                            class="w-full px-3 py-2 rounded-xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 text-sm font-medium">
                        @error('spotLabel') <p class="text-xs text-red-500 mt-0.5">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Código</label>
                            <input wire:model="spotCode" type="text" placeholder="EST-01"
                                class="w-full px-3 py-2 rounded-xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 text-sm font-mono">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tipo</label>
                            <select wire:model="spotType"
                                class="w-full px-3 py-2 rounded-xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 text-sm">
                                @foreach(\App\Models\WarehouseSpot::TYPES as $k => $v)
                                    <option value="{{ $k }}">{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Niveles</label>
                            <input wire:model="spotLevels" type="number" min="1" max="20"
                                class="w-full px-3 py-2 rounded-xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 text-sm font-medium text-center">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Secciones</label>
                            <input wire:model="spotSections" type="number" min="1" max="20"
                                class="w-full px-3 py-2 rounded-xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 text-sm font-medium text-center">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Alto/nv cm</label>
                            <input wire:model="spotLevelHeight" type="number" min="10" max="500"
                                class="w-full px-3 py-2 rounded-xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 text-sm font-medium text-center">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12M8 12h12M8 17h12"/></svg>
                                Ancho (celdas)
                            </label>
                            <input wire:model="spotWidth" type="number" min="1" max="30"
                                class="w-full px-3 py-2 rounded-xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 text-sm font-medium text-center">
                            @error('spotWidth') <p class="text-xs text-red-500 mt-0.5">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h12M7 12h12M7 16h12"/></svg>
                                Fondo (celdas)
                            </label>
                            <input wire:model="spotDepth" type="number" min="1" max="30"
                                class="w-full px-3 py-2 rounded-xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 text-sm font-medium text-center">
                            @error('spotDepth') <p class="text-xs text-red-500 mt-0.5">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <p class="text-[9px] text-slate-400">También puedes arrastrar los manejadores ↔ ↕ en el plano</p>

                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Color</label>
                        <div class="flex items-center gap-2">
                            <input wire:model="spotColor" type="color"
                                class="w-9 h-9 rounded-xl border-slate-200 cursor-pointer p-0.5">
                            <div class="flex gap-1.5 flex-wrap">
                                @foreach(['#6366F1','#F59E0B','#10B981','#3B82F6','#EF4444','#8B5CF6','#94A3B8','#F97316'] as $c)
                                    <button type="button" wire:click="$set('spotColor','{{ $c }}')"
                                        class="w-5 h-5 rounded-full border-2 transition-all hover:scale-110"
                                        style="background-color:{{ $c }};border-color:{{ $spotColor===$c?'#1E293B':'transparent' }}">
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <textarea wire:model="spotNotes" rows="2" placeholder="Notas..."
                        class="w-full px-3 py-2 rounded-xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 text-sm resize-none"></textarea>

                    <div class="flex gap-1.5">
                        <button wire:click="updateSpot"
                            class="flex-1 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-colors">Guardar</button>
                        <button wire:click="rotateSpot({{ $selectedSpotId }})" title="Rotar 90°"
                            class="w-9 h-9 flex items-center justify-center rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </button>
                        <button wire:click="toggleLockSpot({{ $selectedSpotId }})" title="Bloquear"
                            class="w-9 h-9 flex items-center justify-center rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </button>
                        <button wire:click="deleteSpot({{ $selectedSpotId }})" wire:confirm="¿Eliminar este elemento?" title="Eliminar"
                            class="w-9 h-9 flex items-center justify-center rounded-xl bg-red-50 hover:bg-red-100 text-red-500 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>

                @if($selectedSpotLevels?->count())
                <div class="p-4 space-y-2">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Inventario por nivel</p>
                    @foreach($selectedSpotLevels->sortByDesc('level_number') as $level)
                    <div class="rounded-xl border border-slate-200 overflow-hidden">
                        <div class="px-3 py-2 bg-slate-50 flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-700">{{ $level->display_label }}</span>
                            <span class="text-[10px] text-slate-400">{{ $level->levelProducts->count() }} prod.</span>
                        </div>
                        @if($level->levelProducts->isEmpty())
                            <p class="px-3 py-2 text-[11px] text-slate-400 italic">Sin productos asignados</p>
                        @else
                            @foreach($level->levelProducts as $lp)
                                @php $stock = $lp->product->stocks->first(); @endphp
                                <div class="px-3 py-2 flex items-center justify-between gap-2 border-t border-slate-100">
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold text-slate-700 truncate">{{ $lp->product->name }}</p>
                                        <p class="text-[10px] font-mono text-slate-400">{{ $lp->product->sku }}</p>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <p class="text-sm font-black {{ ($stock?->quantity??0)>0?'text-emerald-600':'text-slate-300' }}">
                                            {{ number_format($stock?->quantity??0,0) }}
                                        </p>
                                        <p class="text-[9px] text-slate-400">{{ $lp->product->unit?->abbreviation??'u' }}</p>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- ── ZONE EDIT PANEL (Alpine-driven, no Livewire needed) ─────── --}}
        <div x-show="selectedZoneId" x-cloak
             class="w-72 shrink-0 bg-white border-l border-slate-200 flex flex-col overflow-hidden">

            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between shrink-0">
                <div>
                    <p class="text-sm font-bold text-slate-800" x-text="zoneEditLabel || 'Zona'"></p>
                    <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wider">Zona del plano</p>
                </div>
                <button @click="deselectZone()"
                    class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="overflow-y-auto flex-1 p-4 space-y-4">

                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Propiedades</p>
                    <div class="space-y-3">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Nombre</label>
                            <input x-model="zoneEditLabel" type="text" @change="saveZoneEdits()"
                                class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 text-sm font-medium outline-none transition-all">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Color piso</label>
                                <div class="flex items-center gap-2">
                                    <input x-model="zoneEditFloorColor" type="color" @change="saveZoneEdits()"
                                        class="w-9 h-9 rounded-xl border border-slate-200 cursor-pointer p-0.5 shrink-0">
                                    <span class="text-xs font-mono text-slate-500" x-text="zoneEditFloorColor"></span>
                                </div>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Color pared</label>
                                <div class="flex items-center gap-2">
                                    <input x-model="zoneEditWallColor" type="color" @change="saveZoneEdits()"
                                        class="w-9 h-9 rounded-xl border border-slate-200 cursor-pointer p-0.5 shrink-0">
                                    <span class="text-xs font-mono text-slate-500" x-text="zoneEditWallColor"></span>
                                </div>
                            </div>
                        </div>
                        {{-- Quick floor color presets --}}
                        <div>
                            <p class="text-[10px] text-slate-400 mb-1.5">Presets piso</p>
                            <div class="flex gap-1.5 flex-wrap">
                                <template x-for="preset in ['#FFFFFF','#F0FDF4','#EFF6FF','#FFFBEB','#FFF1F2','#F5F3FF','#F0F9FF','#FDF4FF']">
                                    <button type="button" @click="zoneEditFloorColor = preset; saveZoneEdits()"
                                        class="w-6 h-6 rounded-lg border-2 transition-all hover:scale-110"
                                        :style="`background-color:${preset};border-color:${zoneEditFloorColor===preset?'#6366F1':'#E2E8F0'}`">
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-4">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Forma</p>
                    <div x-show="!editingZoneVertices">
                        <button @click="startEditVertices()"
                            class="w-full flex items-center gap-2.5 px-3 py-2.5 bg-amber-50 hover:bg-amber-100 border border-amber-200 text-amber-700 text-xs font-bold rounded-xl transition-colors">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5"/>
                            </svg>
                            Editar vértices
                        </button>
                    </div>
                    <div x-show="editingZoneVertices" class="space-y-2">
                        <div class="flex items-start gap-2 px-3 py-2.5 bg-amber-50 border border-amber-200 rounded-xl">
                            <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-[11px] text-amber-700 font-medium leading-snug">Arrastra los puntos naranjas en el plano para mover vértices</p>
                        </div>
                        <button @click="stopEditVertices()"
                            class="w-full flex items-center justify-center gap-2 px-3 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-colors shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            Finalizar edición
                        </button>
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-4">
                    <button @click="deleteZone(selectedZoneId); deselectZone()"
                        class="w-full flex items-center justify-center gap-2 px-3 py-2.5 bg-red-50 hover:bg-red-100 border border-red-200 text-red-500 text-xs font-bold rounded-xl transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Eliminar zona
                    </button>
                </div>

            </div>
        </div>

    </div>

    {{-- ── Grid config modal ─────────────────────────────────────────────── --}}
    @if($showGridPanel)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-800">Configuración de cuadrícula</h3>
                <button wire:click="$set('showGridPanel',false)"
                    class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Columnas</label>
                        <input wire:model="gridCols" type="number" min="5" max="60"
                            class="w-full px-3 py-2.5 rounded-xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 text-sm text-center">
                        @error('gridCols') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Filas</label>
                        <input wire:model="gridRows" type="number" min="5" max="50"
                            class="w-full px-3 py-2.5 rounded-xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 text-sm text-center">
                        @error('gridRows') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Tamaño de celda (cm)</label>
                    <input wire:model="cellSizeCm" type="number" min="25" max="200" step="25"
                        class="w-full px-3 py-2.5 rounded-xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 text-sm text-center">
                    <p class="text-[10px] text-slate-400 ml-1">1 celda = {{ $cellSizeCm }} cm real</p>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    @foreach(['wallColor'=>'Paredes','floorColor'=>'Piso','bgColor'=>'Fondo'] as $prop=>$lbl)
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $lbl }}</label>
                        <input wire:model="{{ $prop }}" type="color" class="w-full h-9 rounded-xl border-slate-200 cursor-pointer p-0.5">
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="px-6 pb-6 flex gap-3 justify-end">
                <button wire:click="$set('showGridPanel',false)"
                    class="px-4 py-2.5 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">Cancelar</button>
                <button wire:click="saveGridConfig"
                    class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition-colors shadow-lg shadow-indigo-500/25">Aplicar</button>
            </div>
        </div>
    </div>
    @endif

</div>

@script
<script>
Alpine.data('warehouseEditor', (config) => ({

    // ── State ─────────────────────────────────────────────────────────────
    ppc:           config.ppc      || 36,
    gridCols:      config.gridCols || 24,
    gridRows:      config.gridRows || 20,
    polygons:      config.polygons   || [],   // closed zones [{id,label,points[],floorColor,wallColor}]
    drawingPoints: [],                        // points for the zone currently being drawn
    drawingLabel:  '',
    spots:         config.spots     || [],
    wallColor:     config.wallColor  || '#1E293B',
    floorColor:    config.floorColor || '#FFFFFF',
    bgColor:       config.bgColor    || '#F1F5F9',

    mode:        'select',
    placingType: null,

    mouseGridCol: 0,
    mouseGridRow: 0,

    isDragging:       false,
    pendingDragSpot:  null,   // { spotId, startCol, startRow }
    dragSpotId:       null,
    dragSpotW:        3,
    dragSpotH:        1,
    dragOffsetCol:    0,
    dragOffsetRow:    0,
    dragGhostCol:     0,
    dragGhostRow:     0,

    overriddenPositions: {},  // { [id]: {col,row} } — prevents snap-back flicker after drag

    selectedSpotId: null,

    // Zone editing
    selectedZoneId:       null,
    editingZoneVertices:  false,
    isDraggingVertex:     false,
    draggingVertexZoneId: null,
    draggingVertexIdx:    null,
    zoneEditLabel:        '',
    zoneEditFloorColor:   '#FFFFFF',
    zoneEditWallColor:    '#1E293B',

    // ── Computed ──────────────────────────────────────────────────────────

    // Polyline points for the zone currently being drawn
    get drawingPolylinePoints() {
        return this.drawingPoints.map(p => `${p.col*this.ppc},${p.row*this.ppc}`).join(' ');
    },
    // SVG path of all closed zone borders combined (one <path> = no x-for in SVG)
    get closedZonesBorderPath() {
        return this.polygons
            .filter(z => z.points && z.points.length >= 3)
            .map(z => z.points.map((p, i) => `${i===0?'M':'L'}${p.col*this.ppc},${p.row*this.ppc}`).join(' ') + 'Z')
            .join(' ');
    },
    // Vertex dots for current drawing (no x-for in SVG)
    get drawingVertexCirclesPath() {
        const r = 4;
        return this.drawingPoints.slice(1).map(p => {
            const cx = p.col * this.ppc, cy = p.row * this.ppc;
            return `M${cx},${cy} m-${r},0 a${r},${r} 0 1,0 ${r*2},0 a${r},${r} 0 1,0 -${r*2},0`;
        }).join(' ');
    },
    get placingWidth()  { return this.placingType === 'area' ? 4 : 3; },
    get placingDepth()  { return this.placingType === 'area' ? 3 : 1; },
    get placingColor() {
        const map = { estanteria:'#6366F1', rack:'#F59E0B', armario:'#10B981', mesa:'#3B82F6', area:'#94A3B8', otro:'#8B5CF6' };
        return map[this.placingType] || '#6366F1';
    },
    get nextZoneLabel() {
        return `Zona ${this.polygons.length + 1}`;
    },
    get selectedZone() {
        if (!this.selectedZoneId) return null;
        return this.polygons.find(z => z.id === this.selectedZoneId) || null;
    },
    get selectedZoneBorderPath() {
        const z = this.selectedZone;
        if (!z || !z.points || z.points.length < 3) return '';
        return z.points.map((p, i) => `${i===0?'M':'L'}${p.col*this.ppc},${p.row*this.ppc}`).join(' ') + 'Z';
    },
    get canClosePolygon() {
        if (this.drawingPoints.length < 3) return false;
        const first = this.drawingPoints[0];
        const last  = this.drawingPoints[this.drawingPoints.length - 1];
        return Math.abs(last.col - first.col) <= 1 && Math.abs(last.row - first.row) <= 1;
    },

    // ── Init ──────────────────────────────────────────────────────────────

    init() {
        this.$watch('mode', v => { if (v !== 'place') this.placingType = null; });

        // Single source of truth: whenever Livewire closes the panel, clear selection.
        // This catches ALL paths: X button, deleteSpot, clickingEmpty, Escape, re-renders.
        this.$wire.watch('showSpotPanel', (open) => {
            if (!open) this.selectedSpotId = null;
        });

        this.$wire.on('spot-created', ({ spot }) => {
            if (!this.spots.find(s => s.id === spot.id)) {
                this.spots = [...this.spots, spot];
            }
            this.selectedSpotId = spot.id;
            this.mode = 'select';
            this.placingType = null;
        });

        this.$wire.on('spot-updated', ({ spot }) => {
            const idx = this.spots.findIndex(s => s.id === spot.id);
            if (idx !== -1) {
                this.spots[idx] = { ...this.spots[idx], ...spot };
                this.spots = [...this.spots];
            }
            delete this.overriddenPositions[spot.id];
        });

        this.$wire.on('spot-deleted', ({ spotId }) => {
            this.spots = this.spots.filter(s => s.id !== spotId);
            this.selectedSpotId = null;
            delete this.overriddenPositions[spotId];
        });

        this.$wire.on('spot-deselected', () => {
            this.selectedSpotId = null;
        });

        // SVG has wire:ignore so Livewire won't update it; sync grid dims via event instead
        this.$wire.on('grid-config-updated', ({ cols, rows, wallColor, floorColor, bgColor }) => {
            this.gridCols   = cols;
            this.gridRows   = rows;
            this.wallColor  = wallColor;
            this.floorColor = floorColor;
            this.bgColor    = bgColor;
        });
    },

    // ── Coordinate helpers ────────────────────────────────────────────────

    // Canvas div has exact CSS pixel dimensions = gridCols*ppc × gridRows*ppc,
    // so no scale correction is needed.
    snapToIntersection(e) {
        const r = this.$refs.canvas.getBoundingClientRect();
        const x = e.clientX - r.left, y = e.clientY - r.top;
        return {
            col: Math.max(0, Math.min(Math.round(x / this.ppc), this.gridCols)),
            row: Math.max(0, Math.min(Math.round(y / this.ppc), this.gridRows)),
        };
    },
    snapToCell(e) {
        const r = this.$refs.canvas.getBoundingClientRect();
        const x = e.clientX - r.left, y = e.clientY - r.top;
        return {
            col: Math.max(0, Math.min(Math.floor(x / this.ppc), this.gridCols - 1)),
            row: Math.max(0, Math.min(Math.floor(y / this.ppc), this.gridRows - 1)),
        };
    },

    getSpotX(spot) {
        if (this.isDragging && this.dragSpotId === spot.id) return this.dragGhostCol;
        const ov = this.overriddenPositions[spot.id];
        return ov !== undefined ? ov.col : spot.col;
    },
    getSpotY(spot) {
        if (this.isDragging && this.dragSpotId === spot.id) return this.dragGhostRow;
        const ov = this.overriddenPositions[spot.id];
        return ov !== undefined ? ov.row : spot.row;
    },

    hexToRgba(hex, alpha) {
        const r = parseInt(hex.slice(1, 3), 16);
        const g = parseInt(hex.slice(3, 5), 16);
        const b = parseInt(hex.slice(5, 7), 16);
        return `rgba(${r},${g},${b},${alpha})`;
    },

    spotStyle(spot) {
        const selected  = this.selectedSpotId === spot.id;
        const x         = this.getSpotX(spot) * this.ppc + 1;
        const y         = this.getSpotY(spot) * this.ppc + 1;
        const w         = spot.width_cells  * this.ppc - 2;
        const h         = spot.depth_cells  * this.ppc - 2;
        const border    = selected
            ? `2.5px solid ${spot.color}`
            : `1.5px solid ${spot.color}AA`;
        const shadow    = selected
            ? `0 0 0 3px ${this.hexToRgba(spot.color, 0.85)}, 0 6px 20px ${this.hexToRgba(spot.color, 0.5)}`
            : '2px 2px 0 rgba(0,0,0,0.10)';
        const outline   = selected ? `outline:2.5px dashed ${spot.color};outline-offset:-3px;` : '';
        const cursor    = (spot.is_locked || this.mode !== 'select') ? 'default' : 'grab';
        const pEvents   = this.mode === 'select' ? 'auto' : 'none';
        const zIndex    = selected ? 10 : 1;
        const sepBg     = this.levelSeparatorBg(spot);
        return `left:${x}px;top:${y}px;width:${w}px;height:${h}px;`
             + `background-color:${spot.color}28;border:${border};`
             + `box-shadow:${shadow};cursor:${cursor};`
             + `pointer-events:${pEvents};z-index:${zIndex};`
             + `${outline}background-image:${sepBg};`;
    },

    // CSS background-image with thin vertical lines for each level division
    levelSeparatorBg(spot) {
        if (spot.levels_count <= 1) return 'none';
        const color = spot.color + '66';
        const pct   = 100 / spot.levels_count;
        const stops = [];
        for (let i = 1; i < spot.levels_count; i++) {
            const p = (i * pct).toFixed(2);
            stops.push(
                `transparent calc(${p}% - 0.5px)`,
                `${color} calc(${p}% - 0.5px)`,
                `${color} calc(${p}% + 0.5px)`,
                `transparent calc(${p}% + 0.5px)`
            );
        }
        return `linear-gradient(to right, ${stops.join(',')})`;
    },

    // ── Mouse events ──────────────────────────────────────────────────────

    onMouseMove(e) {
        const snap = this.snapToIntersection(e);
        this.mouseGridCol = snap.col;
        this.mouseGridRow = snap.row;

        if (this.pendingDragSpot && !this.isDragging) {
            const cell = this.snapToCell(e);
            if (cell.col !== this.pendingDragSpot.startCol || cell.row !== this.pendingDragSpot.startRow) {
                this.isDragging = true;
                this.dragSpotId = this.pendingDragSpot.spotId;
            }
        }
        if (this.isDragging && this.dragSpotId !== null) {
            const cell = this.snapToCell(e);
            this.dragGhostCol = Math.max(0, cell.col - this.dragOffsetCol);
            this.dragGhostRow = Math.max(0, cell.row - this.dragOffsetRow);
        }
    },

    onCanvasMouseDown(e) {
        if (this.mode === 'draw') {
            const snap = this.snapToIntersection(e);
            this.addPolygonPoint(snap.col, snap.row);
            return;
        }
        if (this.mode === 'place' && this.placingType) {
            const cell = this.snapToCell(e);
            this.$wire.addSpot(this.placingType, cell.col, cell.row);
            return;
        }
        if (this.mode === 'select') {
            if (this.editingZoneVertices) return;
            const r  = this.$refs.canvas.getBoundingClientRect();
            const px = e.clientX - r.left, py = e.clientY - r.top;
            const hit = this.polygons.slice().reverse().find(z => this.pointInPolygon(px, py, z.points));
            if (hit) { this.selectZone(hit.id); return; }
            this.deselectZone();
            this.selectedSpotId = null;
            this.$wire.closeSpotPanel();
        }
    },

    onMouseUp(e) {
        if (this.isDragging && this.dragSpotId !== null) {
            this.overriddenPositions[this.dragSpotId] = { col: this.dragGhostCol, row: this.dragGhostRow };
            this.$wire.moveSpot(this.dragSpotId, this.dragGhostCol, this.dragGhostRow);
        }
        this.isDragging      = false;
        this.dragSpotId      = null;
        this.pendingDragSpot = null;
    },

    onSpotMouseUp(e, spot) {
        if (!this.isDragging) {
            this.deselectZone();
            this.selectedSpotId = spot.id;
            this.$wire.selectSpot(spot.id);
        }
        this.onMouseUp(e);
    },

    // ── Polygon / Zones ───────────────────────────────────────────────────

    addPolygonPoint(col, row) {
        this.drawingPoints.push({ col, row });
    },
    closePolygon() {
        if (!this.canClosePolygon) return;
        const label = this.drawingLabel.trim() || this.nextZoneLabel;
        const zone  = {
            id:         Date.now(),
            label,
            points:     [...this.drawingPoints],
            floorColor: this.floorColor,
            wallColor:  this.wallColor,
        };
        this.polygons = [...this.polygons, zone];
        this.drawingPoints = [];
        this.drawingLabel  = '';
        this.mode = 'select';
        this.$wire.savePolygons(this.polygons);
    },
    deleteZone(id) {
        this.polygons = this.polygons.filter(z => z.id !== id);
        this.$wire.savePolygons(this.polygons);
    },
    undoLastPoint() { this.drawingPoints.pop(); },
    clearPolygon()  { this.drawingPoints = []; },

    // ── Zone selection & editing ──────────────────────────────────────────

    pointInPolygon(px, py, points) {
        let inside = false;
        for (let i = 0, j = points.length - 1; i < points.length; j = i++) {
            const xi = points[i].col * this.ppc, yi = points[i].row * this.ppc;
            const xj = points[j].col * this.ppc, yj = points[j].row * this.ppc;
            if (((yi > py) !== (yj > py)) && (px < (xj - xi) * (py - yi) / (yj - yi) + xi)) {
                inside = !inside;
            }
        }
        return inside;
    },
    selectZone(id) {
        const zone = this.polygons.find(z => z.id === id);
        if (!zone) return;
        this.selectedZoneId     = id;
        this.zoneEditLabel      = zone.label;
        this.zoneEditFloorColor = zone.floorColor || this.floorColor;
        this.zoneEditWallColor  = zone.wallColor  || this.wallColor;
        this.selectedSpotId = null;
        this.$wire.closeSpotPanel();
    },
    deselectZone() {
        this.selectedZoneId      = null;
        this.editingZoneVertices = false;
    },
    saveZoneEdits() {
        this.polygons = this.polygons.map(z =>
            z.id === this.selectedZoneId
                ? { ...z, label: this.zoneEditLabel, floorColor: this.zoneEditFloorColor, wallColor: this.zoneEditWallColor }
                : z
        );
        this.$wire.savePolygons(this.polygons);
    },
    startEditVertices() {
        this.editingZoneVertices = true;
    },
    stopEditVertices() {
        this.editingZoneVertices = false;
        this.$wire.savePolygons(this.polygons);
    },
    startDragVertex(e, zoneId, idx) {
        this.isDraggingVertex = true;   // flag: prevents canvas @mouseleave from ending spot drag
        const canvas = this.$refs.canvas;
        const onMove = (ev) => {
            const r   = canvas.getBoundingClientRect();
            const col = Math.max(0, Math.min(Math.round((ev.clientX - r.left) / this.ppc), this.gridCols));
            const row = Math.max(0, Math.min(Math.round((ev.clientY - r.top)  / this.ppc), this.gridRows));
            this.mouseGridCol = col;
            this.mouseGridRow = row;
            this.polygons = this.polygons.map(z => {
                if (z.id !== zoneId) return z;
                const pts = [...z.points];
                pts[idx] = { col, row };
                return { ...z, points: pts };
            });
        };
        const onUp = () => {
            this.isDraggingVertex     = false;
            this.draggingVertexZoneId = null;
            this.draggingVertexIdx    = null;
            document.removeEventListener('mousemove', onMove);
            document.removeEventListener('mouseup',   onUp);
            this.$wire.savePolygons(this.polygons);
        };
        document.addEventListener('mousemove', onMove);
        document.addEventListener('mouseup',   onUp);
    },

    // ── Spots ─────────────────────────────────────────────────────────────

    startDragSpot(e, spot) {
        if (this.mode !== 'select' || spot.is_locked) return;
        const cell = this.snapToCell(e);
        this.dragOffsetCol   = cell.col - spot.col;
        this.dragOffsetRow   = cell.row - spot.row;
        this.dragGhostCol    = spot.col;
        this.dragGhostRow    = spot.row;
        this.dragSpotW       = spot.width_cells;
        this.dragSpotH       = spot.depth_cells;
        this.pendingDragSpot = { spotId: spot.id, startCol: cell.col, startRow: cell.row };
    },

    startResizeSpot(e, spot, direction) {
        const startX  = e.clientX, startY = e.clientY;
        const origW   = spot.width_cells, origH = spot.depth_cells;
        const doW     = direction === 'e' || direction === 'se';
        const doH     = direction === 's' || direction === 'se';

        const onMove = (ev) => {
            const dx  = Math.round((ev.clientX - startX) / this.ppc);
            const dy  = Math.round((ev.clientY - startY) / this.ppc);
            const newW = Math.max(1, origW + (doW ? dx : 0));
            const newH = Math.max(1, origH + (doH ? dy : 0));
            const idx  = this.spots.findIndex(s => s.id === spot.id);
            if (idx !== -1) {
                this.spots[idx] = { ...this.spots[idx], width_cells: newW, depth_cells: newH };
                this.spots = [...this.spots];
            }
        };
        const onUp = () => {
            document.removeEventListener('mousemove', onMove);
            document.removeEventListener('mouseup',   onUp);
            const s = this.spots.find(s => s.id === spot.id);
            if (s) this.$wire.resizeSpot(spot.id, s.width_cells, s.depth_cells);
        };
        document.addEventListener('mousemove', onMove);
        document.addEventListener('mouseup',   onUp);
    },

    setPlacingMode(type) {
        this.mode = 'place';
        this.placingType = type;
        this.deselectZone();
        this.selectedSpotId = null;
        this.$wire.closeSpotPanel();
    },

    // ── Save ──────────────────────────────────────────────────────────────

    saveAll() {
        const spotData = this.spots.map(s => ({
            id:          s.id,
            col:         this.getSpotX(s),
            row:         this.getSpotY(s),
            width_cells: s.width_cells,
            depth_cells: s.depth_cells,
            rotation:    s.rotation ?? 0,
        }));
        this.$wire.saveLayout(this.polygons, spotData);
    },

}));
</script>
@endscript
