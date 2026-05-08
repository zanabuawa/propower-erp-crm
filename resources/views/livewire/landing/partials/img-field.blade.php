@php
    $tall    = $tall    ?? false;
    $compact = $compact ?? false;
@endphp

<div class="flex {{ $tall ? 'flex-col' : 'items-center' }} gap-2 w-full">

    {{-- Preview thumbnail --}}
    @if($src)
    <img src="{{ $src }}"
         alt="preview"
         onerror="this.style.display='none'"
         class="{{ $tall
                    ? 'w-full h-32 object-cover rounded-lg border border-slate-200'
                    : ($compact
                        ? 'w-10 h-10 object-cover rounded border border-slate-200 shrink-0'
                        : 'w-16 h-12 object-cover rounded-lg border border-slate-200 shrink-0') }}">
    @else
    <div class="{{ $tall
                    ? 'w-full h-32 rounded-lg border-2 border-dashed border-slate-200 flex items-center justify-center text-slate-300 text-xs'
                    : ($compact
                        ? 'w-10 h-10 rounded border-2 border-dashed border-slate-200 flex items-center justify-center text-slate-300 shrink-0'
                        : 'w-16 h-12 rounded-lg border-2 border-dashed border-slate-200 flex items-center justify-center text-slate-300 shrink-0') }}">
        <svg class="{{ $compact ? 'w-4 h-4' : 'w-5 h-5' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
    </div>
    @endif

    {{-- Path input + upload button --}}
    <div class="flex items-center gap-2 {{ $tall ? 'w-full' : 'flex-1 min-w-0' }}">
        <input wire:model="{{ $model }}"
               type="text"
               placeholder="/assets/img/…"
               class="field {{ $tall ? 'w-full' : 'flex-1 min-w-0' }} text-xs">

        <button type="button"
                x-on:click="$wire.call('setUploadTarget', '{{ $target }}').then(() => $refs.uploadInput.click())"
                title="Subir imagen"
                class="btn-upload shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
        </button>
    </div>

</div>
