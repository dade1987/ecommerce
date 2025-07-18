<button wire:click="open"
    class="
        @if($style === 'primary')
            inline-flex items-center justify-center rounded-lg bg-blue-500 px-5 py-3 text-center text-base font-medium text-white hover:bg-blue-600 focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800
        @else
            inline-flex items-center justify-center rounded-lg bg-gray-200 px-5 py-3 text-center text-base font-medium text-gray-700 hover:bg-gray-300 focus:ring-4 focus:ring-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:focus:ring-gray-800
        @endif
    ">
    {{ $text }}
</button> 