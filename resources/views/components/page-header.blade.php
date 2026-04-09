@props(['title', 'description' => null])

<div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-semibold text-gray-900">{{ $title }}</h1>
        @if($description)
            <p class="text-sm text-gray-500 mt-0.5">{{ $description }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex items-center gap-2 flex-wrap shrink-0">
            {{ $actions }}
        </div>
    @endisset
</div>
