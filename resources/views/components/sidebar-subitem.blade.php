@props(['route', 'label'])

@php
    $routeExists = \Illuminate\Support\Facades\Route::has($route);
    $active      = $routeExists && request()->routeIs($route);
    $url         = $routeExists ? route($route) : '#';
@endphp

<a href="{{ $url }}" @if($routeExists) wire:navigate @endif
    class="flex items-center gap-2 py-1.5 pr-3 text-xs transition-colors duration-150
        {{ $active ? 'text-indigo-300 bg-indigo-500/10' : 'text-white/40 hover:text-white/80 hover:bg-white/4' }}"
    :class="sidebarOpen ? 'pl-11' : 'pl-3 justify-center'"
>
    <span class="w-1 h-1 rounded-full flex-shrink-0 {{ $active ? 'bg-indigo-400' : 'bg-white/25' }}"></span>
    <span class="truncate transition-all duration-200" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0'">
        {{ $label }}
    </span>
</a>