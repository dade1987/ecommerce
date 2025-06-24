@aware(['page'])
<section class="bg-white dark:bg-gray-900 py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">{{ $title }}</h2>
            @if($subtitle)
                <p class="mt-4 text-lg text-gray-600 dark:text-gray-400 mx-auto">{{ $subtitle }}</p>
            @endif
        </div>

        <div class="space-y-8">
            <!-- Riga 1: Immagine Sinistra, Testo Destra -->
            <div class="flex flex-col md:flex-row items-center gap-8 md:gap-12">
                <div class="flex justify-center md:w-1/2">
                    <x-curator-glider :media="$imageOne" class="max-w-md h-auto bg-transparent" />
                </div>
                <div class="md:w-1/2">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $titleOne }}</h3>
                    <p class="mt-4 text-gray-600 dark:text-gray-400">{{ $textOne }}</p>
                </div>
            </div>

            <!-- Riga 2: Testo Sinistra, Immagine Destra su desktop -->
            <div class="flex flex-col md:flex-row-reverse items-center gap-8 md:gap-12">
                <div class="flex justify-center md:w-1/2">
                    <x-curator-glider :media="$imageTwo" class="max-w-md h-auto bg-transparent" />
                </div>
                <div class="md:w-1/2">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $titleTwo }}</h3>
                    <p class="mt-4 text-gray-600 dark:text-gray-400">{{ $textTwo }}</p>
                </div>
            </div>

            <!-- Riga 3: Immagine Sinistra, Testo Destra -->
            <div class="flex flex-col md:flex-row items-center gap-8 md:gap-12">
                <div class="flex justify-center md:w-1/2">
                    <x-curator-glider :media="$imageThree" class="max-w-md h-auto bg-transparent" />
                </div>
                <div class="md:w-1/2">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $titleThree }}</h3>
                    <p class="mt-4 text-gray-600 dark:text-gray-400">{{ $textThree }}</p>
                </div>
            </div>
        </div>

    </div>
</section>