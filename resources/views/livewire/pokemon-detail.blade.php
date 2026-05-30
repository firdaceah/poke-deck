<main class="min-h-screen bg-zinc-50 text-zinc-950">
    <section class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
        <a href="{{ route('pokemon.index') }}" class="inline-flex items-center text-sm font-semibold text-zinc-600 transition hover:text-red-600">
            Kembali ke daftar
        </a>

        <div class="mt-6">
            <x-pokemon.empty-state
                title="Detail Pokemon belum tersedia"
                :message="'Halaman detail untuk '.str($name)->replace('-', ' ')->title().' akan diimplementasikan pada Phase 4.'"
            />
        </div>
    </section>
</main>
