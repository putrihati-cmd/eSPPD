@props(['active' => false])

@php
    $classes = $active
        ? 'flex items-center gap-3 px-4 py-3 rounded-xl bg-blue-600 text-white font-medium'
        : 'flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-700/50 transition-colors';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
