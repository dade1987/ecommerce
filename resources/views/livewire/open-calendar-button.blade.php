<button wire:click="open"
    class="
        @if($style === 'primary')
            inline-block rounded-lg bg-blue-600 px-8 py-3 text-lg font-medium text-white transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800
        @else
            inline-block rounded-lg bg-gray-200 px-8 py-3 text-lg font-medium text-gray-700 transition hover:bg-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:focus:ring-gray-800
        @endif
    ">
    {{ $text }}
</button> 