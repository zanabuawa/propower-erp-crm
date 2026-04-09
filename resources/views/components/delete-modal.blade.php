@props([
    'show'        => false,
    'title'       => '¿Eliminar registro?',
    'description' => 'Esta acción no se puede deshacer.',
    'confirm'     => 'delete',
    'cancel'      => 'cancelDelete',
])

@if($show)
<div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
     role="dialog" aria-modal="true" aria-labelledby="delete-modal-title">
    <div class="bg-white rounded-2xl border border-gray-200 p-6 w-full max-w-sm shadow-xl">
        <div class="flex items-start gap-4 mb-5">
            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0" aria-hidden="true">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>
            <div>
                <h3 id="delete-modal-title" class="font-semibold text-gray-900">{{ $title }}</h3>
                <p class="text-sm text-gray-500 mt-0.5">{{ $description }}</p>
            </div>
        </div>

        @isset($slot)
            @if($slot->isNotEmpty())
                <div class="mb-4 text-sm text-gray-600">{{ $slot }}</div>
            @endif
        @endisset

        <div class="flex gap-3 justify-end">
            <button wire:click="{{ $cancel }}"
                class="px-4 py-2 text-sm font-medium border border-gray-200 rounded-lg hover:bg-gray-50 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gray-400 focus-visible:ring-offset-2">
                Cancelar
            </button>
            <button wire:click="{{ $confirm }}"
                class="px-4 py-2 text-sm font-medium bg-red-600 hover:bg-red-700 text-white rounded-lg transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2">
                Sí, eliminar
            </button>
        </div>
    </div>
</div>
@endif
