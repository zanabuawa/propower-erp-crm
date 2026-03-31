<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        .sb-nav::-webkit-scrollbar { width: 4px; }
        .sb-nav::-webkit-scrollbar-track { background: transparent; }
        .sb-nav::-webkit-scrollbar-thumb { background: #37445e; border-radius: 4px; }
        .sb-nav::-webkit-scrollbar-thumb:hover { background: #0a0d1a; }
        .sb-nav { scrollbar-width: thin; scrollbar-color: #0f1120 transparent; }
        /* Sin transición al cargar — se activa después del primer render */
        .sb-sub { overflow: hidden; max-height: 0; }
        .sb-sub.open { max-height: 400px; }
        .sb-animated .sb-sub { transition: max-height .25s ease; }
        .sb-chevron { transition: transform .2s; }
        .sb-chevron.open { transform: rotate(90deg); }
    </style>
</head>
<body class="bg-gray-100 text-gray-900 antialiased">

<div class="flex h-screen overflow-hidden" x-data="{
    sidebarOpen: true,
    mobileOpen: false,
    openMenus: [],
    saveTimeout: null,

    init() {
        this.loadState();

        // Activar transiciones solo después del primer render
        // para evitar que los menús animen al cargar la página
        this.$nextTick(() => {
            this.$el.querySelector('aside').classList.add('sb-animated');
        });

        document.addEventListener('livewire:navigating', () => {
            this.saveState();
        });

        window.addEventListener('beforeunload', () => {
            this.saveState();
        });
    },

    loadState() {
        const savedSidebarOpen = localStorage.getItem('sidebarOpen');
        if (savedSidebarOpen !== null) {
            this.sidebarOpen = JSON.parse(savedSidebarOpen);
        }

        const savedMenus = localStorage.getItem('sidebarOpenMenus');
        if (savedMenus) {
            this.openMenus = JSON.parse(savedMenus);
        }
    },

    saveState() {
        clearTimeout(this.saveTimeout);
        this.saveTimeout = setTimeout(() => {
            localStorage.setItem('sidebarOpen', JSON.stringify(this.sidebarOpen));
            localStorage.setItem('sidebarOpenMenus', JSON.stringify(this.openMenus));
        }, 100);
    },

    toggleMenu(id) {
        if (this.openMenus.includes(id)) {
            this.openMenus = this.openMenus.filter(m => m !== id);
        } else {
            this.openMenus.push(id);
        }
        this.saveState();
    },

    isOpen(id) {
        return this.openMenus.includes(id);
    }
}"
x-init="init()">

    {{-- OVERLAY MOBILE --}}
    <div
        x-show="mobileOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="mobileOpen = false"
        class="fixed inset-0 bg-black/40 z-30 lg:hidden"
        style="display:none"
    ></div>

    {{-- SIDEBAR --}}
    <aside
        class="bg-[#1a1d2e] text-white flex flex-col z-40 flex-shrink-0
               fixed inset-y-0 left-0 lg:relative lg:translate-x-0
               transition-all duration-300"
        :class="{
            'w-56': sidebarOpen,
            'w-14': !sidebarOpen && !mobileOpen,
            'translate-x-0': mobileOpen,
            '-translate-x-full lg:translate-x-0': !mobileOpen
        }"
    >
        {{-- LOGO --}}
        @php $company = auth()->user()->company; @endphp
        <div class="overflow-hidden flex-shrink-0">
            <div class="flex items-center justify-center p-4"
                :style="sidebarOpen ? 'padding:16px' : 'padding:8px'">
                <div class="w-full overflow-hidden transition-all duration-300 ease-in-out"
                    style="max-height:120px"
                    :style="sidebarOpen ? 'max-height:120px;opacity:1' : 'max-height:0;opacity:0;padding:0'">
                    @if($company?->logo)
                        <img src="{{ Storage::url($company->logo) }}" alt="{{ $company->name }}"
                            class="w-full h-auto max-h-full object-contain">
                    @else
                        <div class="flex items-center gap-2">
                            <div class="w-10 h-10 min-w-[2.5rem] bg-indigo-500 rounded-lg flex items-center justify-center font-semibold text-sm">
                                {{ strtoupper(substr($company?->name ?? 'E', 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium leading-tight">{{ $company?->name ?? config('app.name') }}</p>
                                <p class="text-[10px] text-white/35 leading-tight">Sistema ERP</p>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="overflow-hidden transition-all duration-300 ease-in-out"
                    :style="!sidebarOpen ? 'max-width:100%;max-height:80px;opacity:1' : 'max-width:0;max-height:0;opacity:0'">
                    @if($company?->icon)
                        <img src="{{ Storage::url($company->icon) }}" class="w-full h-auto object-contain rounded-lg">
                    @elseif($company?->logo)
                        <img src="{{ Storage::url($company->logo) }}" class="w-full h-auto object-contain rounded-lg">
                    @else
                        <div class="w-8 h-8 bg-indigo-500 rounded-lg flex items-center justify-center font-semibold text-sm">
                            {{ strtoupper(substr($company?->name ?? 'E', 0, 1)) }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- NAV --}}
        <nav class="sb-nav flex-1 overflow-y-auto py-2">

            {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}" wire:navigate
                class="flex items-center gap-3 px-3 py-2 text-sm transition-colors duration-150 relative group
                    {{ request()->routeIs('dashboard') ? 'bg-indigo-500/20 text-indigo-300' : 'text-white/55 hover:bg-white/6 hover:text-white' }}">
                <span class="w-5 h-5 min-w-[1.25rem] flex items-center justify-center">
                    @include('components.icons.dashboard')
                </span>
                <span class="flex-1 truncate transition-all duration-200" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0'">Dashboard</span>
                <span x-show="!sidebarOpen"
                    class="absolute left-14 bg-[#1a1d2e] border border-white/10 text-white text-xs px-2 py-1 rounded-md whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-50">
                    Dashboard
                </span>
            </a>

            {{-- Inventario --}}
            @canany(['view inventory', 'create inventory', 'edit inventory', 'delete inventory'])
            <x-sidebar-menu id="inv" label="Inventario" icon="inventory" :routes="['inventory.*']">
                @can('view inventory')
                <x-sidebar-subitem route="inventory.index" label="Productos" />
                <x-sidebar-subitem route="inventory.general" label="Existencias generales" />
                <x-sidebar-subitem route="inventory.warehouse-stock" label="Existencias por almacén" />
                <x-sidebar-subitem route="inventory.categories.index" label="Categorías" />
                <x-sidebar-subitem route="inventory.units.index" label="Unidades" />
                <x-sidebar-subitem route="inventory.warehouses.index" label="Almacenes" />
                <x-sidebar-subitem route="inventory.movements.index" label="Movimientos" />
                @endcan
            </x-sidebar-menu>
            @endcanany

            {{-- Compras --}}
            @canany(['view purchases', 'create purchases', 'edit purchases', 'delete purchases'])
            <x-sidebar-menu id="buy" label="Compras" icon="purchases" :routes="['purchases.*']">
                @can('view purchases')
                <x-sidebar-subitem route="purchases.index" label="Requisiciones" />
                <x-sidebar-subitem route="purchases.orders.index" label="Órdenes de compra" />
                <x-sidebar-subitem route="purchases.goods-receipts.index" label="Recepción de mercancías" />
                @endcan
            </x-sidebar-menu>
            @endcanany

            {{-- Ventas --}}
            @canany(['view sales', 'create sales', 'edit sales', 'delete sales'])
            <x-sidebar-menu id="sales" label="Ventas" icon="sales" :routes="['sales.*']">
                @can('view sales')
                <x-sidebar-subitem route="sales.index" label="Cotizaciones" />
                <x-sidebar-subitem route="sales.orders.index" label="Órdenes de venta" />
                <x-sidebar-subitem route="sales.invoices.index" label="Facturas" />
                <x-sidebar-subitem route="sales.price-lists.index" label="Listas de precios" />
                @endcan
            </x-sidebar-menu>
            @endcanany

            {{-- CRM --}}
            @canany(['view contacts', 'create contacts', 'view suppliers', 'create suppliers'])
            <x-sidebar-menu id="crm" label="CRM" icon="contacts" :routes="['contacts.*','suppliers.*','opportunities.*','tickets.*','campaigns.*']">
                @can('view contacts')
                <x-sidebar-subitem route="contacts.index" label="Clientes" />
                @endcan
                @can('view suppliers')
                <x-sidebar-subitem route="suppliers.index" label="Proveedores" />
                @endcan
                <x-sidebar-subitem route="opportunities.index" label="Oportunidades" />
                <x-sidebar-subitem route="tickets.index" label="Tickets" />
                <x-sidebar-subitem route="campaigns.index" label="Campañas" />
            </x-sidebar-menu>
            @endcanany

            {{-- Recursos Humanos --}}
            @canany(['view hr', 'create hr', 'edit hr', 'delete hr'])
            <x-sidebar-menu id="hr" label="Recursos humanos" icon="hr" :routes="['hr.*']">
                <x-sidebar-subitem route="hr.index" label="Empleados" />
            </x-sidebar-menu>
            @endcanany

            {{-- Contabilidad --}}
            @canany(['view accounting', 'create accounting', 'edit accounting', 'delete accounting'])
            <x-sidebar-menu id="acc" label="Contabilidad" icon="accounting" :routes="['accounting.*']">
                <x-sidebar-subitem route="accounting.index" label="Cuentas" />
            </x-sidebar-menu>
            @endcanany

            {{-- Proyectos --}}
            @canany(['view projects', 'create projects', 'edit projects', 'delete projects'])
            <x-sidebar-menu id="proj" label="Proyectos" icon="projects" :routes="['projects.*']">
                <x-sidebar-subitem route="projects.index" label="Mis proyectos" />
            </x-sidebar-menu>
            @endcanany

            {{-- Licitaciones --}}
            @canany(['view projects', 'create projects'])
            <x-sidebar-menu id="lic" label="Licitaciones e ingeniería" icon="inventory" :routes="['licitaciones.*']">
                <x-sidebar-subitem route="projects.index" label="Licitaciones" />
            </x-sidebar-menu>
            @endcanany

            {{-- Administración --}}
            @canany(['view companies', 'view branches', 'view users'])
            <x-sidebar-menu id="adm" label="Administración" icon="companies" :routes="['companies.*','branches.*','users.*']">
                @can('view companies')
                <x-sidebar-subitem route="companies.index" label="Empresas" />
                @endcan
                @can('view branches')
                <x-sidebar-subitem route="branches.index" label="Sucursales" />
                @endcan
                @can('view users')
                <x-sidebar-subitem route="users.index" label="Usuarios" />
                @endcan
            </x-sidebar-menu>
            @endcanany

        </nav>

        {{-- FOOTER --}}
        <div class="flex items-center gap-3 px-3 py-3 border-t border-white/7 flex-shrink-0 overflow-hidden">
            <div class="w-8 h-8 min-w-[2rem] rounded-full bg-[#2d3a5e] flex items-center justify-center text-xs font-medium text-indigo-300">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
            </div>
            <div class="overflow-hidden transition-all duration-200" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0'">
                <p class="text-xs font-medium text-white/80 leading-tight truncate">{{ auth()->user()->name }}</p>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button class="text-[10px] text-white/35 hover:text-white/70 transition">Cerrar sesión</button>
                </form>
            </div>
        </div>
    </aside>

    {{-- CONTENIDO PRINCIPAL --}}
    <div class="flex flex-col flex-1 overflow-hidden min-w-0">

        {{-- TOPBAR --}}
        <header class="bg-white border-b border-gray-200 h-12 flex items-center px-4 gap-3 flex-shrink-0">
            {{-- Toggle desktop --}}
            <button @click="sidebarOpen = !sidebarOpen; saveState()" class="hidden lg:flex text-gray-500 hover:text-gray-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            {{-- Toggle mobile --}}
            <button @click="mobileOpen = !mobileOpen" class="flex lg:hidden text-gray-500 hover:text-gray-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <span class="font-medium text-gray-800 flex-1 truncate">
                {{ $title ?? 'Dashboard' }}
            </span>
            @livewire('shared.notification-bell')
            @if(auth()->user()->branch)
                <span class="hidden sm:inline text-xs bg-gray-100 border border-gray-200 rounded-full px-3 py-1 text-gray-500">
                    {{ auth()->user()->branch->name }}
                </span>
            @endif
        </header>

        {{-- MAIN --}}
        <main class="flex-1 overflow-y-auto p-4 lg:p-6">
            @hasSection('content')
                @yield('content')
            @else
                {{ $slot }}
            @endif
        </main>
    </div>
</div>

@livewireScripts
</body>
</html>