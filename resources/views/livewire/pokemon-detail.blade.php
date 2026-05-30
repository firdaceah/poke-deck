<main class="min-h-screen bg-zinc-50 text-zinc-950">
    <section class="mx-auto max-w-6xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
        <a href="{{ route('pokemon.index') }}" class="inline-flex min-h-10 items-center rounded-lg text-sm font-semibold text-zinc-600 transition hover:text-red-600 focus:outline-none focus:ring-4 focus:ring-red-100">
            Kembali ke daftar
        </a>

        <div class="mt-6">
            @if ($error)
                <x-pokemon.error-state :message="$error" />
            @elseif ($pokemon)
                <div class="grid gap-6 lg:grid-cols-[minmax(0,420px)_1fr]">
                    <section class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between gap-3 text-sm font-semibold text-zinc-400">
                            <span class="shrink-0">#{{ str_pad((string) $pokemon['id'], 4, '0', STR_PAD_LEFT) }}</span>
                            <span class="min-w-0 truncate">{{ str($pokemon['name'])->upper() }}</span>
                        </div>

                        <div class="mt-6 flex min-h-64 items-center justify-center">
                            @if ($pokemon['image'])
                                <img
                                    src="{{ $pokemon['image'] }}"
                                    alt="{{ $pokemon['display_name'] }}"
                                    class="h-64 w-64 object-contain"
                                >
                            @else
                                <div class="flex h-64 w-64 items-center justify-center rounded-full bg-zinc-100 text-sm text-zinc-400">
                                    No image
                                </div>
                            @endif
                        </div>

                        <h1 class="mt-6 break-words text-3xl font-bold leading-tight">{{ $pokemon['display_name'] }}</h1>

                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach ($pokemon['types'] as $type)
                                <x-pokemon.type-badge :type="$type" />
                            @endforeach
                        </div>

                        <dl class="mt-6 grid grid-cols-1 gap-3 text-center min-[420px]:grid-cols-3">
                            <div class="rounded-lg bg-zinc-50 p-3">
                                <dt class="text-xs font-medium text-zinc-500">Height</dt>
                                <dd class="mt-1 font-semibold">{{ $pokemon['height'] }}</dd>
                            </div>
                            <div class="rounded-lg bg-zinc-50 p-3">
                                <dt class="text-xs font-medium text-zinc-500">Weight</dt>
                                <dd class="mt-1 font-semibold">{{ $pokemon['weight'] }}</dd>
                            </div>
                            <div class="rounded-lg bg-zinc-50 p-3">
                                <dt class="text-xs font-medium text-zinc-500">EXP</dt>
                                <dd class="mt-1 font-semibold">{{ $pokemon['base_experience'] ?? '-' }}</dd>
                            </div>
                        </dl>
                    </section>

                    <section class="space-y-6">
                        <div class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm">
                            <h2 class="text-lg font-bold">Base Stats</h2>
                            <div class="mt-5 space-y-4">
                                @foreach ($pokemon['stats'] as $stat)
                                    <x-pokemon.stat-bar :label="$stat['display_name']" :value="$stat['value']" />
                                @endforeach
                            </div>
                        </div>

                        <div class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm">
                            <h2 class="text-lg font-bold">Abilities</h2>
                            <div class="mt-4 flex flex-wrap gap-2">
                                @foreach ($pokemon['abilities'] as $ability)
                                    <span class="rounded-full bg-zinc-100 px-3 py-1 text-sm font-medium text-zinc-700">
                                        {{ $ability['display_name'] }}{{ $ability['is_hidden'] ? ' (Hidden)' : '' }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <div class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm">
                            <h2 class="text-lg font-bold">Moves</h2>
                            <div class="mt-4 flex flex-wrap gap-2">
                                @foreach ($pokemon['moves'] as $move)
                                    <span class="max-w-full break-words rounded-md border border-zinc-200 bg-white px-3 py-1 text-sm leading-6 text-zinc-700">
                                        {{ $move }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </section>
                </div>
            @endif
        </div>
    </section>
</main>
