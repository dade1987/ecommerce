@aware(['page'])
@props([
    'text',
    'style',
    'align',
])

@php
    $alignmentClasses = [
        'left' => 'text-left',
        'center' => 'text-center',
        'right' => 'text-right',
    ];

@endphp

<section class="px-4 py-8">
    <div class="{{ $alignmentClasses[$align] ?? 'text-center' }}">
        @livewire('open-calendar-button', [
            'text' => $text,
            'style' => $style,
        ])
    </div>
</section>

@livewire('calendar-slideover') 