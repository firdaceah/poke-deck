<main class="min-h-screen bg-zinc-50 text-zinc-950">
    <section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <header class="flex flex-col gap-4 border-b border-zinc-200 pb-6 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-red-600">PokeAPI Explorer</p>
                <h1 class="mt-2 text-3xl font-bold text-zinc-950 md:text-4xl">Pokemon Explorer</h1>
                <p class="mt-2 max-w-2xl text-sm text-zinc-600">
                    Jelajahi Pokemon, type, artwork, dan statistik dasar langsung dari PokeAPI.
                </p>
            </div>

            <label class="w-full md:max-w-sm">
                <span class="sr-only">Cari Pokemon</span>
                <input
                    wire:model.live.debounce.350ms="search"
                    type="search"
                    placeholder="Cari Pokemon..."
                    class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-red-500 focus:ring-4 focus:ring-red-100"
                >
            </label>
        </header>

        <div class="mt-6" wire:loading.delay>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach (range(1, 4) as $item)
                    <x-pokemon.skeleton-card />
                @endforeach
            </div>
        </div>

        <div class="mt-6" wire:loading.delay.remove>
            @if ($error)
                <x-pokemon.error-state :message="$error" />
            @elseif (count($pokemon) === 0)
                <x-pokemon.empty-state />
            @else
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($pokemon as $item)
                        <a
                            href="{{ route('pokemon.show', $item['name']) }}"
                            wire:key="pokemon-{{ $item['id'] }}"
                            class="group rounded-lg border border-zinc-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-red-200 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-red-100"
                        >
                            <div class="flex items-center justify-between text-xs font-semibold text-zinc-400">
                                <span>#{{ str_pad((string) $item['id'], 4, '0', STR_PAD_LEFT) }}</span>
                                <span>{{ str($item['name'])->upper() }}</span>
                            </div>

                            <div class="mt-4 flex justify-center">
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

                            <h2 class="mt-4 text-lg font-bold text-zinc-950">{{ $item['display_name'] }}</h2>

                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($item['types'] as $type)
                                    <x-pokemon.type-badge :type="$type" />
                                @endforeach
                            </div>
                        </a>
                    @endforeach
                </div>

                @if ($search === '' && count($pokemon) < $total)
                    <div class="mt-8 flex justify-center">
                        <button
                            wire:click="loadMore"
                            wire:loading.attr="disabled"
                            type="button"
                            class="rounded-lg bg-zinc-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-red-600 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            Muat lagi
                        </button>
                    </div>
                @endif
            @endif
        </div>
    </section>
</main>
