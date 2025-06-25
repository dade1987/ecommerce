@aware(['page'])
@props([
'title',
'subtitle',
'image',
'button_one_text',
'button_one_link',
])

<section class="bg-gray-100 dark:bg-gray-800">
    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
        <div class="grid grid-cols-1 items-center gap-12 md:grid-cols-2">

            {{-- Colonna Sinistra: Testo --}}
            <div class="text-left">
                @if($title)
                    <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-5xl md:text-6xl">
                        {{ $title }}
                    </h1>
                @endif

                @if($subtitle)
                    <div class="prose prose-xl dark:prose-invert mt-4 text-gray-600 dark:text-gray-300 md:text-2xl">
                        {!! $subtitle !!}
                    </div>
                @endif

                <div class="mt-8 flex flex-wrap justify-start gap-4">
                    @if($button_one_link && $button_one_text)
                        <a href="{{ $button_one_link }}"
                           class="inline-block rounded-lg bg-blue-600 px-8 py-3 text-lg font-medium text-white transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800">
                            {{ $button_one_text }}
                        </a>
                    @endif

                    @livewire('open-calendar-button', ['style' => 'secondary'])
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
