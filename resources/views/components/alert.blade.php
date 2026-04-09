@if(session('success'))
<div class="mb-5 flex items-center gap-3 px-4 py-3 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 text-sm rounded-r-lg shadow-sm" role="alert" aria-live="polite">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <span>{{ session('success') }}</span>
</div>
@endif

@if(session('error'))
<div class="mb-5 flex items-center gap-3 px-4 py-3 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm rounded-r-lg shadow-sm" role="alert" aria-live="polite">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <span>{{ session('error') }}</span>
</div>
@endif

@if(session('warning'))
<div class="mb-5 flex items-center gap-3 px-4 py-3 bg-amber-50 border-l-4 border-amber-500 text-amber-700 text-sm rounded-r-lg shadow-sm" role="alert" aria-live="polite">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
    </svg>
    <span>{{ session('warning') }}</span>
</div>
@endif
