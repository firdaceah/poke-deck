@props(['title' => 'Pokemon tidak ditemukan', 'message' => 'Coba gunakan kata kunci lain.'])

<div {{ $attributes->class('rounded-lg border border-dashed border-zinc-300 bg-white p-8 text-center') }}>
    <h2 class="text-base font-semibold text-zinc-900">{{ $title }}</h2>
    <p class="mt-2 text-sm text-zinc-500">{{ $message }}</p>
</div>
