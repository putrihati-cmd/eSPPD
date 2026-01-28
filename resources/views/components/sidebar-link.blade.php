@props(['active' => false])

@php
    $classes = $active
        ? 'flex items-center gap-3 px-4 py-3 bg-brand-50 text-brand-600 font-semibold border-r-4 border-brand-500 transition-all'
        : 'flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 hover:text-slate-700 transition-all';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
