<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-100 text-gray-900 antialiased">

<div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: true }">

    <aside class="bg-[#1e2130] text-white flex flex-col transition-all duration-300 overflow-hidden z-20"
        :class="sidebarOpen ? 'w-56' : 'w-14'">

        @php $company = auth()->user()->company; @endphp
        <div class="flex items-center justify-center px-3 py-3 border-b border-white/10 overflow-hidden">
            <div x-show="sidebarOpen" x-transition class="w-full flex items-center justify-center">
                @if($company?->logo)
                    <img src="{{ Storage::url($company->logo) }}" alt="{{ $company->name }}"
                        class="h-9 w-auto max-w-[160px] object-contain">
                @else
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 min-w-[2rem] bg-indigo-500 rounded-lg flex items-center justify-center font-semibold text-sm text-white">
                            {{ strtoupper(substr($company?->name ?? 'E', 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-white leading-tight">{{ $company?->name ?? config('app.name') }}</p>
                            <p class="text-[10px] text-white/40 leading-tight">Sistema ERP</p>
                        </div>
                    </div>
                @endif
            </div>
            <div x-show="!sidebarOpen" x-transition class="flex items-center justify-center">
                @if($company?->icon)
                    <img src="{{ Storage::url($company->icon) }}" class="w-8 h-8 object-contain rounded-lg">
                @else
                    <div class="w-8 h-8 bg-indigo-500 rounded-lg flex items-center justify-center font-semibold text-sm text-white">
                        {{ strtoupper(substr($company?->name ?? 'E', 0, 1)) }}
                    </div>
                @endif
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto py-2">
            <x-sidebar-section label="PRINCIPAL" />
            <x-sidebar-item route="dashboard" icon="dashboard" label="Dashboard" />
            <x-sidebar-section label="ERP" />
            <x-sidebar-item route="inventory.index" icon="inventory" label="Inventario" />
            <x-sidebar-item route="purchases.index" icon="purchases" label="Compras" />
            <x-sidebar-item route="sales.index" icon="sales" label="Ventas" />
            <x-sidebar-item route="hr.index" icon="hr" label="Recursos humanos" />
            <x-sidebar-item route="production.index" icon="production" label="Producción" />
            <x-sidebar-item route="projects.index" icon="projects" label="Proyectos" />
            <x-sidebar-section label="CRM" />
            <x-sidebar-item route="contacts.index" icon="contacts" label="Clientes" />
            <x-sidebar-item route="opportunities.index" icon="opportunities" label="Oportunidades" />
            <x-sidebar-item route="tickets.index" icon="tickets" label="Tickets" />
            <x-sidebar-item route="campaigns.index" icon="campaigns" label="Campañas" />
            <x-sidebar-section label="ADMIN" />
            <x-sidebar-item route="companies.index" icon="companies" label="Empresas" />
            <x-sidebar-item route="users.index" icon="users" label="Usuarios" />
        </nav>

        <div class="flex items-center gap-3 px-4 py-3 border-t border-white/10">
            <div class="w-8 h-8 min-w-[2rem] rounded-full bg-[#2d3a5e] flex items-center justify-center text-xs font-medium text-indigo-300">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
            </div>
            <div x-show="sidebarOpen" x-transition>
                <p class="text-xs font-medium text-white/80 leading-tight">{{ auth()->user()->name }}</p>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button class="text-[10px] text-white/35 hover:text-white/70 transition">Cerrar sesión</button>
                </form>
            </div>
        </div>
    </aside>

    <div class="flex flex-col flex-1 overflow-hidden">
        <header class="bg-white border-b border-gray-200 h-12 flex items-center px-5 gap-3 flex-shrink-0">
            <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 hover:text-gray-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <span class="font-medium text-gray-800 flex-1">{{ $title ?? 'Dashboard' }}</span>
            @if(auth()->user()->branch)
                <span class="text-xs bg-gray-100 border border-gray-200 rounded-full px-3 py-1 text-gray-500">
                    {{ auth()->user()->branch->name }}
                </span>
            @endif
        </header>

        <main class="flex-1 overflow-y-auto p-6">
            {{ $slot }}
        </main>
    </div>
</div>

@livewireScripts
</body>
</html>