@props(['id', 'label', 'icon', 'routes' => []])

@php
    $isActive = collect($routes)->contains(fn($r) => request()->routeIs($r));
@endphp

<div x-data>
    <button
        @click="toggleMenu('{{ $id }}')"
        class="w-full flex items-center gap-3 px-3 py-2 text-sm transition-colors duration-150 relative group
            {{ $isActive ? 'text-indigo-300' : 'text-white/55 hover:bg-white/6 hover:text-white' }}"
        :class="isOpen('{{ $id }}') ? 'bg-white/5 text-white/80' : ''"
    >
        <span class="w-5 h-5 min-w-[1.25rem] flex items-center justify-center">
            @include('components.icons.' . $icon)
        </span>
        <span class="flex-1 truncate text-left transition-all duration-200"
            :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0'">
            {{ $label }}
        </span>
        <svg class="sb-chevron w-3.5 h-3.5 min-w-[0.875rem] text-white/30 transition-all duration-200"
            :class="[isOpen('{{ $id }}') ? 'open' : '', sidebarOpen ? 'opacity-100' : 'opacity-0 w-0 min-w-0']"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 18l6-6-6-6"/>
        </svg>
        <span x-show="!sidebarOpen"
            class="absolute left-14 bg-[#1a1d2e] border border-white/10 text-white text-xs px-2 py-1 rounded-md whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-50">
            {{ $label }}
        </span>
    </button>

    <div class="sb-sub" :class="isOpen('{{ $id }}') ? 'open' : ''">
        {{ $slot }}
    </div>
</div>