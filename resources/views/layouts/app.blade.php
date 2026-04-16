@php $company = auth()->user()->company; @endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    @if($company?->icon)
        <link rel="icon" href="{{ Storage::url($company->icon) }}">
    @elseif($company?->logo)
        <link rel="icon" href="{{ Storage::url($company->logo) }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        /* ── Sidebar scrollbar ───────────────────────────────────────── */
        .sb-nav::-webkit-scrollbar { width: 3px; }
        .sb-nav::-webkit-scrollbar-track { background: transparent; }
        .sb-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.08); border-radius: 4px; }
        .sb-nav::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.16); }
        .sb-nav { scrollbar-width: thin; scrollbar-color: rgba(255,255,255,0.08) transparent; }

        /* ── Accordion ───────────────────────────────────────────────── */
        .sb-sub { overflow: hidden; max-height: 0; }
        .sb-sub.open { max-height: 600px; }
        .sb-animated .sb-sub { transition: max-height 0.22s ease; }

        /* ── Chevron ─────────────────────────────────────────────────── */
        .sb-chevron { transition: transform 0.18s ease; }
        .sb-chevron.open { transform: rotate(90deg); }

        /* ── Skip link ───────────────────────────────────────────────── */
        .skip-link {
            position: absolute; left: -9999px; z-index: 999;
            padding: 0.4rem 1rem; background: #6366f1; color: #fff;
            font-size: 0.8125rem; font-weight: 600;
            border-radius: 0 0 0.5rem 0.5rem;
        }
        .skip-link:focus { left: 50%; transform: translateX(-50%); }

        /* ── Focus ring ──────────────────────────────────────────────── */
        :focus-visible {
            outline: 2px solid rgba(99,102,241,0.75);
            outline-offset: 2px;
            border-radius: 5px;
        }

        /* ── Sidebar active left-border indicator ────────────────────── */
        .sb-item-active::before {
            content: '';
            position: absolute;
            left: 0; top: 50%;
            transform: translateY(-50%);
            width: 3px; height: 60%;
            background: #6366f1;
            border-radius: 0 3px 3px 0;
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 antialiased">

<a href="#main-content" class="skip-link">Ir al contenido principal</a>

<div class="flex h-screen overflow-hidden" x-data="{
    sidebarOpen: true,
    mobileOpen: false,
    openMenus: [],
    saveTimeout: null,

    init() {
        this.loadState();
        this.$nextTick(() => {
            this.$el.querySelector('aside')?.classList.add('sb-animated');
        });
        document.addEventListener('livewire:navigating', () => { this.saveState(); });
        window.addEventListener('beforeunload', () => { this.saveState(); });
    },

    loadState() {
        const s = localStorage.getItem('sidebarOpen');
        if (s !== null) this.sidebarOpen = JSON.parse(s);
        const m = localStorage.getItem('sidebarOpenMenus');
        if (m) this.openMenus = JSON.parse(m);
    },

    saveState() {
        clearTimeout(this.saveTimeout);
        this.saveTimeout = setTimeout(() => {
            localStorage.setItem('sidebarOpen', JSON.stringify(this.sidebarOpen));
            localStorage.setItem('sidebarOpenMenus', JSON.stringify(this.openMenus));
        }, 100);
    },

    toggleMenu(id) {
        this.openMenus = this.openMenus.includes(id)
            ? this.openMenus.filter(m => m !== id)
            : [...this.openMenus, id];
        this.saveState();
    },

    isOpen(id) { return this.openMenus.includes(id); }
}"
x-init="init()">

    {{-- ── MOBILE OVERLAY ────────────────────────────────────────────── --}}
    <div
        x-show="mobileOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="mobileOpen = false"
        class="fixed inset-0 bg-black/50 backdrop-blur-[2px] z-30 lg:hidden"
        style="display:none"
    ></div>

    {{-- ── SIDEBAR ────────────────────────────────────────────────────── --}}
    <aside
        aria-label="Navegación principal"
        class="flex flex-col z-40 flex-shrink-0
               fixed inset-y-0 left-0 lg:relative lg:translate-x-0
               transition-[width] duration-300 ease-in-out
               border-r border-white/[0.04]"
        style="background: #0f1117;"
        :class="{
            'w-60': sidebarOpen,
            'w-[3.75rem]': !sidebarOpen && !mobileOpen,
            'translate-x-0 w-60': mobileOpen,
            '-translate-x-full lg:translate-x-0': !mobileOpen
        }"
    >
        {{-- ── LOGO ──────────────────────────────────────────────────── --}}
        <div class="flex-shrink-0 border-b border-white/[0.06]">

            {{-- Expanded: full logo centered --}}
            <div x-show="sidebarOpen"
                 x-transition:enter="transition-opacity ease-out duration-200 delay-100"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="flex items-center justify-center py-4 px-4">
                @if($company?->logo)
                    <img src="{{ Storage::url($company->logo) }}"
                         alt="{{ $company->name }}"
                         class="h-14 w-auto max-w-full object-contain">
                @else
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-9 h-9 min-w-[2.25rem] rounded-lg bg-indigo-500 flex items-center justify-center
                                    text-white font-bold text-sm shadow-lg shadow-indigo-500/30">
                            {{ strtoupper(substr($company?->name ?? 'E', 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-[13px] font-semibold text-white/90 leading-tight truncate">
                                {{ $company?->name ?? config('app.name') }}
                            </p>
                            <p class="text-[10px] text-white/30 leading-tight tracking-wide">
                                Sistema ERP
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Collapsed: icon only, strictly centered --}}
            <div x-show="!sidebarOpen"
                 x-transition:enter="transition-opacity ease-out duration-150 delay-150"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-in duration-75"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="flex items-center justify-center py-3.5">
                @if($company?->icon)
                    <img src="{{ Storage::url($company->icon) }}"
                         class="w-8 h-8 rounded-lg object-contain">
                @elseif($company?->logo)
                    <img src="{{ Storage::url($company->logo) }}"
                         class="w-8 h-8 rounded-lg object-contain">
                @else
                    <div class="w-8 h-8 rounded-lg bg-indigo-500 flex items-center justify-center
                                text-white font-bold text-sm shadow-lg shadow-indigo-500/30">
                        {{ strtoupper(substr($company?->name ?? 'E', 0, 1)) }}
                    </div>
                @endif
            </div>

        </div>

        {{-- ── NAVIGATION ─────────────────────────────────────────────── --}}
        <nav class="sb-nav flex-1 overflow-y-auto overflow-x-hidden py-3 space-y-0.5 px-2">

            {{-- ·· PRINCIPAL ──────────────────────────────────── --}}
            <div class="mb-1">
                <div class="transition-all duration-200 overflow-hidden"
                     :class="sidebarOpen ? 'max-h-8 opacity-100 px-2 pb-1' : 'max-h-0 opacity-0'">
                    <span class="text-[10px] font-semibold uppercase tracking-widest text-white/25 select-none">
                        Principal
                    </span>
                </div>

                {{-- Dashboard --}}
                <a href="{{ route('dashboard') }}" wire:navigate
                   class="relative flex items-center gap-3 px-2.5 py-2 text-sm rounded-lg transition-colors duration-150 group cursor-pointer
                          {{ request()->routeIs('dashboard')
                             ? 'bg-indigo-500/10 text-indigo-300 sb-item-active'
                             : 'text-white/65 hover:bg-white/[0.04] hover:text-white/90' }}"
                >
                    <span class="w-5 h-5 min-w-[1.25rem] flex items-center justify-center flex-shrink-0">
                        @include('components.icons.dashboard')
                    </span>
                    <span class="flex-1 truncate font-medium transition-all duration-200"
                          :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0 overflow-hidden'">
                        Dashboard
                    </span>
                    {{-- Tooltip (collapsed) --}}
                    <span x-show="!sidebarOpen"
                          class="pointer-events-none absolute left-[3.8rem] z-50 whitespace-nowrap
                                 rounded-lg bg-slate-800 border border-white/10 shadow-xl
                                 px-2.5 py-1.5 text-xs font-medium text-white/90
                                 opacity-0 group-hover:opacity-100 transition-opacity duration-150">
                        Dashboard
                    </span>
                </a>
            </div>

            {{-- ·· OPERACIONES ────────────────────────────────── --}}
            @canany(['view inventory', 'create inventory', 'view purchases', 'create purchases', 'view sales', 'create sales'])
            <div class="pt-3 mb-1">
                <div class="transition-all duration-200 overflow-hidden"
                     :class="sidebarOpen ? 'max-h-8 opacity-100 px-2 pb-1' : 'max-h-0 opacity-0'">
                    <span class="text-[10px] font-semibold uppercase tracking-widest text-white/25 select-none">
                        Operaciones
                    </span>
                </div>

                @canany(['view inventory', 'create inventory', 'edit inventory', 'delete inventory'])
                <x-sidebar-menu id="inv" label="Inventario" icon="inventory" :routes="['inventory.*']">
                    @can('view inventory')
                    <x-sidebar-subitem route="inventory.index" label="Productos y servicios" />
                    <x-sidebar-subitem route="inventory.general" label="Existencias generales" />
                    <x-sidebar-subitem route="inventory.warehouse-stock" label="Existencias por almacén" />
                    <x-sidebar-subitem route="inventory.categories.index" label="Categorías" />
                    <x-sidebar-subitem route="inventory.warehouses.index" label="Almacenes" />
                    <x-sidebar-subitem route="inventory.movements.index" label="Movimientos" />
                    <x-sidebar-subitem route="inventory.transfers.index" label="Transferencias" />
                    <x-sidebar-subitem route="inventory.lots.index" label="Lotes PEPS" />
                    <x-sidebar-subitem route="inventory.kardex" label="Kardex PEPS" />
                    @endcan
                </x-sidebar-menu>
                @endcanany

                @canany(['view purchases', 'create purchases', 'edit purchases', 'delete purchases'])
                <x-sidebar-menu id="buy" label="Compras" icon="purchases" :routes="['purchases.*']">
                    @can('view purchases')
                    <x-sidebar-subitem route="purchases.index" label="Requisiciones" />
                    <x-sidebar-subitem route="purchases.orders.index" label="Órdenes de compra" />
                    <x-sidebar-subitem route="purchases.goods-receipts.index" label="Recepción de mercancías" />
                    <x-sidebar-subitem route="purchases.report" label="Reporte de compras" />
                    @endcan
                </x-sidebar-menu>
                @endcanany

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
            </div>
            @endcanany

            {{-- ·· CRM ────────────────────────────────────────── --}}
            @canany(['view contacts', 'create contacts', 'view suppliers', 'create suppliers'])
            <div class="pt-3 mb-1">
                <div class="transition-all duration-200 overflow-hidden"
                     :class="sidebarOpen ? 'max-h-8 opacity-100 px-2 pb-1' : 'max-h-0 opacity-0'">
                    <span class="text-[10px] font-semibold uppercase tracking-widest text-white/25 select-none">
                        CRM
                    </span>
                </div>

                <x-sidebar-menu id="crm" label="CRM" icon="contacts"
                    :routes="['contacts.*','suppliers.*','opportunities.*','tickets.*','campaigns.*']">
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
            </div>
            @endcanany

            {{-- ·· PROYECTOS ──────────────────────────────────── --}}
            @canany(['view projects', 'create projects'])
            <div class="pt-3 mb-1">
                <div class="transition-all duration-200 overflow-hidden"
                     :class="sidebarOpen ? 'max-h-8 opacity-100 px-2 pb-1' : 'max-h-0 opacity-0'">
                    <span class="text-[10px] font-semibold uppercase tracking-widest text-white/25 select-none">
                        Proyectos
                    </span>
                </div>

                @canany(['view projects', 'create projects', 'edit projects', 'delete projects'])
                <x-sidebar-menu id="proj" label="Proyectos" icon="projects" :routes="['projects.*']">
                    <x-sidebar-subitem route="projects.index" label="Mis proyectos" />
                </x-sidebar-menu>
                @endcanany

                @canany(['view projects', 'create projects'])
                <x-sidebar-menu id="lic" label="Licitaciones e ingeniería" icon="licitaciones" :routes="['licitaciones.*']">
                    <x-sidebar-subitem route="projects.index" label="Licitaciones" />
                </x-sidebar-menu>
                @endcanany
            </div>
            @endcanany

            {{-- ·· FINANZAS ───────────────────────────────────── --}}
            @canany(['view hr', 'create hr', 'view accounting', 'create accounting', 'view finance', 'create finance', 'view assets', 'create assets'])
            <div class="pt-3 mb-1">
                <div class="transition-all duration-200 overflow-hidden"
                     :class="sidebarOpen ? 'max-h-8 opacity-100 px-2 pb-1' : 'max-h-0 opacity-0'">
                    <span class="text-[10px] font-semibold uppercase tracking-widest text-white/25 select-none">
                        Finanzas y RRHH
                    </span>
                </div>

                @canany(['view hr', 'create hr', 'edit hr', 'delete hr'])
                <x-sidebar-menu id="hr" label="Recursos humanos" icon="hr"
                    :routes="['hr.employees.*','hr.departments.*','hr.positions.*','hr.contracts.*','hr.attendances.*','hr.payrolls.*','hr.leaves.*','hr.incidents.*','hr.evaluations.*']">
                    @can('view hr')
                    <x-sidebar-subitem route="hr.employees.index" label="Empleados" />
                    <x-sidebar-subitem route="hr.departments.index" label="Departamentos" />
                    <x-sidebar-subitem route="hr.positions.index" label="Puestos laborales" />
                    <x-sidebar-subitem route="hr.contracts.index" label="Contratos" />
                    <x-sidebar-subitem route="hr.attendances.index" label="Asistencias" />
                    <x-sidebar-subitem route="hr.payrolls.index" label="Nóminas" />
                    <x-sidebar-subitem route="hr.leaves.index" label="Permisos y bajas" />
                    <x-sidebar-subitem route="hr.incidents.index" label="Incidencias" />
                    <x-sidebar-subitem route="hr.evaluations.index" label="Evaluaciones" />
                    @endcan
                </x-sidebar-menu>
                @endcanany

                @canany(['view accounting', 'create accounting', 'edit accounting', 'delete accounting'])
                <x-sidebar-menu id="acc" label="Contabilidad" icon="accounting" :routes="['accounting.*']">
                    <x-sidebar-subitem route="accounting.index" label="Cuentas" />
                </x-sidebar-menu>
                @endcanany

                @canany(['view finance', 'create finance', 'edit finance', 'delete finance'])
                <x-sidebar-menu id="fin" label="Finanzas" icon="finance" :routes="['finance.*']">
                    @can('view finance')
                    <x-sidebar-subitem route="finance.accounts.index" label="Cuentas" />
                    <x-sidebar-subitem route="finance.transactions.index" label="Transacciones" />
                    <x-sidebar-subitem route="finance.budgets.index" label="Presupuestos" />
                    <x-sidebar-subitem route="finance.cashflow.index" label="Flujo de caja" />
                    @endcan
                </x-sidebar-menu>
                @endcanany

                @canany(['view assets', 'create assets', 'transfer assets'])
                <x-sidebar-menu id="assets" label="Activos fijos" icon="assets" :routes="['assets.*']">
                    @can('view assets')
                    <x-sidebar-subitem route="assets.index" label="Equipo y bienes" />
                    <x-sidebar-subitem route="assets.inventory" label="Inventario de activos" />
                    <x-sidebar-subitem route="assets.transfers.index" label="Transferencias" />
                    @endcan
                </x-sidebar-menu>
                @endcanany
            </div>
            @endcanany

            {{-- ·· ADMINISTRACIÓN ─────────────────────────────── --}}
            @canany(['view companies', 'view branches', 'view users'])
            <div class="pt-3 mb-1">
                <div class="transition-all duration-200 overflow-hidden"
                     :class="sidebarOpen ? 'max-h-8 opacity-100 px-2 pb-1' : 'max-h-0 opacity-0'">
                    <span class="text-[10px] font-semibold uppercase tracking-widest text-white/25 select-none">
                        Administración
                    </span>
                </div>

                <x-sidebar-menu id="adm" label="Administración" icon="companies"
                    :routes="['companies.*','branches.*','users.*']">
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
            </div>
            @endcanany

        </nav>

        {{-- ── USER FOOTER ────────────────────────────────────────────── --}}
        <div class="flex-shrink-0 border-t border-white/[0.06] p-2">
            <div class="relative flex items-center gap-3 rounded-lg px-2 py-2 group
                        hover:bg-white/[0.04] transition-colors duration-150 cursor-default overflow-hidden">
                {{-- Avatar --}}
                <div class="w-8 h-8 min-w-[2rem] rounded-full flex items-center justify-center
                            text-xs font-semibold text-indigo-300 flex-shrink-0
                            ring-1 ring-indigo-500/30"
                     style="background: rgba(99,102,241,0.15)">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                {{-- User info --}}
                <div class="min-w-0 flex-1 transition-all duration-200"
                     :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0 overflow-hidden'">
                    <p class="text-[12px] font-medium text-white/80 leading-tight truncate">
                        {{ auth()->user()->name }}
                    </p>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="text-[11px] text-white/35 hover:text-indigo-400 transition-colors duration-150 cursor-pointer">
                            Cerrar sesión
                        </button>
                    </form>
                </div>
                {{-- Tooltip (collapsed) --}}
                <span x-show="!sidebarOpen"
                      class="pointer-events-none absolute left-[3.8rem] z-50 whitespace-nowrap
                             rounded-lg bg-slate-800 border border-white/10 shadow-xl
                             px-2.5 py-1.5 text-xs font-medium text-white/90
                             opacity-0 group-hover:opacity-100 transition-opacity duration-150">
                    {{ auth()->user()->name }}
                </span>
            </div>
        </div>
    </aside>

    {{-- ── MAIN CONTENT ────────────────────────────────────────────────── --}}
    <div class="flex flex-col flex-1 overflow-hidden min-w-0">

        {{-- ── TOPBAR ──────────────────────────────────────────────────── --}}
        <header class="bg-white border-b border-slate-200/80 h-12 flex items-center px-4 gap-3 flex-shrink-0 shadow-sm">

            {{-- Toggle desktop --}}
            <button @click="sidebarOpen = !sidebarOpen; saveState()"
                    class="hidden lg:flex items-center justify-center w-8 h-8 rounded-lg
                           text-slate-400 hover:text-slate-700 hover:bg-slate-100
                           transition-colors duration-150 cursor-pointer"
                    :aria-label="sidebarOpen ? 'Contraer menú' : 'Expandir menú'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- Toggle mobile --}}
            <button @click="mobileOpen = !mobileOpen"
                    class="flex lg:hidden items-center justify-center w-8 h-8 rounded-lg
                           text-slate-400 hover:text-slate-700 hover:bg-slate-100
                           transition-colors duration-150 cursor-pointer"
                    :aria-label="mobileOpen ? 'Cerrar menú' : 'Abrir menú'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- Divider --}}
            <div class="hidden lg:block h-5 w-px bg-slate-200 flex-shrink-0"></div>

            {{-- Module title --}}
            <span class="text-[13px] font-semibold text-slate-700 flex-1 truncate">
                @php
                    $moduleTitle = match(true) {
                        request()->routeIs('inventory.*')    => 'Inventario',
                        request()->routeIs('purchases.*')    => 'Compras',
                        request()->routeIs('sales.*')        => 'Ventas',
                        request()->routeIs('contacts.*', 'suppliers.*', 'opportunities.*',
                                           'tickets.*', 'campaigns.*') => 'CRM',
                        request()->routeIs('hr.*')           => 'Recursos humanos',
                        request()->routeIs('accounting.*')   => 'Contabilidad',
                        request()->routeIs('projects.*', 'licitaciones.*') => 'Proyectos',
                        request()->routeIs('finance.*')      => 'Finanzas',
                        request()->routeIs('assets.*')       => 'Activos fijos',
                        request()->routeIs('companies.*', 'branches.*', 'users.*') => 'Administración',
                        default => $title ?? 'Dashboard',
                    };
                @endphp
                {{ $moduleTitle }}
            </span>

            @livewire('shared.notification-bell')

            @if(auth()->user()->branch)
                <span class="hidden sm:inline-flex items-center gap-1.5 text-[11px] font-medium
                             bg-slate-100 border border-slate-200 rounded-full px-3 py-1 text-slate-500">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 flex-shrink-0"></span>
                    {{ auth()->user()->branch->name }}
                </span>
            @endif
        </header>

        {{-- ── MAIN ────────────────────────────────────────────────────── --}}
        <main id="main-content" class="flex-1 overflow-y-auto p-4 lg:p-6" tabindex="-1">
            @hasSection('content')
                @yield('content')
            @else
                {{ $slot }}
            @endif
        </main>
    </div>

</div>

@livewireScripts
@stack('scripts')
</body>
</html>
