@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-bold text-sm text-brand-teal']) }}>
        {{ $status }}
    </div>
@endif
