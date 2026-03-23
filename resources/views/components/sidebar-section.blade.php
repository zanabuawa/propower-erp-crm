@props(['label'])

<p
    x-show="sidebarOpen"
    x-transition
    class="px-4 pt-3 pb-1 text-[10px] text-white/30 tracking-widest"
>{{ $label }}</p>