@props(['status', 'config' => [], 'label' => null])

@php
    $defaultConfig = [
        'draft' => 'bg-gray-100 text-gray-700 border-gray-200',
        'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
        'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'rejected' => 'bg-red-50 text-red-700 border-red-200',
        'cancelled' => 'bg-red-50 text-red-700 border-red-200',
        'sent' => 'bg-blue-50 text-blue-700 border-blue-200',
        'completed' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
    ];

    $config = array_merge($defaultConfig, $config);
    $classes = $config[$status] ?? 'bg-gray-50 text-gray-500 border-gray-100';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {$classes}"]) }}>
    {{ $label ?? $slot }}
</span>
