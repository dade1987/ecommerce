@aware(['page'])
<section class="bg-white dark:bg-gray-800 dark:text-gray-100">
    <div class="container mx-auto py-12">
        <div class="row items-center justify-center">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="text-4xl font-bold mb-4 text-gray-900 dark:text-white">
                    {{ $heading }}
                </h1>
                <p class="text-lg mb-4 text-gray-600 dark:text-gray-300">
                    {{ $subheading }}
                </p>
                @if($badges)
                <div class="flex flex-wrap justify-center gap-3">
                    @foreach($badges as $badge)
                        <span class="bg-gray-200 text-gray-800 px-3 py-2 rounded-full text-sm font-semibold">{{ $badge['text'] }}</span>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</section> 