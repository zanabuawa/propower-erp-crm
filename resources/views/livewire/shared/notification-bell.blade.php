<div class="relative">
    <button wire:click="toggle" class="relative p-1.5 text-gray-500 hover:text-gray-800 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        @if($count > 0)
            <span class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-red-500 text-white text-[10px] font-medium rounded-full flex items-center justify-center">
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
            <div class="max-h-80 overflow-y-auto divide-y divide-gray-100">
                @forelse($notifications as $notification)
                    <div wire:click="markAsRead('{{ $notification->id }}')"
                        class="px-4 py-3 hover:bg-gray-50 cursor-pointer transition
                            {{ $notification->read_at ? 'opacity-60' : '' }}">
                        <div class="flex items-start gap-3">
                            <div class="w-2 h-2 rounded-full mt-1.5 flex-shrink-0
                                {{ $notification->read_at ? 'bg-gray-300' : 'bg-indigo-500' }}"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-medium text-gray-900">{{ $notification->data['title'] }}</p>
                                <p class="text-xs text-gray-500 mt-0.5 truncate">{{ $notification->data['message'] }}</p>
                                <p class="text-[10px] text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
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