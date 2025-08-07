@aware(['page'])

<section class="py-12 bg-gray-50 dark:bg-gray-900">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            <div class="@if($alignment === 'right') md:order-last @endif">
                @if(!empty($images) && $images->count() > 0)
                    <div x-data="{ activeSlide: 1, totalSlides: {{ $images->count() }}, isLightboxOpen: false, lightboxImage: '' }" class="relative">
                        <div class="overflow-hidden rounded-lg shadow-lg">
                            <div class="flex transition-transform duration-500 ease-in-out" :style="'transform: translateX(-' + (activeSlide - 1) * 100 + '%)'">
                                @foreach($images as $image)
                                    <div class="w-full flex-shrink-0">
                                        <div @click="isLightboxOpen = true; lightboxImage = '{{ $image->url }}'" class="cursor-pointer">
                                            <x-curator-glider
                                                :media="$image"
                                                class="w-full h-auto object-cover"
                                            />
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Prev/Next Buttons --}}
                        @if ($images->count() > 1)
                            <div class="absolute inset-0 flex items-center justify-between p-4 pointer-events-none">
                                <button @click="activeSlide = (activeSlide === 1) ? totalSlides : activeSlide - 1" class="text-white bg-black bg-opacity-30 hover:bg-opacity-50 rounded-full p-2 transition pointer-events-auto">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                                </button>
                                <button @click="activeSlide = (activeSlide % totalSlides) + 1" class="text-white bg-black bg-opacity-30 hover:bg-opacity-50 rounded-full p-2 transition pointer-events-auto">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </button>
                            </div>
                        @endif

                        {{-- Dots --}}
                        @if ($images->count() > 1)
                            <div class="absolute bottom-4 left-0 right-0 flex justify-center space-x-2">
                                @foreach($images as $image)
                                    <button @click="activeSlide = {{ $loop->index + 1 }}" :class="{'bg-white scale-125': activeSlide === {{ $loop->index + 1 }}, 'bg-white/50': activeSlide !== {{ $loop->index + 1 }}}" class="w-3 h-3 rounded-full hover:bg-white focus:outline-none transition-transform"></button>
                                @endforeach
                            </div>
                        @endif

                        {{-- Lightbox --}}
                        <div x-show="isLightboxOpen" @keydown.escape.window="isLightboxOpen = false" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center p-4 z-50" style="display: none;">
                            <div @click.away="isLightboxOpen = false" class="relative">
                                <img :src="lightboxImage" alt="Zoomed image" class="max-w-full max-h-[90vh] rounded-lg">
                                <button @click="isLightboxOpen = false" class="absolute top-2 right-2 text-white bg-black bg-opacity-50 rounded-full p-2">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                        </div>
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

                <div class="mt-6 flex flex-wrap gap-4">
                    @livewire('open-calendar-button', ['text' => __('frontend.book_call'), 'style' => 'primary'])
                    <a href="#contact-form" class="inline-block px-6 py-3 text-base font-semibold text-white bg-gray-600 rounded-lg shadow-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition">
                        {{ __('frontend.contact_us') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</section> 