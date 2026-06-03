<div class="space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between px-1">
        <div>
            <h1 class="text-xl font-medium text-gray-900">Autorizaciones de descuento</h1>
            <p class="text-sm text-gray-500 mt-0.5">Revisa y gestiona solicitudes que superan el descuento máximo</p>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-lg px-4 py-3">
            {{ session('success') }}
        </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-center">
            <p class="text-2xl font-bold text-amber-700">{{ $counts['pending'] ?? 0 }}</p>
            <p class="text-xs text-amber-600 mt-0.5">Pendientes</p>
        </div>
        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 text-center">
            <p class="text-2xl font-bold text-emerald-700">{{ $counts['approved'] ?? 0 }}</p>
            <p class="text-xs text-emerald-600 mt-0.5">Aprobadas</p>
        </div>
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-center">
            <p class="text-2xl font-bold text-red-600">{{ $counts['rejected'] ?? 0 }}</p>
            <p class="text-xs text-red-500 mt-0.5">Rechazadas</p>
        </div>
    </div>

    {{-- Filtro status --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="flex border-b border-gray-100">
            @foreach(['pending' => 'Pendientes', 'approved' => 'Aprobadas', 'rejected' => 'Rechazadas', '' => 'Todas'] as $val => $label)
                <button type="button" wire:click="$set('filterStatus', '{{ $val }}')"
                    class="px-4 py-3 text-sm font-medium transition-colors
                        {{ $filterStatus === $val
                            ? 'border-b-2 border-indigo-600 text-indigo-700 bg-indigo-50/50'
                            : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- Tabla --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-500">Cotización / Documento</th>
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-500">Solicitante</th>
                        <th class="text-right px-4 py-3 text-xs font-medium text-gray-500">Desc. solicitado</th>
                        <th class="text-right px-4 py-3 text-xs font-medium text-gray-500">Máx. permitido</th>
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-500">Fecha</th>
                        <th class="text-center px-4 py-3 text-xs font-medium text-gray-500">Estado</th>
                        <th class="text-center px-4 py-3 text-xs font-medium text-gray-500 w-28">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($approvals as $approval)
                        @php
                            $model = $approval->approvable;
                            $folio = $model?->folio ?? '—';
                            $modelLabel = class_basename($approval->model_type);
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900">{{ $folio }}</p>
                                <p class="text-xs text-gray-400">{{ $modelLabel }}</p>
                                @if($approval->requester_notes)
                                    <p class="text-xs text-gray-500 mt-0.5 italic">"{{ Str::limit($approval->requester_notes, 60) }}"</p>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-gray-800">{{ $approval->requester?->name ?? '—' }}</p>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-red-600">
                                {{ number_format($approval->requested_discount_pct, 1) }}%
                            </td>
                            <td class="px-4 py-3 text-right text-gray-600">
                                {{ number_format($approval->max_allowed_pct, 1) }}%
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs">
                                {{ $approval->created_at->format('d/m/Y H:i') }}
                                @if($approval->responded_at)
                                    <br><span class="text-gray-400">Resp: {{ $approval->responded_at->format('d/m/Y H:i') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border
                                    {{ \App\Models\DiscountApproval::STATUS_COLORS[$approval->status] ?? 'bg-gray-100 text-gray-500 border-gray-200' }}">
                                    {{ \App\Models\DiscountApproval::STATUSES[$approval->status] ?? $approval->status }}
                                </span>
                                @if($approval->approver_notes)
                                    <p class="text-[10px] text-gray-400 mt-0.5 italic">{{ Str::limit($approval->approver_notes, 40) }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($approval->status === 'pending')
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" wire:click="openModal({{ $approval->id }}, 'approve')"
                                            class="px-2.5 py-1 text-[10px] font-semibold bg-emerald-100 text-emerald-700 hover:bg-emerald-200 rounded-lg transition">
                                            Aprobar
                                        </button>
                                        <button type="button" wire:click="openModal({{ $approval->id }}, 'reject')"
                                            class="px-2.5 py-1 text-[10px] font-semibold bg-red-100 text-red-600 hover:bg-red-200 rounded-lg transition">
                                            Rechazar
                                        </button>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">
                                        {{ $approval->approver?->name ?? '—' }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-gray-400 italic">
                                No hay solicitudes {{ $filterStatus ? 'con estado "'.($filterStatus).'"' : '' }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($approvals->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $approvals->links('vendor.pagination.tailwind') }}
            </div>
        @endif
    </div>

    {{-- ── Modal: Aprobar / Rechazar ─────────────────────────────────────── --}}
    @if($approvalId)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0
                        {{ $action === 'approve' ? 'bg-emerald-100' : 'bg-red-100' }}">
                        @if($action === 'approve')
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        @else
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        @endif
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">
                            {{ $action === 'approve' ? 'Aprobar descuento' : 'Rechazar solicitud' }}
                        </h3>
                        <p class="text-sm text-gray-500 mt-0.5">
                            {{ $action === 'approve'
                                ? 'El vendedor podrá guardar la cotización con el descuento solicitado.'
                                : 'El vendedor deberá ajustar el descuento dentro del rango permitido.' }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">
                        Notas para el vendedor <span class="text-gray-400">(opcional)</span>
                    </label>
                    <textarea wire:model="approverNotes" rows="3"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 resize-none"
                        placeholder="{{ $action === 'approve' ? 'Ej: Autorizado por volumen / cliente estratégico...' : 'Ej: El descuento máximo para este producto es 10%...' }}"></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="button" wire:click="closeModal"
                        class="flex-1 px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition font-medium">
                        Cancelar
                    </button>
                    <button type="button" wire:click="confirm"
                        class="flex-1 px-4 py-2 text-sm text-white rounded-lg font-semibold transition shadow-sm
                            {{ $action === 'approve'
                                ? 'bg-emerald-600 hover:bg-emerald-700'
                                : 'bg-red-600 hover:bg-red-700' }}">
                        {{ $action === 'approve' ? 'Confirmar aprobación' : 'Confirmar rechazo' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
