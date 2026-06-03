<div class="min-h-screen bg-slate-50/50 -m-4 sm:-m-6 lg:-m-8">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('hr.attendance.locations') }}" 
                   class="group flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:shadow-sm transition-all duration-200">
                    <svg class="w-5 h-5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">
                        {{ $attendanceLocation?->exists ? 'Editar Zona' : 'Nueva Zona de Asistencia' }}
                    </h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">
                        {{ $attendanceLocation?->exists ? $name : 'Configuración de perímetros para check-in por geolocalización' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a wire:navigate href="{{ route('hr.attendance.locations') }}"
                    class="hidden sm:inline-flex px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">
                    Cancelar
                </a>
                <button type="button" wire:click="save"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>{{ $attendanceLocation?->exists ? 'Guardar cambios' : 'Crear zona' }}</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
        <form wire:submit="save" class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8">

            {{-- ── COLUMNA IZQUIERDA: Ubicación (7 cols) ────────── --}}
            <div class="lg:col-span-7 space-y-6 lg:space-y-8">
                
                {{-- Card: Datos Geográficos --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Coordenadas y Perímetro</h3>
                    </div>
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Nombre identificador *</label>
                            <input wire:model="name" type="text" placeholder="Ej. Oficinas Centrales, Almacén Norte..."
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold">
                            @error('name') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-3"
                            wire:ignore
                            x-data="attendanceZoneMap({
                                lat: @entangle('latitude').live,
                                lng: @entangle('longitude').live,
                                radius: @entangle('radius_meters').live
                            })">
                            <div class="flex items-center justify-between gap-3">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Seleccionar en mapa</label>
                                <div class="flex items-center gap-2">
                                    <button type="button" x-on:click="useCurrentLocation" x-bind:disabled="locating || calibrating"
                                        class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white text-[10px] font-black uppercase tracking-wider text-slate-500 hover:text-indigo-600 hover:border-indigo-200 hover:bg-indigo-50 disabled:opacity-60 transition-colors">
                                        <span x-show="!locating">Usar mi GPS</span>
                                        <span x-show="locating">Localizando...</span>
                                    </button>
                                    <button type="button" x-on:click="calibrateCurrentLocation" x-bind:disabled="locating || calibrating"
                                        class="px-3 py-1.5 rounded-xl border border-indigo-100 bg-indigo-50 text-[10px] font-black uppercase tracking-wider text-indigo-600 hover:bg-indigo-100 disabled:opacity-60 transition-colors">
                                        <span x-show="!calibrating">Calibrar</span>
                                        <span x-show="calibrating">Calibrando...</span>
                                    </button>
                                </div>
                            </div>
                            <div class="relative">
                                <div class="flex gap-2">
                                    <input type="text" x-model="searchQuery" x-on:keydown.enter.prevent="searchAddress"
                                        placeholder="Buscar direccion, colonia, ciudad..."
                                        class="min-w-0 flex-1 px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm">
                                    <button type="button" x-on:click="searchAddress" x-bind:disabled="searching"
                                        class="px-4 py-3 rounded-2xl bg-indigo-600 text-white text-xs font-black uppercase tracking-wider hover:bg-indigo-700 disabled:opacity-60 transition-colors">
                                        <span x-show="!searching">Buscar</span>
                                        <span x-show="searching">...</span>
                                    </button>
                                </div>

                                <div x-show="searchResults.length" x-cloak
                                    class="absolute z-[1000] mt-2 w-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-200/70">
                                    <template x-for="result in searchResults" :key="result.place_id">
                                        <button type="button" x-on:click="selectSearchResult(result)"
                                            class="block w-full px-4 py-3 text-left text-sm font-semibold text-slate-700 hover:bg-indigo-50 hover:text-indigo-700 transition-colors">
                                            <span x-text="result.display_name"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                            <div x-ref="map" class="h-[360px] rounded-3xl border border-slate-200 overflow-hidden bg-slate-100"></div>
                            <p x-show="locationMessage" x-cloak
                                class="text-[10px] font-bold uppercase tracking-wider ml-1"
                                x-bind:class="locationMessageType === 'error' ? 'text-red-500' : 'text-indigo-500'"
                                x-text="locationMessage"></p>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider ml-1">Haz clic en el mapa o arrastra el marcador para definir la zona.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Latitud *</label>
                                <input wire:model.live="latitude" type="text" placeholder="19.4326"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-mono text-sm font-bold">
                                @error('latitude') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Longitud *</label>
                                <input wire:model.live="longitude" type="text" placeholder="-99.1332"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-mono text-sm font-bold">
                                @error('longitude') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Radio de tolerancia (metros) *</label>
                            <div class="flex items-center gap-4">
                                <input type="range" wire:model.live="radius_meters" min="10" max="1000" step="10" class="flex-1 accent-indigo-600">
                                <span class="px-4 py-2 bg-indigo-50 text-indigo-700 rounded-xl font-mono font-black text-sm w-24 text-center border border-indigo-100">
                                    {{ $radius_meters }}m
                                </span>
                            </div>
                            @error('radius_meters') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="p-6 lg:p-8 space-y-2">
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Dirección física descriptiva</label>
                        <textarea wire:model="address" rows="3" placeholder="Calle, número, colonia, CP..."
                            class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm"></textarea>
                    </div>
                </div>
            </div>

            {{-- ── COLUMNA DERECHA: Configuración (5 cols) ───────────── --}}
            <div class="lg:col-span-5 space-y-6 lg:space-y-8">
                
                {{-- Card: Sucursal --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Sucursal vinculada</label>
                            <select wire:model="branch_id"
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm font-bold text-slate-700">
                                <option value="">— Ninguna (Zona independiente) —</option>
                                @foreach($branches as $br)
                                    <option value="{{ $br->id }}">{{ $br->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-center justify-between p-4 rounded-2xl bg-indigo-50/50 border border-indigo-100/50">
                            <div>
                                <p class="text-xs font-bold text-slate-700">Estado de la zona</p>
                                <p class="text-[10px] text-indigo-600 uppercase font-bold tracking-wider">Activa para check-in</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input wire:model="is_active" type="checkbox" class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Card: Notas --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="p-6 lg:p-8">
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Notas internas</label>
                        <textarea wire:model="notes" rows="4" placeholder="Instrucciones especiales para esta ubicación..."
                            class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm resize-none"></textarea>
                    </div>
                </div>

            </div>
        </form>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>

    <script>
        function attendanceZoneMap(bindings) {
            return {
                map: null,
                marker: null,
                circle: null,
                lat: bindings.lat,
                lng: bindings.lng,
                radius: bindings.radius,
                searchQuery: '',
                searching: false,
                searchResults: [],
                locating: false,
                calibrating: false,
                locationMessage: '',
                locationMessageType: 'info',
                defaultLat: 28.1906,
                defaultLng: -105.4706,
                init() {
                    this.loadLeaflet().then(() => this.initMap());
                },
                loadLeaflet() {
                    return new Promise((resolve) => {
                        if (window.L) {
                            resolve();
                            return;
                        }

                        const cssHref = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                        if (!document.querySelector(`link[href='${cssHref}']`)) {
                            const link = document.createElement('link');
                            link.rel = 'stylesheet';
                            link.href = cssHref;
                            document.head.appendChild(link);
                        }

                        const existingScript = document.querySelector('script[data-leaflet]');
                        if (existingScript) {
                            existingScript.addEventListener('load', resolve, { once: true });
                            return;
                        }

                        const script = document.createElement('script');
                        script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                        script.dataset.leaflet = 'true';
                        script.onload = resolve;
                        document.head.appendChild(script);
                    });
                },
                initMap() {
                    const startLat = parseFloat(this.lat) || this.defaultLat;
                    const startLng = parseFloat(this.lng) || this.defaultLng;
                    const startRadius = parseInt(this.radius || 100, 10);

                    this.map = L.map(this.$refs.map).setView([startLat, startLng], this.lat && this.lng ? 16 : 13);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap'
                    }).addTo(this.map);

                    this.marker = L.marker([startLat, startLng], { draggable: true }).addTo(this.map);
                    this.circle = L.circle([startLat, startLng], {
                        radius: startRadius,
                        color: '#4f46e5',
                        weight: 2,
                        fillColor: '#6366f1',
                        fillOpacity: 0.16
                    }).addTo(this.map);

                    this.map.on('click', (event) => this.setPoint(event.latlng.lat, event.latlng.lng, true));
                    this.marker.on('dragend', () => {
                        const point = this.marker.getLatLng();
                        this.setPoint(point.lat, point.lng, true);
                    });

                    this.$watch('radius', (value) => {
                        if (this.circle) this.circle.setRadius(parseInt(value || 100, 10));
                    });
                    this.$watch('lat', () => this.syncFromInputs());
                    this.$watch('lng', () => this.syncFromInputs());

                    setTimeout(() => this.map.invalidateSize(), 250);
                },
                setPoint(lat, lng, center = false) {
                    const cleanLat = Number(lat).toFixed(6);
                    const cleanLng = Number(lng).toFixed(6);
                    this.lat = cleanLat;
                    this.lng = cleanLng;
                    this.marker.setLatLng([cleanLat, cleanLng]);
                    this.circle.setLatLng([cleanLat, cleanLng]);
                    if (center) this.map.panTo([cleanLat, cleanLng]);
                },
                syncFromInputs() {
                    const nextLat = parseFloat(this.lat);
                    const nextLng = parseFloat(this.lng);
                    if (!this.map || Number.isNaN(nextLat) || Number.isNaN(nextLng)) return;

                    this.marker.setLatLng([nextLat, nextLng]);
                    this.circle.setLatLng([nextLat, nextLng]);
                    this.map.panTo([nextLat, nextLng]);
                },
                useCurrentLocation() {
                    if (!navigator.geolocation) {
                        this.locationMessage = 'Tu navegador no permite geolocalizacion.';
                        this.locationMessageType = 'error';
                        return;
                    }

                    this.locating = true;
                    this.locationMessage = 'Buscando tu ubicacion actual...';
                    this.locationMessageType = 'info';

                    navigator.geolocation.getCurrentPosition((position) => {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;

                        this.setPoint(lat, lng, true);
                        this.map.setZoom(17);
                        this.locationMessage = 'Ubicacion detectada. Ajusta el radio si necesitas ampliar la zona.';
                        this.locationMessageType = 'info';
                        this.reverseGeocode(lat, lng);
                        this.locating = false;
                    }, () => {
                        this.locating = false;
                        this.locationMessage = 'No se pudo obtener tu ubicacion. Revisa permisos de GPS del navegador.';
                        this.locationMessageType = 'error';
                    }, {
                        enableHighAccuracy: true,
                        timeout: 12000,
                        maximumAge: 0
                    });
                },
                getCurrentPosition() {
                    return new Promise((resolve, reject) => {
                        navigator.geolocation.getCurrentPosition(resolve, reject, {
                            enableHighAccuracy: true,
                            timeout: 15000,
                            maximumAge: 0
                        });
                    });
                },
                wait(milliseconds) {
                    return new Promise((resolve) => setTimeout(resolve, milliseconds));
                },
                async calibrateCurrentLocation() {
                    if (!navigator.geolocation) {
                        this.locationMessage = 'Tu navegador no permite geolocalizacion.';
                        this.locationMessageType = 'error';
                        return;
                    }

                    this.calibrating = true;
                    this.locationMessageType = 'info';
                    const samples = [];

                    try {
                        for (let index = 1; index <= 6; index++) {
                            this.locationMessage = `Calibrando GPS ${index}/6. Permanece quieto en la zona...`;
                            const position = await this.getCurrentPosition();
                            samples.push({
                                lat: position.coords.latitude,
                                lng: position.coords.longitude,
                                accuracy: position.coords.accuracy || 999
                            });

                            if (index < 6) {
                                await this.wait(900);
                            }
                        }

                        const bestSamples = samples
                            .sort((a, b) => a.accuracy - b.accuracy)
                            .slice(0, 3);
                        const totalWeight = bestSamples.reduce((sum, item) => sum + (1 / Math.max(item.accuracy, 1)), 0);
                        const lat = bestSamples.reduce((sum, item) => sum + item.lat * (1 / Math.max(item.accuracy, 1)), 0) / totalWeight;
                        const lng = bestSamples.reduce((sum, item) => sum + item.lng * (1 / Math.max(item.accuracy, 1)), 0) / totalWeight;
                        const bestAccuracy = bestSamples[0]?.accuracy || null;

                        this.setPoint(lat, lng, true);
                        this.map.setZoom(18);
                        this.reverseGeocode(lat, lng);
                        this.locationMessage = bestAccuracy
                            ? `GPS calibrado con precision aprox. de ${bestAccuracy.toFixed(1)} m. Guarda la zona si el pin esta correcto.`
                            : 'GPS calibrado. Guarda la zona si el pin esta correcto.';
                    } catch (error) {
                        this.locationMessage = 'No se pudo calibrar el GPS. Revisa permisos, señal y vuelve a intentarlo.';
                        this.locationMessageType = 'error';
                    } finally {
                        this.calibrating = false;
                    }
                },
                async reverseGeocode(lat, lng) {
                    try {
                        const params = new URLSearchParams({
                            format: 'json',
                            lat: Number(lat).toFixed(6),
                            lon: Number(lng).toFixed(6),
                            zoom: '18',
                            addressdetails: '1'
                        });
                        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?${params.toString()}`);

                        if (!response.ok) return;

                        const result = await response.json();
                        const displayName = result.display_name || '';
                        if (!displayName) return;

                        this.searchQuery = displayName;

                        if (this.$wire) {
                            this.$wire.set('address', displayName);
                        }
                    } catch (error) {
                        // La zona queda definida aunque no se pueda resolver la direccion.
                    }
                },
                async searchAddress() {
                    const query = this.searchQuery.trim();
                    if (!query) {
                        this.searchResults = [];
                        return;
                    }

                    this.searching = true;

                    try {
                        const params = new URLSearchParams({
                            format: 'json',
                            limit: '5',
                            addressdetails: '1',
                            q: query
                        });
                        const response = await fetch(`https://nominatim.openstreetmap.org/search?${params.toString()}`);

                        if (!response.ok) {
                            this.searchResults = [];
                            return;
                        }

                        this.searchResults = await response.json();
                    } catch (error) {
                        this.searchResults = [];
                    } finally {
                        this.searching = false;
                    }
                },
                selectSearchResult(result) {
                    this.searchResults = [];
                    this.searchQuery = result.display_name || '';
                    this.setPoint(parseFloat(result.lat), parseFloat(result.lon), true);
                    this.map.setZoom(17);

                    if (this.$wire) {
                        this.$wire.set('address', this.searchQuery);
                    }
                }
            };
        }
    </script>
</div>
