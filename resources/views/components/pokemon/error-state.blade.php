@props(['message' => 'Data belum bisa dimuat.'])

<div {{ $attributes->class('rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium leading-6 text-red-800 shadow-sm') }} role="alert">
    <span class="block break-words">{{ $message }}</span>
</div>
