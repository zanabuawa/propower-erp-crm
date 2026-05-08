{{--
    Picker modal de imágenes de la biblioteca (/assets/img/gallery/).
    Variables opcionales:
      $withUpload (bool) — muestra botón para subir fotos nuevas (sólo gallery editor)
      $uploadRef  (string) — x-ref del input file para el upload (default: "libUpload")
--}}
@php $withUpload = $withUpload ?? false; $uploadRef = $uploadRef ?? 'libUpload'; @endphp

@if($showPicker)
<div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4"
     style="background:rgba(15,23,42,0.65); backdrop-filter:blur(4px)">

    <div class="bg-white w-full sm:rounded-2xl shadow-2xl flex flex-col"
         style="max-width:900px; max-height:88vh">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 shrink-0">
            <div>
                <h3 class="font-bold text-slate-900 text-base">Biblioteca de imágenes</h3>
                <p class="text-xs text-slate-400 mt-0.5">{{ count($this->library) }} fotos disponibles · haz clic para seleccionar</p>
            </div>
            <div class="flex items-center gap-3">
                @if($withUpload)
                {{-- Upload a la biblioteca --}}
                <input type="file" x-ref="{{ $uploadRef }}" class="hidden" accept="image/*" wire:model="libraryUpload">
                <button
                    x-on:click="$refs.{{ $uploadRef }}.click()"
                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-indigo-600 text-white text-xs font-semibold rounded-lg hover:bg-indigo-700 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Subir foto
                </button>
                @endif
                <div class="relative">
                    <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input wire:model.live="pickerSearch" type="text" placeholder="Buscar…"
                           class="pl-8 pr-3 py-1.5 text-xs border border-slate-200 rounded-lg w-44 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                <button wire:click="closePicker"
                        class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 text-slate-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Spinner de upload --}}
        <div wire:loading wire:target="libraryUpload"
             class="flex items-center gap-3 px-6 py-3 bg-indigo-50 border-b border-indigo-100 shrink-0">
            <svg class="animate-spin w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
            <span class="text-xs font-semibold text-indigo-700">Subiendo imagen a la biblioteca…</span>
        </div>

        {{-- Grid --}}
        <div class="flex-1 overflow-y-auto p-4">
            @php
                $filtered = collect($this->library)
                    ->when($pickerSearch, fn($c) => $c->filter(
                        fn($p) => str_contains(strtolower(basename($p)), strtolower($pickerSearch))
                    ))->values();
            @endphp

            @if($filtered->isEmpty())
                <div class="text-center py-16 text-slate-400">
                    <svg class="w-12 h-12 mx-auto mb-3 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    @if($pickerSearch)
                        <p class="text-sm">Sin resultados para "<strong>{{ $pickerSearch }}</strong>"</p>
                    @else
                        <p class="text-sm">La biblioteca está vacía.</p>
                        @if($withUpload)
                            <p class="text-xs mt-1">Sube tu primera foto con el botón de arriba.</p>
                        @endif
                    @endif
                </div>
            @else
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
                    @foreach($filtered as $imgPath)
                    <button wire:click="selectMedia('{{ $imgPath }}')"
                            class="group relative aspect-square overflow-hidden rounded-lg border-2 border-transparent hover:border-indigo-500 focus:outline-none focus:border-indigo-500 transition-all">
                        <img src="{{ $imgPath }}" alt=""
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        <div class="absolute inset-0 bg-indigo-600/0 group-hover:bg-indigo-600/20 transition-colors flex items-center justify-center">
                            <svg class="w-7 h-7 text-white opacity-0 group-hover:opacity-100 transition-opacity drop-shadow-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    </button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Footer --}}
        <div class="px-6 py-3 border-t border-slate-100 shrink-0 flex justify-between items-center">
            <span class="text-xs text-slate-400">
                {{ $filtered->count() }} foto(s) mostrada(s)
            </span>
            <button wire:click="closePicker"
                    class="px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                Cancelar
            </button>
        </div>
    </div>
</div>
@endif
