<div class="relative">
    <button wire:click="toggle" class="relative p-1.5 text-gray-500 hover:text-gray-800 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        @if($count > 0)
            <span class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-amber-400 text-white text-[10px] font-bold rounded-full flex items-center justify-center">
                {{ $count > 9 ? '9+' : $count }}
            </span>
        @endif
    </button>

    @if($open)
        <div class="absolute right-0 top-9 w-80 bg-white border border-gray-200 rounded-xl shadow-lg z-50">
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                <span class="text-sm font-medium text-gray-900">Notificaciones</span>
                @if($count > 0)
                    <button wire:click="markAllAsRead" class="text-xs text-indigo-600 hover:text-indigo-800">
                        Marcar todas como leídas
                    </button>
                @endif
            </div>
            <div class="max-h-96 overflow-y-auto divide-y divide-gray-100">
                @forelse($notifications as $notification)
                    @php
                        $isUnread  = is_null($notification->read_at);
                        $type      = $notification->data['type'] ?? '';
                        $hasLink   = !empty($notification->data['requisition_id']) || !empty($notification->data['order_id']);

                        $typeIcon = match($type) {
                            'requisition_submitted'       => ['bg' => 'bg-amber-100',  'color' => 'text-amber-600',  'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                            'preliminary_quotation'       => ['bg' => 'bg-blue-100',   'color' => 'text-blue-600',   'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                            'requester_confirmed'         => ['bg' => 'bg-cyan-100',   'color' => 'text-cyan-600',   'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                            'quotation_returned'          => ['bg' => 'bg-orange-100', 'color' => 'text-orange-600', 'icon' => 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6'],
                            'quotation_approval_required' => ['bg' => 'bg-purple-100', 'color' => 'text-purple-600', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                            'requisition_authorized'      => ['bg' => 'bg-green-100',  'color' => 'text-green-600',  'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                            'requisition_rejected'        => ['bg' => 'bg-red-100',    'color' => 'text-red-600',    'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'],
                            'order_created'               => ['bg' => 'bg-green-100',  'color' => 'text-green-700',  'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'],
                            default                       => ['bg' => 'bg-gray-100',   'color' => 'text-gray-500',   'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                        };
                    @endphp
                    <div wire:click="markAsRead('{{ $notification->id }}')"
                        class="px-4 py-3 transition cursor-pointer
                            {{ $isUnread ? 'bg-indigo-50/50 hover:bg-indigo-50' : 'hover:bg-gray-50 opacity-70' }}">
                        <div class="flex items-start gap-3">
                            <div class="w-7 h-7 rounded-full flex-shrink-0 flex items-center justify-center {{ $typeIcon['bg'] }}">
                                <svg class="w-3.5 h-3.5 {{ $typeIcon['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $typeIcon['icon'] }}"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-1">
                                    <p class="text-xs font-medium text-gray-900 leading-tight">{{ $notification->data['title'] }}</p>
                                    @if($isUnread)
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 flex-shrink-0 mt-1"></span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ $notification->data['message'] }}</p>
                                <div class="flex items-center justify-between mt-1">
                                    <p class="text-[10px] text-gray-400">{{ $notification->created_at->diffForHumans() }}</p>
                                    @if($hasLink)
                                        <span class="text-[10px] text-indigo-500 font-medium">Ver →</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-8 text-center text-sm text-gray-400">
                        No hay notificaciones
                    </div>
                @endforelse
            </div>
        </div>

        <div wire:click="toggle" class="fixed inset-0 z-40"></div>
    @endif
</div>
