{{-- País --}}
@if($showCountry ?? true)
<div>
    <label class="block text-xs text-gray-500 mb-1">País *</label>
    <select wire:model.live="countryId"
        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 disabled:bg-gray-50 disabled:text-gray-400">
        <option value="0">— Seleccionar país —</option>
        @foreach($availableCountries as $id => $name)
            <option value="{{ $id }}">{{ $name }}</option>
        @endforeach
    </select>
    @error('country') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
</div>
@endif

{{-- Estado --}}
<div>
    <label class="block text-xs text-gray-500 mb-1">Estado</label>
    <select wire:model.live="stateId"
        @disabled(!$countryId)
        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 disabled:bg-gray-50 disabled:text-gray-400">
        <option value="0">— Seleccionar estado —</option>
        @foreach($availableStates as $id => $name)
            <option value="{{ $id }}">{{ $name }}</option>
        @endforeach
    </select>
    @error('state') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
</div>

{{-- Ciudad --}}
<div>
    <label class="block text-xs text-gray-500 mb-1">Ciudad</label>
    <select wire:model.live="cityId"
        @disabled(!$stateId)
        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 disabled:bg-gray-50 disabled:text-gray-400">
        <option value="0">— Seleccionar ciudad —</option>
        @foreach($availableCities as $id => $name)
            <option value="{{ $id }}">{{ $name }}</option>
        @endforeach
    </select>
    @error('city') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
</div>
