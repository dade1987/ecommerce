@aware(['categories', 'header_image'])


@foreach ($categories as $category)
    <section class="text-gray-600 body-font">
        <div class="container py-3 mx-auto">
            <div class="flex flex-col text-center w-full">
                <h2 class="text-xs text-indigo-500 tracking-widest font-medium title-font mb-1">ROOF PARTY POLAROID</h2>
                <h1 class="sm:text-3xl text-2xl font-medium title-font mb-4 text-gray-900">{{ $category->name }}</h1>
                <div class="flex justify-center items-center">
                    <div class="aspect-video">
                        <x-curator-glider class="object-cover w-auto" :media="$category->featuredImage" />
                    </div>
                </div>
            </div>
            {{--<div class="flex flex-wrap">
                <div class="xl:w-full lg:w-full text-center md:w-full px-8 py-6 border-l-2 border-gray-200 border-opacity-60">
                    <h2 class="text-lg sm:text-xl text-gray-900 font-medium title-font mb-2">Melanchole</h2>
                    <p class="leading-relaxed text-base mb-4">Fingerstache flexitarian street art 8-bit waistcoat.
                        Distillery hexagon disrupt edison bulbche.</p>
                    <a class="text-indigo-500 inline-flex items-center">Learn More
                        <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="2" class="w-4 h-4 ml-2" viewBox="0 0 24 24">
                            <path d="M5 12h14M12 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            </div>--}}
            <button
                class="flex mx-auto mt-5 text-white bg-green-500 border-0 py-2 px-8 focus:outline-none hover:bg-green-600 rounded text-lg">Vedi</button>
        </div>
    </section>
@endforeach
