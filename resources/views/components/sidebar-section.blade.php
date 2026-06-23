@props(['label'])

<div class="px-2.5 pt-4 pb-1.5 first:pt-1">
    <p class="text-[10px] font-black uppercase tracking-[0.16em] text-white/30 transition-all duration-200"
       :class="sidebarOpen ? 'opacity-100' : 'opacity-0 h-0 overflow-hidden'">
        {{ $label }}
    </p>
</div>
