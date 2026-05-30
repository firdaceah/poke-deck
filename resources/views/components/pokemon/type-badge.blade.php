@props(['type'])

@php
    $colors = [
        'normal' => 'bg-zinc-100 text-zinc-700 ring-zinc-200',
        'fire' => 'bg-red-100 text-red-700 ring-red-200',
        'water' => 'bg-sky-100 text-sky-700 ring-sky-200',
        'electric' => 'bg-yellow-100 text-yellow-800 ring-yellow-200',
        'grass' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
        'ice' => 'bg-cyan-100 text-cyan-700 ring-cyan-200',
        'fighting' => 'bg-orange-100 text-orange-800 ring-orange-200',
        'poison' => 'bg-fuchsia-100 text-fuchsia-700 ring-fuchsia-200',
        'ground' => 'bg-amber-100 text-amber-800 ring-amber-200',
        'flying' => 'bg-indigo-100 text-indigo-700 ring-indigo-200',
        'psychic' => 'bg-pink-100 text-pink-700 ring-pink-200',
        'bug' => 'bg-lime-100 text-lime-800 ring-lime-200',
        'rock' => 'bg-stone-100 text-stone-700 ring-stone-200',
        'ghost' => 'bg-violet-100 text-violet-700 ring-violet-200',
        'dragon' => 'bg-purple-100 text-purple-700 ring-purple-200',
        'dark' => 'bg-neutral-800 text-white ring-neutral-700',
        'steel' => 'bg-slate-100 text-slate-700 ring-slate-200',
        'fairy' => 'bg-rose-100 text-rose-700 ring-rose-200',
    ];

    $class = $colors[$type] ?? 'bg-zinc-100 text-zinc-700 ring-zinc-200';
@endphp

<span {{ $attributes->class("inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold capitalize ring-1 {$class}") }}>
    {{ str($type)->replace('-', ' ')->title() }}
</span>
