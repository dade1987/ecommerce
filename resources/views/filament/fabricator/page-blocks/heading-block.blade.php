@if($title || $subtitle)
<div class="bg-white dark:bg-gray-900 py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        @if($title)
            <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white">
                {{ $title }}
            </h1>
        @endif
        @if($subtitle)
            <p class="mt-2 text-xl text-gray-600 dark:text-gray-300">
                {{ $subtitle }}
            </p>
        @endif
    </div>
</div>
@endif 