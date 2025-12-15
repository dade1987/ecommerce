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

                <div class="mt-8">
                    <div class="flex flex-wrap justify-start gap-4">
                        @if($button_one_link && $button_one_text)
                            <a href="{{ $button_one_link }}"
                               class="inline-flex items-center justify-center rounded-lg bg-blue-500 px-5 py-3 text-center text-base font-medium text-white hover:bg-blue-600 focus:ring-4 focus:ring-blue-200 dark:focus:ring-blue-700">
                                {{ $button_one_text }}
                            </a>
                        @endif

                        @livewire('open-calendar-button', ['style' => 'primary', 'text' => 'Prenota una call tecnica di 15 minuti'])
                        
                        <a href="#contact-form" class="inline-flex items-center justify-center rounded-lg bg-gray-600 px-5 py-3 text-center text-base font-medium text-white hover:bg-gray-700 focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700">
                            {{ __('frontend.contact_us') }}
                        </a>
                    </div>
                    
                    <!-- Sottotesto CTA -->
                    <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                        Serve solo a capire se ha senso lavorare insieme. Se non ha senso, te lo dico.
                    </p>
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
