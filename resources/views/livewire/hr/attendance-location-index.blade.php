<div>{{-- Livewire root --}}

    {{-- Leaflet CSS/JS dentro del root para no romper el árbol de componentes --}}
    @once
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" defer></script>
    <style>
        [x-cloak]  { display: none !important; }
        #zone-map  { height: 280px; width: 100%; background: #e2e8f0; }
        @media (max-width: 640px) { #zone-map { height: 210px; } }
    </style>
    @endonce

    <x-page-header title="Zonas de Asistencia" description="Define las ubicaciones permitidas para registrar entradas y salidas">
        <x-slot:actions>
            <button wire:click="openCreate"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                + Nueva zona
            </button>
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    <div class="mb-5 p-4 bg-blue-50 border border-blue-200 rounded-xl text-sm text-blue-700">
        <strong>¿Cómo funciona?</strong> Cuando un empleado registra su asistencia, el sistema valida su GPS.
        Si no se encuentra dentro del radio de alguna zona activa, el registro es rechazado.
    </div>

    {{-- ── Lista de zonas ──────────────────────────────────────────────── --}}
    @if($locations->isEmpty())
    <div class="bg-white rounded-xl border border-slate-200 p-12 text-center">
        <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
        <p class="text-slate-500 mb-3">No hay zonas de asistencia definidas.</p>
        <button wire:click="openCreate"
                class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
            Crear primera zona
        </button>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
        @foreach($locations as $loc)
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden {{ !$loc->is_active ? 'opacity-60' : '' }}">
            <div class="relative h-28 bg-slate-100 overflow-hidden">
                <iframe
                    src="https://www.openstreetmap.org/export/embed.html?bbox={{ $loc->longitude - 0.003 }},{{ $loc->latitude - 0.002 }},{{ $loc->longitude + 0.003 }},{{ $loc->latitude + 0.002 }}&layer=mapnik&marker={{ $loc->latitude }},{{ $loc->longitude }}"
                    class="w-full h-full border-0 pointer-events-none" loading="lazy">
                </iframe>
                <span class="absolute top-1.5 right-1.5 inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                    {{ $loc->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                    {{ $loc->is_active ? 'Activa' : 'Inactiva' }}
                </span>
            </div>
            <div class="p-4">
                <h3 class="font-semibold text-slate-800">{{ $loc->name }}</h3>
                @if($loc->branch)<p class="text-xs text-slate-400">{{ $loc->branch->name }}</p>@endif
                @if($loc->address)<p class="text-xs text-slate-500 mt-0.5">{{ $loc->address }}</p>@endif

                <div class="grid grid-cols-2 gap-2 text-xs text-slate-500 mt-3 mb-3">
                    <div class="bg-slate-50 rounded-lg p-2">
                        <p class="font-medium text-slate-400 uppercase text-[10px] mb-0.5">Latitud</p>
                        <p class="font-mono">{{ $loc->latitude }}</p>
                    </div>
                    <div class="bg-slate-50 rounded-lg p-2">
                        <p class="font-medium text-slate-400 uppercase text-[10px] mb-0.5">Longitud</p>
                        <p class="font-mono">{{ $loc->longitude }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 text-xs text-slate-600 mb-3">
                    <svg class="w-4 h-4 text-indigo-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                    Radio: <strong>{{ $loc->radius_meters }} m</strong>
                </div>

                <a href="https://www.openstreetmap.org/?mlat={{ $loc->latitude }}&mlon={{ $loc->longitude }}&zoom=16"
                   target="_blank"
                   class="block w-full text-center text-xs text-indigo-600 hover:text-indigo-800 border border-indigo-100 hover:bg-indigo-50 rounded-lg py-1.5 transition">
                    Ver en OpenStreetMap ↗
                </a>
            </div>
            <div class="border-t border-slate-100 px-4 py-2.5 flex items-center justify-between bg-slate-50">
                <button wire:click="toggleActive({{ $loc->id }})"
                        class="text-xs {{ $loc->is_active ? 'text-slate-500 hover:text-slate-700' : 'text-green-600 hover:text-green-800' }} font-medium">
                    {{ $loc->is_active ? 'Desactivar' : 'Activar' }}
                </button>
                <div class="flex gap-3">
                    <button wire:click="openEdit({{ $loc->id }})" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Editar</button>
                    <button wire:click="delete({{ $loc->id }})"
                            wire:confirm="¿Eliminar esta zona? Los registros de asistencia no se verán afectados."
                            class="text-xs text-red-500 hover:text-red-700 font-medium">Eliminar</button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- ── Modal ── solo existe en DOM cuando showModal es true ────────── --}}
    @if($showModal)
    <div class="fixed inset-0 z-[100] flex items-end sm:items-center justify-center bg-black/50 p-0 sm:p-4">
        <div class="bg-white w-full sm:max-w-2xl rounded-t-2xl sm:rounded-2xl shadow-2xl flex flex-col max-h-[95vh] sm:max-h-[90vh]">

            {{-- Header --}}
            <div class="flex items-center justify-between px-4 sm:px-6 py-4 border-b border-slate-100 flex-shrink-0">
                <h3 class="text-base font-bold text-slate-800">
                    {{ $editingId ? 'Editar zona' : 'Nueva zona de asistencia' }}
                </h3>
                <button wire:click="$set('showModal', false)"
                        class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Cuerpo --}}
            <div class="overflow-y-auto flex-1 px-4 sm:px-6 py-4 space-y-4">

                {{-- Nombre --}}
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Nombre <span class="text-red-400">*</span></label>
                    <input wire:model="name" type="text"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                           placeholder="Ej. Oficina principal, Planta Norte">
                    @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Buscador de dirección — Alpine solo para el dropdown, sin $wire --}}
                <div x-data="{ query:'', results:[], loading:false }">
                    <label class="block text-xs font-medium text-slate-600 mb-1">
                        Buscar dirección
                        <span class="text-slate-400 font-normal">— o haz clic en el mapa</span>
                    </label>
                    <div class="relative">
                        <input x-model="query"
                               @input.debounce.600ms="
                                   if (query.length < 3) { results=[]; return; }
                                   loading = true;
                                   fetch('https://nominatim.openstreetmap.org/search?format=json&limit=6&q='+encodeURIComponent(query), {headers:{'Accept-Language':'es'}})
                                       .then(r=>r.json()).then(d=>{results=d;loading=false;}).catch(()=>loading=false);
                               "
                               @keydown.escape="results=[]"
                               type="text" autocomplete="off"
                               class="w-full border border-slate-200 rounded-lg pl-9 pr-8 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                               placeholder="Escribe una dirección, colonia, ciudad…">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <svg x-show="loading" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-indigo-400 animate-spin pointer-events-none"
                             fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                        </svg>
                        <div x-show="results.length > 0" x-cloak
                             class="absolute z-[9999] left-0 right-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-xl overflow-hidden max-h-52 overflow-y-auto">
                            <template x-for="r in results" :key="r.place_id">
                                <button type="button"
                                        @click="
                                            window.zoneSelectResult(parseFloat(r.lat), parseFloat(r.lon), r.display_name.split(',').slice(0,3).join(',').trim());
                                            query = r.display_name.split(',')[0];
                                            results = [];
                                        "
                                        class="w-full text-left px-4 py-2.5 text-sm hover:bg-indigo-50 border-b border-slate-50 last:border-0 transition">
                                    <span class="block font-medium text-slate-800 truncate" x-text="r.display_name.split(',')[0]"></span>
                                    <span class="block text-xs text-slate-400 truncate" x-text="r.display_name.split(',').slice(1,3).join(',')"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Mapa Leaflet --}}
                <div class="rounded-xl overflow-hidden border border-slate-200 shadow-sm">
                    <div id="zone-map"></div>
                </div>
                <p class="text-xs text-slate-400 text-center -mt-2">
                    Haz clic en el mapa o arrastra el marcador · el círculo azul muestra el radio
                </p>

                {{-- Coordenadas (solo lectura) --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Latitud</label>
                        <input wire:model="latitude" type="text" readonly
                               class="w-full border border-slate-100 bg-slate-50 rounded-lg px-3 py-2 text-sm font-mono text-slate-600 cursor-default"
                               placeholder="—">
                        @error('latitude')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Longitud</label>
                        <input wire:model="longitude" type="text" readonly
                               class="w-full border border-slate-100 bg-slate-50 rounded-lg px-3 py-2 text-sm font-mono text-slate-600 cursor-default"
                               placeholder="—">
                        @error('longitude')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- GPS --}}
                <button type="button" onclick="zoneUseMyLocation()"
                        class="w-full flex items-center justify-center gap-2 text-sm text-indigo-600 hover:text-indigo-800 border border-indigo-200 hover:bg-indigo-50 rounded-lg py-2 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Usar mi ubicación actual
                </button>

                {{-- Radio --}}
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-2">
                        Radio permitido <span class="text-red-400">*</span>
                        <span class="text-slate-400 font-normal ml-1">(círculo azul en el mapa)</span>
                    </label>
                    <div class="flex items-center gap-3">
                        <input wire:model.live="radius_meters"
                               oninput="zoneUpdateCircle(this.value)"
                               id="zone-radius"
                               type="range" min="10" max="2000" step="10"
                               class="flex-1 accent-indigo-600 h-2 cursor-pointer">
                        <span class="text-sm font-bold text-indigo-600 w-20 text-right tabular-nums">{{ $radius_meters }} m</span>
                    </div>
                    @error('radius_meters')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Dirección referencia --}}
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Dirección de referencia</label>
                    <input wire:model="address" type="text"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                           placeholder="Se llena al buscar, o escribe una referencia manual">
                </div>

                @if(count($branchOptions) > 0)
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Sucursal (opcional)</label>
                    <select wire:model="branch_id"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                        <option value="">Sin sucursal específica</option>
                        @foreach($branchOptions as $b)
                        <option value="{{ $b['id'] }}">{{ $b['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Notas</label>
                    <textarea wire:model="notes" rows="2"
                              class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 resize-none"
                              placeholder="Instrucciones, referencia visual, etc."></textarea>
                </div>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input wire:model="is_active" type="checkbox"
                           class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-300">
                    <span class="text-sm text-slate-700">Zona activa</span>
                </label>
            </div>

            {{-- Footer --}}
            <div class="flex gap-3 px-4 sm:px-6 py-4 border-t border-slate-100 flex-shrink-0">
                <button wire:click="save" wire:loading.attr="disabled"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition">
                    <span wire:loading.remove wire:target="save">Guardar zona</span>
                    <span wire:loading wire:target="save">Guardando…</span>
                </button>
                <button wire:click="$set('showModal', false)"
                        class="flex-1 text-slate-600 text-sm px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 transition">
                    Cancelar
                </button>
            </div>
        </div>
    </div>

    @endif

    {{-- Script del mapa: fuera del @if para que las funciones existan siempre --}}
    <script>
    (function () {
        let _map = null, _marker = null, _circle = null;

        function tryInitMap() {
            const container = document.getElementById('zone-map');
            if (typeof L === 'undefined' || !container) {
                if (container) setTimeout(tryInitMap, 100);
                return;
            }

            // Evitar doble inicialización si el div ya tiene un mapa
            if (container._leaflet_id) return;

            // Leer valores actuales de Livewire (usando @this o Livewire.find)
            const lat = parseFloat(@this.get('latitude')) || 0;
            const lng = parseFloat(@this.get('longitude')) || 0;
            const radius = parseInt(@this.get('radius_meters')) || 100;

            _map = L.map('zone-map', { zoomControl: true })
                    .setView(
                        lat ? [lat, lng] : [19.4326, -99.1332],
                        lat ? 15 : 5
                    );

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                maxZoom: 19,
            }).addTo(_map);

            setTimeout(() => _map.invalidateSize(), 150);

            if (lat) zoneSetMarker(lat, lng, radius, false);

            _map.on('click', function (e) {
                zoneSetMarker(e.latlng.lat, e.latlng.lng);
            });
        }

        // Exponer funciones globales
        window.zoneSetMarker = function (lat, lng, radius, pan) {
            if (!_map) return;
            lat    = parseFloat(parseFloat(lat).toFixed(7));
            lng    = parseFloat(parseFloat(lng).toFixed(7));
            radius = (radius !== undefined && radius !== null)
                        ? parseInt(radius)
                        : parseInt(document.getElementById('zone-radius')?.value || 100);
            pan    = (pan !== false);

            const icon = L.divIcon({
                html: `<div style="width:20px;height:20px;background:#4f46e5;border:3px solid #fff;
                            border-radius:50% 50% 50% 0;transform:rotate(-45deg);
                            box-shadow:0 2px 8px rgba(0,0,0,.4);"></div>`,
                iconSize: [20, 20], iconAnchor: [10, 20], className: '',
            });

            if (_marker) {
                _marker.setLatLng([lat, lng]);
            } else {
                _marker = L.marker([lat, lng], { draggable: true, icon }).addTo(_map);
                _marker.on('dragend', function (e) {
                    const ll = e.target.getLatLng();
                    zoneSetMarker(ll.lat, ll.lng);
                });
            }

            if (_circle) {
                _circle.setLatLng([lat, lng]).setRadius(radius);
            } else {
                _circle = L.circle([lat, lng], {
                    radius, color: '#4f46e5', fillColor: '#818cf8',
                    fillOpacity: 0.2, weight: 2, dashArray: '6 3',
                }).addTo(_map);
            }

            if (pan) _map.panTo([lat, lng]);

            @this.set('latitude',  lat.toString());
            @this.set('longitude', lng.toString());
        };

        window.zoneUpdateCircle = function (meters) {
            if (_circle) _circle.setRadius(parseInt(meters));
        };

        window.zoneSelectResult = function (lat, lng, address) {
            if (!_map) {
                // Si el mapa no está listo, reintentar brevemente
                setTimeout(() => window.zoneSelectResult(lat, lng, address), 100);
                return;
            }
            _map.setView([lat, lng], 16);
            zoneSetMarker(lat, lng);
            @this.set('address', address);
        };

        window.zoneUseMyLocation = function () {
            if (!navigator.geolocation) {
                alert('Tu navegador no soporta geolocalización.');
                return;
            }
            navigator.geolocation.getCurrentPosition(
                function (pos) {
                    if (_map) _map.setView([pos.coords.latitude, pos.coords.longitude], 16);
                    zoneSetMarker(pos.coords.latitude, pos.coords.longitude);
                },
                function (err) {
                    const msgs = { 1: 'Permisos denegados.', 2: 'Ubicación no disponible.', 3: 'Tiempo agotado.' };
                    alert(msgs[err.code] || 'Error: ' + err.message);
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        };

        // Escuchar cuando Livewire abre el modal para intentar inicializar el mapa
        // En Livewire v3 podemos usar transiciones o simplemente observar cambios
        document.addEventListener('livewire:initialized', () => {
            Livewire.hook('morph.updated', ({ el, component }) => {
                if (document.getElementById('zone-map')) {
                    tryInitMap();
                }
            });
        });

        // Intentar inicialización inmediata por si acaso
        tryInitMap();
    })();
    </script>
</div>{{-- /Livewire root --}}
