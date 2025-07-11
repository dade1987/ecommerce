@aware(['page'])

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glider-js@1.7.7/glider.min.css">

<div class="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">
    @if(!empty($certifications))
        <div>
            <h2 class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">Certificazioni</h2>
            <div class="glider-contain">
                <div class="glider mt-6">
                    @foreach ($certifications as $certification)
                        <div class="mx-2">
                            <div class="flex flex-col items-start p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700 h-full">
                                @if($certification['image'])
                                    @php
                                        $media = \App\Models\Media::find($certification['image']);
                                    @endphp
                                    <div class="flex-shrink-0 mb-4">
                                        @if($media)
                                        <img class="w-16 h-16 rounded-full" src="{{ $media->getUrl() }}" alt="{{ $certification['issuer'] }} logo">
                                        @endif
                                    </div>
                                @endif
                                <div class="flex-grow">
                                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $certification['title'] }}</h3>
                                    <p class="text-base text-gray-600 dark:text-gray-400">{{ $certification['issuer'] }}</p>
                                    @if($certification['issue_date'] || $certification['credential_id'])
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-300">
                                        @if($certification['issue_date'])
                                            <span>Data: {{ $certification['issue_date'] }}</span>
                                        @endif
                                        @if($certification['issue_date'] && $certification['credential_id'])
                                            <span class="mx-2">·</span>
                                        @endif
                                        @if($certification['credential_id'])
                                            <span>ID: {{ $certification['credential_id'] }}</span>
                                        @endif
                                    </p>
                                    @endif
                                </div>
                                @if($certification['credential_url'])
                                    <div class="mt-auto pt-4">
                                        <a href="{{ $certification['credential_url'] }}" target="_blank" rel="noopener noreferrer" 
                                           class="inline-block px-5 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-blue-800">
                                            Mostra credenziale
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                <button aria-label="Previous" class="glider-prev">«</button>
                <button aria-label="Next" class="glider-next">»</button>
                <div role="tablist" class="dots"></div>
            </div>
        </div>
    @endif

        @if(!empty($important_links))
            <div>
                <h2 class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">Link Importanti</h2>
                <div class="mt-6 space-y-4">
                    <ul class="list-disc list-inside">
                        @foreach ($important_links as $link)
                            <li class="text-lg">
                                <a href="{{ $link['link_url'] }}" target="_blank" rel="noopener noreferrer" class="font-medium text-blue-600 hover:underline dark:text-blue-500">
                                    {{ $link['link_title'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/glider-js@1.7.7/glider.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        new Glider(document.querySelector('.glider'), {
            slidesToShow: 1,
            slidesToScroll: 1,
            draggable: true,
            dots: '.dots',
            arrows: {
                prev: '.glider-prev',
                next: '.glider-next'
            },
            responsive: [
                {
                    breakpoint: 640,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 2,
                    }
                },
                {
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 3,
                    }
                }
            ]
        });
    });
</script>
<style>
.glider-prev, .glider-next {
    background: rgba(0,0,0,0.5);
    color: white;
    border-radius: 9999px;
    padding: 0.5rem 1rem;
}
.glider-dots .glider-dot {
    background: #ccc;
}
.glider-dots .glider-dot.active {
    background: #3B82F6;
}
</style> 