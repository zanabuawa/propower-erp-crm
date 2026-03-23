@props(['route', 'icon', 'label', 'badge' => null])

@php
    $routeExists = \Illuminate\Support\Facades\Route::has($route);
    $active = $routeExists && request()->routeIs($route);
    $url = $routeExists ? route($route) : '#';
@endphp

<a href="{{ $url }}"
    class="flex items-center gap-3 px-4 py-2 text-sm transition-colors duration-150
        {{ $active
            ? 'bg-indigo-500/20 text-indigo-300'
            : 'text-white/60 hover:bg-white/5 hover:text-white' }}"
>
    <span class="w-5 h-5 min-w-[1.25rem] flex items-center justify-center">
        @include('components.icons.' . $icon)
    </span>
    <span x-show="sidebarOpen" x-transition class="flex-1 truncate">{{ $label }}</span>
    @if($badge)
        <span x-show="sidebarOpen" class="bg-red-500 text-white text-[10px] px-1.5 rounded-full">
            {{ $badge }}
        </span>
    @endif
</a>