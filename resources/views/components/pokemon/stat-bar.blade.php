@props(['label', 'value'])

@php
    $percentage = min(100, round(((int) $value / 255) * 100));
@endphp

<div>
    <div class="flex items-center justify-between gap-4 text-sm">
        <span class="font-medium text-zinc-700">{{ $label }}</span>
        <span class="tabular-nums font-semibold text-zinc-950">{{ $value }}</span>
    </div>
    <div class="mt-2 h-2 overflow-hidden rounded-full bg-zinc-100">
        <div class="h-full rounded-full bg-red-500" style="width: {{ $percentage }}%"></div>
    </div>
</div>
