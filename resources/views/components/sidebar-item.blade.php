@props(['route', 'icon', 'label', 'badge' => null, 'active' => false])

@php
    $routeExists = \Illuminate\Support\Facades\Route::has($route);
    $url = $routeExists ? route($route) : '#';
@endphp

<a href="{{ $url }}" @if($routeExists) wire:navigate @endif
   class="relative flex items-center gap-3 px-2.5 py-2 text-sm rounded-lg transition-colors duration-150
          {{ $active ? 'text-indigo-300 bg-indigo-500/10' : 'text-white/80 hover:bg-white/[0.06] hover:text-white' }}">
    <span class="w-5 h-5 min-w-[1.25rem] flex items-center justify-center flex-shrink-0">
        @includeIf('components.icons.' . $icon)
    </span>

    <span class="flex-1 truncate text-left font-medium transition-all duration-200 leading-none"
          :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0 overflow-hidden'">
        {{ $label }}
    </span>

    @if($badge)
        <span class="px-1.5 py-0.5 rounded-full bg-indigo-500/20 text-[10px] font-bold text-indigo-200"
              :class="sidebarOpen ? 'inline-flex' : 'hidden'">
            {{ $badge }}
        </span>
    @endif
</a>
