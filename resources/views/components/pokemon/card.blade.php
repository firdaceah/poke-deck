@props(['item'])

<a
    href="{{ route('pokemon.show', $item['name']) }}"
    wire:key="pokemon-{{ $item['name'] }}"
    class="group flex min-h-72 flex-col rounded-lg border border-zinc-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-red-200 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-red-100"
>
    <div class="flex items-center justify-between gap-3 text-xs font-semibold text-zinc-400">
        <span class="shrink-0">#{{ str_pad((string) $item['id'], 4, '0', STR_PAD_LEFT) }}</span>
        <span class="min-w-0 truncate">{{ str($item['name'])->upper() }}</span>
    </div>

    <div class="mt-4 flex h-32 items-center justify-center">
        @if ($item['image'])
            <img
                src="{{ $item['image'] }}"
                alt="{{ $item['display_name'] }}"
                class="h-32 w-32 object-contain transition group-hover:scale-105"
                loading="lazy"
            >
        @else
            <div class="flex h-32 w-32 items-center justify-center rounded-full bg-zinc-100 text-sm text-zinc-400">
                No image
            </div>
        @endif
    </div>

    <h2 class="mt-4 min-w-0 break-words text-lg font-bold leading-6 text-zinc-950">{{ $item['display_name'] }}</h2>

    <div class="mt-3 flex flex-wrap gap-2">
        @foreach ($item['types'] as $type)
            <x-pokemon.type-badge :type="$type" />
        @endforeach
    </div>
</a>
