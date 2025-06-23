@aware(['page'])
<div class="px-4 py-4 md:py-8  ">
    <div class="max-w-7xl mx-auto">
        <section class="my-8 dark:bg-gray-800 dark:text-gray-100">
            <div class="container flex flex-col items-center mx-auto mb-12 md:p-10 md:px-12">
                <h1 class="p-4 text-4xl font-semibold leadi text-center">{{ $title }}</h1>
            </div>
            <div
                class="container flex flex-col items-center justify-center mx-auto lg:flex-row lg:flex-wrap lg:justify-evenly lg:px-10">
                @foreach ($testimonials as $index => $testimonial)
                    <div class="flex flex-col max-w-sm mx-4 my-6 shadow-lg">
                        <div class="px-4 py-12 rounded-t-lg sm:px-8 md:px-12 dark:bg-gray-900">
                            <p class="relative px-6 py-1 text-lg italic text-center dark:text-gray-100">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor"
                                    class="w-8 h-8 dark:text-violet-400">
                                    <path d="M232,246.857V16H16V416H54.4ZM48,48H200V233.143L48,377.905Z"></path>
                                    <path d="M280,416h38.4L496,246.857V16H280ZM312,48H464V233.143L312,377.905Z"></path>
                                </svg>
                                {{ $testimonial['text'] }}

                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor"
                                    class="absolute right-0 w-8 h-8 dark:text-violet-400">
                                    <path d="M280,185.143V416H496V16H457.6ZM464,384H312V198.857L464,54.1Z"></path>
                                    <path d="M232,16H193.6L16,185.143V416H232ZM200,384H48V198.857L200,54.1Z"></path>
                                </svg>
                            </p>
                        </div>
                        <div
                            class="flex flex-col items-center justify-center p-8 rounded-b-lg dark:bg-violet-400 dark:text-gray-900">
                            <img src="{{ url('images/' . $testimonial['logo']) }}" alt="{{ $testimonial['subtitle'] }}"
                                class="w-16 h-16 mb-2 -mt-16 bg-center bg-cover rounded-full dark:bg-gray-500 dark:bg-gray-700">
                            <p class="text-xl font-semibold leadi">{{ $testimonial['subtitle'] }}</p>
                            <p class="text-sm uppercase">{{ $testimonial['subtitle'] }}</p>
                        </div>
                    </div>
                @endforeach

            </div>
        </section>
    </div>
</div>
