<div>
    <article>
        <div class="flex items-center mb-4">
            <img class="w-10 h-10 me-4 rounded-full" src="/docs/images/people/profile-picture-5.jpg" alt="Jese Leos">
            <div class="font-medium dark:text-white">
                <p>Jese Leos <time datetime="2014-08-16 19:00"
                        class="block text-sm text-gray-500 dark:text-gray-400">Joined
                        on August 2014</time></p>
            </div>
        </div>
        <div class="flex items-center mb-1 space-x-1 rtl:space-x-reverse">
            @for ($i = 1; $i <= 5; $i++)
                <svg class="w-4 h-4 @if ($review->rating >= $i) text-yellow-300 @else text-gray-300 dark:text-gray-500 @endif"
                    aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 22 20">
                    <path
                        d="M20.924 7.625a1.523 1.523 0 0 0-1.238-1.044l-5.051-.734-2.259-4.577a1.534 1.534 0 0 0-2.752 0L7.365 5.847l-5.051.734A1.535 1.535 0 0 0 1.463 9.2l3.656 3.563-.863 5.031a1.532 1.532 0 0 0 2.226 1.616L11 17.033l4.518 2.375a1.534 1.534 0 0 0 2.226-1.617l-.863-5.03L20.537 9.2a1.523 1.523 0 0 0 .387-1.575Z" />
                </svg>
            @endfor

            <h3 class="ms-2 text-sm font-semibold text-gray-900 dark:text-white">{{ $review->title }}</h3>
        </div>
        <footer class="mb-5 text-sm text-gray-500 dark:text-gray-400">
            <p>Reviewed in the United Kingdom on <time datetime="2017-03-03 19:00">March 3, 2017</time></p>
        </footer>
        <p class="mb-2 text-gray-500 dark:text-gray-400">{{ $review->review }}

        </p>
        <a href="#" class="block mb-5 text-sm font-medium text-blue-600 hover:underline dark:text-blue-500">Read
            more</a>
        <aside>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">19 people found this helpful</p>
            <div class="flex items-center mt-3">
                <a href="#"
                    class="px-2 py-1.5 text-xs font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Helpful</a>
                <a href="#"
                    class="ps-4 text-sm font-medium text-blue-600 hover:underline dark:text-blue-500 border-gray-200 ms-4 border-s md:mb-0 dark:border-gray-600">Report
                    abuse</a>
            </div>
        </aside>
    </article>
</div>
