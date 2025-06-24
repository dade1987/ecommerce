<a name="about-us"></a>
<section class="bg-white dark:bg-gray-900">
    <div class="py-8 px-4 mx-auto max-w-screen-xl lg:py-16 lg:px-6">
        <div class="mx-auto max-w-screen-sm text-center mb-8 lg:mb-16">
            <h2 class="mb-4 text-4xl tracking-tight font-extrabold text-gray-900 dark:text-white">{{ $title }}</h2>
            <p class="font-light text-gray-500 lg:mb-16 sm:text-xl dark:text-gray-400">{{ $text }}</p>
        </div>
        <div class="flex flex-wrap gap-8 mb-6 lg:mb-16 justify-center">
            @foreach ($persons as $person)
                <div class="w-full sm:w-2/5 md:w-1/3 bg-gray-50 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700 p-5 text-center sm:text-left">
                    <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4">
                        <img class="w-24 h-24 rounded-full object-cover flex-shrink-0" src="{{ $person['image'] }}" alt="{{ $person['name'] }}">
                        <div class="person-info">
                            <h3 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">
                                <a href="#">{{ $person['name'] }}</a>
                            </h3>
                            <span class="text-gray-500 dark:text-gray-400">{{ $person['role'] }}</span>
                            <p class="mt-3 mb-4 font-light text-gray-500 dark:text-gray-400">{{ $person['text'] }}</p>
                            <ul class="flex space-x-4 sm:mt-0 justify-center sm:justify-start">
                                <li>
                                    <a href="{{ $person['linkedin'] }}" class="text-gray-500 hover:text-gray-900 dark:hover:text-white" target="_blank" rel="noopener noreferrer">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path fill-rule="evenodd"
                                                d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
