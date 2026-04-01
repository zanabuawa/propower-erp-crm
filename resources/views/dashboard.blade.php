{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    {{-- Welcome Section --}}
    <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 rounded-2xl p-6 text-white shadow-lg">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold mb-1">¡Bienvenido, {{ auth()->user()->name }}!</h1>
                <p class="text-indigo-100 text-sm">Aquí tienes un resumen de tu negocio hoy {{ now()->format('d/m/Y') }}</p>
            </div>
            <div class="flex gap-3">
                <button class="bg-white/10 hover:bg-white/20 backdrop-blur-sm px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200">
                    <i class="fas fa-download mr-2"></i> Reporte
                </button>
                <button class="bg-white/10 hover:bg-white/20 backdrop-blur-sm px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200">
                    <i class="fas fa-calendar-alt mr-2"></i> {{ now()->format('M Y') }}
                </button>
            </div>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        {{-- Ventas del mes --}}
        <div class="group bg-white rounded-2xl border border-gray-200 p-5 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-indigo-50 rounded-xl group-hover:bg-indigo-100 transition-colors">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-2xl font-bold text-gray-800 group-hover:text-indigo-600 transition-colors">${{ number_format(0, 2) }}</span>
            </div>
            <p class="text-sm text-gray-500 font-medium">Ventas del mes</p>
            <div class="mt-2 flex items-center text-xs">
                <span class="text-green-600 bg-green-100 px-2 py-0.5 rounded-full">+0%</span>
                <span class="text-gray-400 ml-2">vs mes anterior</span>
            </div>
        </div>

        {{-- Órdenes activas --}}
        <div class="group bg-white rounded-2xl border border-gray-200 p-5 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-blue-50 rounded-xl group-hover:bg-blue-100 transition-colors">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
                <span class="text-2xl font-bold text-gray-800">0</span>
            </div>
            <p class="text-sm text-gray-500 font-medium">Órdenes activas</p>
            <div class="mt-2 flex items-center text-xs">
                <span class="text-amber-600 bg-amber-100 px-2 py-0.5 rounded-full">Pendientes: 0</span>
            </div>
        </div>

        {{-- Clientes nuevos --}}
        <div class="group bg-white rounded-2xl border border-gray-200 p-5 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-emerald-50 rounded-xl group-hover:bg-emerald-100 transition-colors">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <span class="text-2xl font-bold text-gray-800">0</span>
            </div>
            <p class="text-sm text-gray-500 font-medium">Clientes nuevos</p>
            <div class="mt-2 text-xs text-gray-400">Este mes</div>
        </div>

        {{-- Tickets abiertos --}}
        <div class="group bg-white rounded-2xl border border-gray-200 p-5 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-purple-50 rounded-xl group-hover:bg-purple-100 transition-colors">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-2xl font-bold text-gray-800">0</span>
            </div>
            <p class="text-sm text-gray-500 font-medium">Tickets abiertos</p>
            <div class="mt-2 text-xs text-gray-400">Prioridad alta: 0</div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        {{-- Ventas Gráfico --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5 hover:shadow-lg transition-shadow">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold text-gray-800">Ventas mensuales</h3>
                <select class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option>2024</option>
                    <option>2023</option>
                </select>
            </div>
            <canvas id="salesChart" height="200"></canvas>
        </div>

        {{-- Top Productos --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5 hover:shadow-lg transition-shadow">
            <h3 class="font-semibold text-gray-800 mb-4">Productos más vendidos</h3>
            <div class="space-y-4">
                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7h-4.5M20 7l-4.5-4.5M20 7l-4.5 4.5M4 7h4.5M4 7l4.5-4.5M4 7l4.5 4.5M12 3v4.5m0 0v4.5m0-4.5h4.5m-4.5 0H7.5"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-800">Sin datos aún</p>
                        <p class="text-xs text-gray-400">Agrega productos para ver estadísticas</p>
                    </div>
                    <span class="text-sm font-semibold text-gray-600">0</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Activity & Quick Actions --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Actividad Reciente --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 p-5">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold text-gray-800">Actividad reciente</h3>
                <button class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">Ver todas</button>
            </div>
            <div class="space-y-3">
                <div class="flex items-start gap-3 p-3 hover:bg-gray-50 rounded-xl transition-colors">
                    <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm text-gray-700">No hay actividad reciente</p>
                        <p class="text-xs text-gray-400 mt-1">Comienza a utilizar el sistema para ver actividad</p>
                    </div>
                    <span class="text-xs text-gray-400">--</span>
                </div>
            </div>
        </div>

        {{-- Acciones Rápidas --}}
        <div class="bg-gradient-to-br from-indigo-50 to-blue-50 rounded-2xl border border-indigo-100 p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Acciones rápidas</h3>
            <div class="space-y-2">
                <a href="#" class="flex items-center gap-3 p-3 bg-white rounded-xl hover:shadow-md transition-all group">
                    <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center group-hover:bg-indigo-200 transition-colors">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-700">Nueva venta</span>
                </a>
                <a href="#" class="flex items-center gap-3 p-3 bg-white rounded-xl hover:shadow-md transition-all group">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-200 transition-colors">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-700">Nuevo producto</span>
                </a>
                <a href="#" class="flex items-center gap-3 p-3 bg-white rounded-xl hover:shadow-md transition-all group">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-700">Nuevo cliente</span>
                </a>
                <a href="#" class="flex items-center gap-3 p-3 bg-white rounded-xl hover:shadow-md transition-all group">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-700">Ver reportes</span>
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gráfico de ventas
        const ctx = document.getElementById('salesChart')?.getContext('2d');
        if (ctx && window.Chart) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                    datasets: [{
                        label: 'Ventas',
                        data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                display: true,
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush

@endsection