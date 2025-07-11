@aware(['page'])
<div class="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">
    <div class="space-y-12">
        @if(!empty($certifications))
            <div>
                <h2 class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">Certificazioni</h2>
                <div class="mt-6 space-y-8">
                    @foreach ($certifications as $certification)
                        <div class="flex items-start p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                            @if($certification['image'])
                                <div class="flex-shrink-0 mr-6">
                                    <img class="w-16 h-16 rounded-full" src="{{ asset('storage/' . $certification['image']) }}" alt="{{ $certification['issuer'] }} logo">
                                </div>
                            @endif
                            <div class="flex-grow">
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $certification['title'] }}</h3>
                                <p class="text-base text-gray-600 dark:text-gray-400">{{ $certification['issuer'] }}</p>
                                @if($certification['issue_date'] || $certification['credential_id'])
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-300">
                                    @if($certification['issue_date'])
                                        <span>Data di rilascio: {{ $certification['issue_date'] }}</span>
                                    @endif
                                    @if($certification['issue_date'] && $certification['credential_id'])
                                        <span class="mx-2">·</span>
                                    @endif
                                    @if($certification['credential_id'])
                                        <span>ID credenziale: {{ $certification['credential_id'] }}</span>
                                    @endif
                                </p>
                                @endif
                                @if($certification['credential_url'])
                                    <div class="mt-4">
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