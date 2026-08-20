@props(['status'])

@php
    $styles = match ($status) {
        'pendiente' => 'bg-amber-50 text-amber-700',
        'confirmado' => 'bg-blue-50 text-blue-700',
        'enviado' => 'bg-brand-50 text-brand-700',
        'entregado', 'recibido' => 'bg-emerald-50 text-emerald-700',
        'cancelado' => 'bg-red-50 text-red-700',
        default => 'bg-gray-100 text-gray-600',
    };

    $label = match ($status) {
        'enviado' => 'Despachado',
        default => ucfirst((string) $status),
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium $styles"]) }}>
    {{ $label }}
</span>
