@props(['id', 'label', 'icon', 'routes' => []])

@php
    $isActive = collect($routes)->contains(fn($r) => request()->routeIs($r));
@endphp

<div x-data="{ hovered: false }" class="relative"
     @mouseenter="hovered = true"
     @mouseleave="hovered = false">

    {{-- ── Trigger button ─────────────────────────────────────────────── --}}
    <button
        @click="sidebarOpen ? toggleMenu('{{ $id }}') : (sidebarOpen = true, saveState())"
        class="relative w-full flex items-center gap-3 px-2.5 py-2 text-sm rounded-lg
               transition-colors duration-150 cursor-pointer
               {{ $isActive
                   ? 'text-indigo-300 bg-indigo-500/10 sb-item-active'
                   : 'text-white/80 hover:bg-white/[0.06] hover:text-white' }}"
        :class="isOpen('{{ $id }}') && sidebarOpen && !{{ $isActive ? 'true' : 'false' }}
                ? 'bg-white/[0.04] text-white/90' : ''"
        :aria-expanded="(isOpen('{{ $id }}') && sidebarOpen).toString()"
        aria-haspopup="true"
    >
        {{-- Icon --}}
        <span class="w-5 h-5 min-w-[1.25rem] flex items-center justify-center flex-shrink-0">
            @include('components.icons.' . $icon)
        </span>

        {{-- Label --}}
        <span class="flex-1 truncate text-left font-medium transition-all duration-200 leading-none"
              :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0 overflow-hidden'">
            {{ $label }}
        </span>

        {{-- Chevron --}}
        <svg class="sb-chevron w-3.5 h-3.5 min-w-[0.875rem] flex-shrink-0 text-white/25 transition-all duration-200"
             :class="[
                 isOpen('{{ $id }}') && sidebarOpen ? 'open text-white/40' : '',
                 sidebarOpen ? 'opacity-100' : 'opacity-0 w-0 min-w-0 overflow-hidden'
             ]"
             fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 18l6-6-6-6"/>
        </svg>

        {{-- Tooltip (collapsed, no flyout) --}}
        <span x-show="!sidebarOpen && !hovered"
              class="pointer-events-none absolute left-[3.8rem] z-50 whitespace-nowrap
                     rounded-lg bg-slate-800 border border-white/10 shadow-xl
                     px-2.5 py-1.5 text-xs font-medium text-white/90
                     opacity-0 group-hover:opacity-100 transition-opacity duration-150">
            {{ $label }}
        </span>
    </button>

    {{-- ── Accordion submenu (expanded sidebar) ───────────────────────── --}}
    <div class="sb-sub mt-0.5 space-y-0.5"
         :class="isOpen('{{ $id }}') && sidebarOpen ? 'open' : ''">
        {{ $slot }}
    </div>

    {{-- ── Flyout panel (collapsed sidebar) ──────────────────────────── --}}
    <div x-show="!sidebarOpen && hovered"
         x-data="{ sidebarOpen: true }"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-x-2 scale-95"
         x-transition:enter-end="opacity-100 translate-x-0 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-x-0 scale-100"
         x-transition:leave-end="opacity-0 -translate-x-2 scale-95"
         data-flyout="true"
         class="absolute left-[3.5rem] top-0 z-50 min-w-[13rem]
                rounded-xl border border-white/[0.08] shadow-2xl overflow-hidden"
         style="display:none; background: #161b2e;"
    >
        {{-- Flyout header --}}
        <div class="flex items-center gap-2.5 px-3.5 py-2.5 border-b border-white/[0.06]">
            <span class="w-4 h-4 flex items-center justify-center text-indigo-400 flex-shrink-0">
                @include('components.icons.' . $icon)
            </span>
            <p class="text-[12px] font-semibold text-white/80 truncate">{{ $label }}</p>
        </div>
        {{-- Flyout items --}}
        <div class="py-1.5 space-y-0.5 px-1.5">
            {{ $slot }}
        </div>
    </div>

</div>
