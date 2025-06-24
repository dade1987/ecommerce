@aware(['page'])
<section class="bg-white dark:bg-gray-900 py-16">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">{{ $title }}</h2>
            @if($subtitle)
                <p class="mt-4 text-lg text-gray-600 dark:text-gray-400 max-w-3xl mx-auto">{{ $subtitle }}</p>
            @endif
        </div>

        <div class="space-y-24">
            <!-- Riga 1 -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <div class="order-1">
                    <x-curator-glider :media="$imageOne" class="max-w-full h-auto mx-auto rounded-lg shadow-lg" />
                </div>
                <div class="order-2">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $titleOne }}</h3>
                    <p class="mt-4 text-gray-600 dark:text-gray-400">{{ $textOne }}</p>
                </div>
            </div>

            <!-- Riga 2 -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <div class="order-2 md:order-1">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $titleTwo }}</h3>
                    <p class="mt-4 text-gray-600 dark:text-gray-400">{{ $textTwo }}</p>
                </div>
                <div class="order-1 md:order-2">
                    <x-curator-glider :media="$imageTwo" class="max-w-full h-auto mx-auto rounded-lg shadow-lg" />
                </div>
            </div>

            <!-- Riga 3 -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <div class="order-1">
                    <x-curator-glider :media="$imageThree" class="max-w-full h-auto mx-auto rounded-lg shadow-lg" />
                </div>
                <div class="order-2">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $titleThree }}</h3>
                    <p class="mt-4 text-gray-600 dark:text-gray-400">{{ $textThree }}</p>
                </div>
            </div>
        </div>

    </div>
</section>