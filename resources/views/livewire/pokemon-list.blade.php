<main class="min-h-screen bg-zinc-50 text-zinc-950">
    <section class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
        <header class="flex flex-col gap-5 border-b border-zinc-200 pb-6 md:flex-row md:items-end md:justify-between">
            <div class="min-w-0">
                <p class="text-sm font-semibold uppercase text-red-600">PokeAPI Explorer</p>
                <h1 class="mt-2 break-words text-3xl font-bold text-zinc-950 md:text-4xl">Pokemon Explorer</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-600">
                    Jelajahi Pokemon, type, artwork, dan statistik dasar langsung dari PokeAPI.
                </p>
            </div>

            <label class="w-full md:max-w-sm">
                <span class="sr-only">Cari Pokemon</span>
                <input
                    wire:model.live.debounce.350ms="search"
                    type="search"
                    placeholder="Cari Pokemon..."
                    class="h-12 w-full rounded-lg border border-zinc-300 bg-white px-4 text-sm outline-none transition placeholder:text-zinc-400 focus:border-red-500 focus:ring-4 focus:ring-red-100"
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
                <div id="pokemon-grid" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($pokemon as $item)
                        <x-pokemon.card :item="$item" />
                    @endforeach
                </div>

                @if ($search === '' && count($pokemon) < $total)
                    <div id="load-more-wrap" class="mt-8 flex justify-center">
                        <button
                            id="load-more-button"
                            data-url="{{ route('pokemon.load-more') }}"
                            data-next-offset="{{ $offset + $limit }}"
                            type="button"
                            class="inline-flex h-11 min-w-32 items-center justify-center rounded-lg bg-zinc-950 px-5 text-sm font-semibold text-white transition hover:bg-red-600 focus:outline-none focus:ring-4 focus:ring-red-100 disabled:cursor-not-allowed disabled:opacity-70"
                        >
                            <span data-label>Muat lagi</span>
                        </button>
                    </div>
                @endif
            @endif
        </div>
    </section>

    <script>
        document.addEventListener('click', async (event) => {
            const button = event.target.closest('#load-more-button');

            if (! button || button.disabled) {
                return;
            }

            const label = button.querySelector('[data-label]');
            const grid = document.getElementById('pokemon-grid');
            const wrapper = document.getElementById('load-more-wrap');
            const url = new URL(button.dataset.url, window.location.origin);

            url.searchParams.set('offset', button.dataset.nextOffset);
            button.disabled = true;
            label.textContent = 'Mohon tunggu';

            try {
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                    },
                });

                if (! response.ok) {
                    throw new Error('Request failed');
                }

                const payload = await response.json();
                grid.insertAdjacentHTML('beforeend', payload.html);
                button.dataset.nextOffset = payload.nextOffset;

                if (! payload.hasMore && wrapper) {
                    wrapper.remove();
                    return;
                }
            } catch (error) {
                label.textContent = 'Coba lagi';
                return;
            }

            button.disabled = false;
            label.textContent = 'Muat lagi';
        });
    </script>
</main>
