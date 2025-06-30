<x-filament-widgets::widget>
    <x-filament::section>
        {{-- Widget content --}}
    </x-filament::section>
</x-filament-widgets::widget>

@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
           Livewire.on('gtag_report_conversion', (event) => {
               if (typeof gtag_report_conversion === 'function') {
                   gtag_report_conversion();
               }
           });
        });
    </script>
@endpush
