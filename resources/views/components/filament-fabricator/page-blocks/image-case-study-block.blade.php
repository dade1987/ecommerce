@aware(['page'])

<section class="py-12 bg-gray-50 dark:bg-gray-900">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            <div class="@if($alignment === 'right') md:order-last @endif">
                @if(!empty($images) && $images->count() > 0)
                    <div x-data="{ activeSlide: 1, totalSlides: {{ $images->count() }} }" class="relative">
                        {{-- Images --}}
                        @foreach($images as $image)
                            <div x-show="activeSlide === {{ $loop->index + 1 }}" class="duration-300 ease-in-out" x-transition:enter.opacity.duration.300ms x-transition:leave.opacity.duration.300ms>
                                <x-curator-glider
                                    :media="$image"
                                    class="w-full h-auto rounded-lg shadow-lg object-cover"
                                />
                            </div>
                        @endforeach

                        {{-- Prev/Next Buttons --}}
                        @if ($images->count() > 1)
                            <div class="absolute inset-0 flex items-center justify-between">
                                <button @click="activeSlide = (activeSlide === 1) ? totalSlides : activeSlide - 1" class="text-white bg-black bg-opacity-30 hover:bg-opacity-50 rounded-full p-2 transition">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                                </button>
                                <button @click="activeSlide = (activeSlide % totalSlides) + 1" class="text-white bg-black bg-opacity-30 hover:bg-opacity-50 rounded-full p-2 transition">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </button>
                            </div>

                            {{-- Dots --}}
                            <div class="absolute bottom-4 left-0 right-0 flex justify-center space-x-2">
                                @foreach($images as $image)
                                    <button @click="activeSlide = {{ $loop->index + 1 }}" :class="{'bg-white scale-125': activeSlide === {{ $loop->index + 1 }}, 'bg-white/50': activeSlide !== {{ $loop->index + 1 }}}" class="w-3 h-3 rounded-full hover:bg-white focus:outline-none transition-transform"></button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
            </div>
            <div>
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ $title }}</h2>
                @if($subtitle)
                    <p class="text-xl text-violet-600 dark:text-violet-400 font-semibold mb-4">{{ $subtitle }}</p>
                @endif
                <div class="prose dark:prose-invert max-w-none text-gray-600 dark:text-gray-300">
                    {!! $description !!}
                </div>
            </div>
        </div>
    </div>
</section> 