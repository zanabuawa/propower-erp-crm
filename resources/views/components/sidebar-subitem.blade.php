@props(['route', 'label'])

@php
    $routeExists = \Illuminate\Support\Facades\Route::has($route);
    $active      = $routeExists && request()->routeIs($route);
    $url         = $routeExists ? route($route) : '#';
@endphp

<a href="{{ $url }}" @if($routeExists) wire:navigate @endif
   class="relative flex items-center gap-2.5 py-1.5 pr-3 text-xs font-medium rounded-md
          transition-colors duration-150 cursor-pointer
          {{ $active
              ? 'text-indigo-300 bg-indigo-500/10'
              : 'text-white/75 hover:text-white hover:bg-white/[0.06]' }}"
   :class="$el.closest('[data-flyout]') ? 'pl-3.5' : (sidebarOpen ? 'pl-10' : 'pl-2.5 justify-center')"
>
    {{-- Dot indicator --}}
    <span class="w-1.5 h-1.5 rounded-full flex-shrink-0 transition-colors duration-150
                 {{ $active ? 'bg-indigo-400' : 'bg-white/35' }}
                 group-hover:bg-white/40">
    </span>

    {{-- Label --}}
    <span class="truncate leading-none transition-all duration-200"
          :class="$el.closest('[data-flyout]') ? 'opacity-100' : (sidebarOpen ? 'opacity-100' : 'opacity-0 w-0 overflow-hidden')">
        {{ $label }}
    </span>

    {{-- Active right glow (accent) --}}
    @if($active)
    <span class="ml-auto w-1 h-1 rounded-full bg-indigo-400 flex-shrink-0"
          :class="$el.closest('[data-flyout]') ? 'opacity-100' : (sidebarOpen ? 'opacity-100' : 'hidden')">
    </span>
    @endif
</a>
