@aware(['page'])

<section class="py-12 bg-gray-50 dark:bg-gray-900">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
            <div class="@if($alignment === 'right') md:order-last @endif">
                @if(!empty($images))
                    <x-curator-glider
                        :media="$images"
                        class="rounded-lg shadow-lg"
                    />
                @endif
            </div>
            <div>
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ $title }}</h2>
                @if($subtitle)
                    <p class="text-xl text-violet-600 dark:text-violet-400 font-semibold mb-4">{{ $subtitle }}</p>
                @endif
                <div class="prose dark:prose-invert max-w-none">
                    {!! $description !!}
                </div>
            </div>
        </div>
    </div>
</section> 