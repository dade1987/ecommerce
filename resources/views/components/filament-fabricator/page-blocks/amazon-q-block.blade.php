@aware(['page'])
@props([
'title',
'subtitle',
'image',
'button_one_text',
'button_one_link',
'button_two_text',
'button_two_link',
])

<section class="bg-white dark:bg-gray-900">
    <div class="container mx-auto px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
        <div class="grid grid-cols-1 items-center gap-12 md:grid-cols-2">

            {{-- Colonna Sinistra: Testo --}}
            <div class="text-center md:text-left">
                @if($title)
                    <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-5xl md:text-6xl">
                        {{ $title }}
                    </h1>
                @endif

                @if($subtitle)
                    <h2 class="mt-4 text-xl text-gray-600 dark:text-gray-300 md:text-2xl">
                        {{ $subtitle }}
                    </h2>
                @endif

                <div class="mt-8 flex flex-wrap justify-center gap-4 md:justify-start">
                    @if($button_one_link && $button_one_text)
                        <a href="{{ $button_one_link }}"
                           class="inline-block rounded-lg bg-blue-600 px-8 py-3 text-lg font-medium text-white transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800">
                            {{ $button_one_text }}
                        </a>
                    @endif

                    @if($button_two_link && $button_two_text)
                        <a href="{{ $button_two_link }}"
                           class="inline-block rounded-lg bg-gray-200 px-8 py-3 text-lg font-medium text-gray-700 transition hover:bg-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:focus:ring-gray-800">
                            {{ $button_two_text }}
                        </a>
                    @endif
                </div>
            </div>

            {{-- Colonna Destra: Immagine --}}
            <div class="flex justify-center">
                @if($image)
                    <div class="relative h-64 w-64 sm:h-80 sm:w-80 lg:h-96 lg:w-96">
                        <div class="absolute inset-0 rounded-full bg-gradient-to-tr from-purple-500 to-cyan-400 opacity-50 blur-2xl"></div>
                        <x-curator-glider
                            :media="$image"
                            class="relative h-full w-full rounded-full object-cover shadow-2xl"
                        />
                    </div>
                @endif
            </div>

        </div>
    </div>
</section>
