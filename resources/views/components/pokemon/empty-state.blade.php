@props(['title' => 'Pokemon tidak ditemukan', 'message' => 'Coba gunakan kata kunci lain.'])

<div {{ $attributes->class('rounded-lg border border-dashed border-zinc-300 bg-white px-5 py-8 text-center shadow-sm sm:px-8') }}>
    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 text-lg font-bold text-zinc-400">
        ?
    </div>
    <h2 class="mt-4 text-base font-semibold text-zinc-900">{{ $title }}</h2>
    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-zinc-500">{{ $message }}</p>
</div>
