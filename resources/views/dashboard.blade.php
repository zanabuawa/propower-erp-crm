@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500 mb-1">Ventas del mes</p>
        <p class="text-2xl font-medium">$0</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500 mb-1">Órdenes activas</p>
        <p class="text-2xl font-medium">0</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500 mb-1">Clientes nuevos</p>
        <p class="text-2xl font-medium">0</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500 mb-1">Tickets abiertos</p>
        <p class="text-2xl font-medium">0</p>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-5">
    <p class="text-sm text-gray-500">El sistema está listo. Ve construyendo los módulos.</p>
</div>
@endsection