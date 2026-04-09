@props(['id', 'label', 'icon', 'routes' => []])

@php
    $isActive = collect($routes)->contains(fn($r) => request()->routeIs($r));
@endphp

<div x-data="{ hovered: false }" class="relative"
     @mouseenter="hovered = true"
     @mouseleave="hovered = false">

    <button
        @click="sidebarOpen ? toggleMenu('{{ $id }}') : (sidebarOpen = true, saveState())"
        class="w-full flex items-center gap-3 px-3 py-2 text-sm transition-colors duration-150
            {{ $isActive ? 'text-indigo-300' : 'text-white/55 hover:bg-white/6 hover:text-white' }}"
        :class="isOpen('{{ $id }}') && sidebarOpen ? 'bg-white/5 text-white/80' : ''"
        aria-expanded="false"
        :aria-expanded="(isOpen('{{ $id }}') && sidebarOpen).toString()"
    >
        <span class="w-5 h-5 min-w-[1.25rem] flex items-center justify-center">
            @include('components.icons.' . $icon)
        </span>
        <span class="flex-1 truncate text-left transition-all duration-200"
              :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0'">
            {{ $label }}
        </span>
        <svg class="sb-chevron w-3.5 h-3.5 min-w-[0.875rem] text-white/30 transition-all duration-200"
             :class="[isOpen('{{ $id }}') && sidebarOpen ? 'open' : '', sidebarOpen ? 'opacity-100' : 'opacity-0 w-0 min-w-0']"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 18l6-6-6-6"/>
        </svg>
    </button>

    {{-- Acordeón (sidebar expandido) --}}
    <div class="sb-sub" :class="isOpen('{{ $id }}') && sidebarOpen ? 'open' : ''">
        {{ $slot }}
    </div>

    {{-- Flyout (sidebar colapsado) --}}
    <div x-show="!sidebarOpen && hovered"
         x-data="{ sidebarOpen: true }"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-x-1"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-x-0"
         x-transition:leave-end="opacity-0 -translate-x-1"
         data-flyout="true"
         class="absolute left-[3.6rem] top-0 z-50 min-w-52 bg-[#1a1d2e] border border-white/10 rounded-xl shadow-2xl overflow-hidden"
         style="display:none">
        {{-- Cabecera del flyout --}}
        <div class="px-3 py-2.5 border-b border-white/8">
            <p class="text-xs font-semibold text-white/80">{{ $label }}</p>
        </div>
        {{-- Subelementos --}}
        <div class="py-1">
            {{ $slot }}
        </div>
    </div>
</div>
