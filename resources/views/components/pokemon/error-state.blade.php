@props(['message' => 'Data belum bisa dimuat.'])

<div {{ $attributes->class('rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700') }}>
    {{ $message }}
</div>
