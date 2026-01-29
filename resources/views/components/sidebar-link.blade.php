@props(['active' => false])

@php
    $classes = $active
        ? 'flex items-center gap-3 px-4 py-3 bg-brand-50 text-brand-teal font-bold border-r-4 border-brand-teal transition-all'
        : 'flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 hover:text-brand-teal transition-all';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
