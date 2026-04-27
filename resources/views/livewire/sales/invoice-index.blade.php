<div class="min-h-screen bg-slate-50/50 -m-4 sm:-m-6 lg:-m-8">
    {{-- STICKY HEADER --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-xl bg-violet-600 flex items-center justify-center text-white shrink-0 shadow-lg shadow-violet-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">Facturas</h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Gestiona tus facturas de venta</p>
                </div>
            </div>
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a wire:navigate href="{{ route('sales.invoices.create') }}"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Nueva factura</span>
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto p-4 sm:p-6 lg:p-8 space-y-8">
        @if(session('success'))
            <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl animate-in fade-in slide-in-from-top-4 duration-300">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-sm font-semibold">{{ session('success') }}</p>
            </div>
        @endif

        {{-- Filters --}}
        <div class="bg-white p-4 rounded-3xl border border-slate-200/60 shadow-sm flex flex-wrap gap-4 items-center">
            <div class="flex-1 min-w-[280px] relative group">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por folio o cliente..."
                    class="w-full pl-11 pr-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm">
            </div>
            <select wire:model.live="filterStatus" aria-label="Filtrar por estado"
                class="px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm font-bold text-slate-600">
                <option value="">Todos los estados</option>
                @foreach(\App\Models\SaleInvoice::STATUS as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[700px]">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Folio</th>
                            <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Cliente</th>
                            <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest hidden md:table-cell">Tipo</th>
                            <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total</th>
                            <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest hidden sm:table-cell">Pagado</th>
                            <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest hidden sm:table-cell">Saldo</th>
                            <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Estado</th>
                            <th class="px-6 py-4 text-right text-[10px] font-bold text-slate-400 uppercase tracking-widest">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($invoices as $invoice)
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <p class="font-mono text-sm font-bold text-slate-700">{{ $invoice->folio }}</p>
                                    <p class="text-[10px] font-medium text-slate-400 uppercase tracking-tight sm:hidden mt-0.5">{{ $invoice->customer->name }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm font-semibold text-slate-700">{{ $invoice->customer->name }}</p>
                                </td>
                                <td class="px-6 py-4 hidden md:table-cell">
                                    <span class="text-xs font-semibold {{ $invoice->type === 'cfdi' ? 'text-indigo-600' : 'text-slate-500' }}">
                                        {{ $invoice->type === 'cfdi' ? 'CFDI' : 'Interna' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-mono text-sm font-bold text-slate-800">{{ $invoice->currency }} ${{ number_format($invoice->total, 2) }}</p>
                                </td>
                                <td class="px-6 py-4 hidden sm:table-cell">
                                    <p class="font-mono text-sm font-semibold text-emerald-600">${{ number_format($invoice->paid_amount, 2) }}</p>
                                </td>
                                <td class="px-6 py-4 hidden sm:table-cell">
                                    <p class="font-mono text-sm font-semibold {{ ($invoice->total - $invoice->paid_amount) > 0 ? 'text-red-500' : 'text-slate-400' }}">
                                        ${{ number_format($invoice->total - $invoice->paid_amount, 2) }}
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $status = $invoice->status;
                                        $statusBadge = match($status) {
                                            'paid'      => 'bg-emerald-50 text-emerald-600 border border-emerald-200',
                                            'pending'   => 'bg-amber-50 text-amber-600 border border-amber-200',
                                            'draft'     => 'bg-slate-100 text-slate-400 border border-slate-200',
                                            'cancelled' => 'bg-red-50 text-red-500 border border-red-200',
                                            default     => 'bg-slate-100 text-slate-400 border border-slate-200',
                                        };
                                    @endphp
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider {{ $statusBadge }}">
                                        {{ \App\Models\SaleInvoice::STATUS[$invoice->status] ?? $invoice->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a wire:navigate href="{{ route('sales.invoices.show', $invoice) }}"
                                            class="p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 hover:shadow-sm transition-all" title="Ver">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="w-10 h-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                                        <p class="text-slate-400 text-sm font-medium">No se encontraron facturas.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($invoices->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/30">
                    {{ $invoices->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
