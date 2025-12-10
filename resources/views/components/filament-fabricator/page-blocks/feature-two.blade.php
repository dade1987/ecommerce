@aware(['page'])
@props([
    'title',
    'subtitle',
    'imageOne',
    'titleOne',
    'textOne',
    'showButtonOne',
    'imageTwo',
    'titleTwo',
    'textTwo',
    'showButtonTwo',
    'imageThree',
    'titleThree',
    'textThree',
    'showButtonThree',
])
<section id="servizi" class="bg-white dark:bg-gray-900 py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">{{ $title }}</h2>
            @if($subtitle)
                <p class="mt-4 text-lg text-gray-600 dark:text-gray-400 mx-auto">{{ $subtitle }}</p>
            @endif
        </div>

        <div class="space-y-8">
            <!-- Riga 1: Immagine Sinistra, Testo Destra -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <div class="flex justify-center">
                    <x-curator-glider :media="$imageOne" class="max-w-full h-auto bg-white dark:bg-gray-900" />
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $titleOne }}</h3>
                    <div class="prose dark:prose-invert mt-4 text-gray-600 dark:text-gray-400">{!! $textOne !!}</div>
                    @if($showButtonOne)
                        <div class="mt-6">
                            @livewire('open-calendar-button')
                            <a href="#contact-form" class="inline-block ml-4 px-6 py-3 text-base font-semibold text-white bg-gray-600 rounded-lg shadow-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition">
                                {{ __('frontend.contact_us') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Riga 2: Testo Sinistra, Immagine Destra -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $titleTwo }}</h3>
                    <div class="prose dark:prose-invert mt-4 text-gray-600 dark:text-gray-400">{!! $textTwo !!}</div>
                    @if($showButtonTwo)
                        <div class="mt-6">
                            @livewire('open-calendar-button')
                            <a href="#contact-form" class="inline-block ml-4 px-6 py-3 text-base font-semibold text-white bg-gray-600 rounded-lg shadow-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition">
                                {{ __('frontend.contact_us') }}
                            </a>
                        </div>
                    @endif
                </div>
                <div class="order-first md:order-last flex justify-center">
                    <x-curator-glider :media="$imageTwo" class="max-w-full h-auto bg-white dark:bg-gray-900" />
                </div>
            </div>

            <!-- Riga 3: Immagine Sinistra, Testo Destra -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <div class="flex justify-center">
                    <x-curator-glider :media="$imageThree" class="max-w-full h-auto bg-white dark:bg-gray-900" />
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $titleThree }}</h3>
                    <div class="prose dark:prose-invert mt-4 text-gray-600 dark:text-gray-400">{!! $textThree !!}</div>
                    @if($showButtonThree)
                        <div class="mt-6">
                            @livewire('open-calendar-button')
                            <a href="#contact-form" class="inline-block ml-4 px-6 py-3 text-base font-semibold text-white bg-gray-600 rounded-lg shadow-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition">
                                {{ __('frontend.contact_us') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</section>