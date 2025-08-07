@aware(['page'])
<section class="bg-gray-100 dark:bg-gray-900 py-12">
    <div class="container mx-auto px-4">
        <div class="grid gap-8 text-center sm:grid-cols-2 lg:grid-cols-4">
            @if(!empty($stats))
                @foreach($stats as $stat)
                    <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
                        <i class="{{ $stat['icon'] }} text-4xl {{ $stat['icon_color_class'] ?? 'text-violet-600' }} dark:{{ $stat['icon_color_class'] ?? 'text-violet-400' }} mb-4 inline-block"></i>
                        <h3 class="text-3xl font-bold {{ $stat['number_color_class'] ?? 'text-gray-900' }} dark:{{ $stat['number_color_class'] ?? 'text-white' }}">{{ $stat['number'] }}</h3>
                        <p class="text-gray-500 dark:text-gray-400 mt-2">{{ $stat['label'] }}</p>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</section> 