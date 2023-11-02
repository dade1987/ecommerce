<x-app-layout>
    <div class="container py-3 mx-auto grid md:grid-cols-2 md:gap-2 sm:grid-cols-1 sm:gap-1">
        <div class="text-center py-3 mx-auto">
            <div class="text-center">
                <x-filament::breadcrumbs :breadcrumbs="$breadcrumbs" />
            </div>
        </div>
    </div>

    <x-models-list :rows="$products" />
</x-app-layout>