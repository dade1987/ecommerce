@aware(['page'])

<section class="bg-white py-16 lg:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        
        <!-- Sezione "Perché AWS?" -->
        <div class="grid grid-cols-1 gap-12 lg:grid-cols-2 lg:items-start lg:gap-16 mb-20">
            
            <!-- Colonna sinistra: Titolo e descrizione -->
            <div class="flex flex-col">
                <h2 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl mb-8">
                    {{ $percheTitle }}
                </h2>
                <div class="prose prose-lg text-gray-600 leading-relaxed" id="perche-description">
                    {!! $percheDescription !!}
                </div>
            </div>

            <!-- Colonna destra: Accordion -->
            <div class="space-y-4">
                @foreach($accordionItems ?? [] as $index => $item)
                    <div class="border border-gray-200 rounded-lg" x-data="{ open: false }">
                        <button 
                            @click="open = !open"
                            class="w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50 transition-colors duration-200"
                        >
                            <div class="flex items-center space-x-3">
                                @if(isset($item['icon']))
                                    <div class="flex-shrink-0">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            @switch($item['icon'])
                                                @case('heroicon-o-server')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                                                    @break
                                                @case('heroicon-o-cloud')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path>
                                                    @break
                                                @case('heroicon-o-shield-check')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                                    @break
                                                @case('heroicon-o-lightning-bolt')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                    @break
                                                @case('heroicon-o-globe-alt')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                                                    @break
                                                @case('heroicon-o-cog')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    @break
                                                @case('heroicon-o-chart-bar')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                                    @break
                                                @case('heroicon-o-database')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                                                    @break
                                                @case('heroicon-o-network')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path>
                                                    @break
                                                @case('heroicon-o-cpu-chip')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                                                    @break
                                                @case('heroicon-o-key')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                                    @break
                                                @case('heroicon-o-lock-closed')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                                    @break
                                                @case('heroicon-o-rocket-launch')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                    @break
                                                @case('heroicon-o-star')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                                    @break
                                                @case('heroicon-o-check-circle')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    @break
                                                @case('heroicon-o-users')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                                    @break
                                                @case('heroicon-o-building-office')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                    @break
                                                @case('heroicon-o-wrench-screwdriver')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.05.58.073 1.194.14 1.743m-.207 3.584a2.548 2.548 0 113.586-3.586 2.548 2.548 0 01-3.586 3.586z"></path>
                                                    @break
                                                @case('heroicon-o-beaker')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                                                    @break
                                                @case('heroicon-o-code-bracket')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5"></path>
                                                    @break
                                                @default
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                                            @endswitch
                                        </svg>
                                    </div>
                                @endif
                                <span class="font-medium text-gray-900">{{ $item['title'] }}</span>
                            </div>
                            <svg 
                                class="w-5 h-5 text-gray-500 transform transition-transform duration-200" 
                                :class="{ 'rotate-45': open }"
                                fill="none" 
                                stroke="currentColor" 
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </button>
                        <div 
                            x-show="open" 
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 transform -translate-y-2"
                            x-transition:enter-end="opacity-100 transform translate-y-0"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 transform translate-y-0"
                            x-transition:leave-end="opacity-0 transform -translate-y-2"
                            class="px-6 pb-4"
                        >
                            <div class="prose prose-sm text-gray-600">
                                {!! $item['content'] !!}
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="mt-8">
                    @livewire('open-calendar-button')
                </div>
            </div>
        </div>

    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const descriptionElement = document.getElementById('perche-description');
    if (!descriptionElement) return;

    const originalContent = descriptionElement.innerHTML;
    const searchTerm = 'READ MORE';
    
    const searchRegex = new RegExp(searchTerm, 'i');
    const match = originalContent.match(searchRegex);
    
    if (!match) return;

    const index = match.index;
    const actualTerm = match[0];
    const endIndex = index + actualTerm.length;
    
    const visibleText = originalContent.slice(0, index);
    const hiddenText = originalContent.slice(endIndex);
    
    descriptionElement.innerHTML = '';

    const visibleSpan = document.createElement('span');
    visibleSpan.innerHTML = visibleText;
    descriptionElement.appendChild(visibleSpan);

    const locale = '{{ app()->getLocale() }}';
    const readMoreText = locale === 'it' ? 'Continua a Leggere...' : 'Read More...';
    const readLessText = locale === 'it' ? 'Leggi di Meno' : 'Read Less';

    const readMoreLink = document.createElement('a');
    readMoreLink.href = '#';
    readMoreLink.className = 'text-blue-600 hover:text-blue-800 font-medium underline cursor-pointer ml-2';
    readMoreLink.innerText = readMoreText;
    descriptionElement.appendChild(readMoreLink);

    const hiddenSpan = document.createElement('span');
    hiddenSpan.innerHTML = hiddenText;
    hiddenSpan.style.display = 'none';
    descriptionElement.appendChild(hiddenSpan);

    const readLessLink = document.createElement('a');
    readLessLink.href = '#';
    readLessLink.className = 'text-blue-600 hover:text-blue-800 font-medium underline cursor-pointer ml-2';
    readLessLink.innerText = readLessText;
    hiddenSpan.appendChild(document.createElement('br'));
    hiddenSpan.appendChild(readLessLink);
    
    readMoreLink.addEventListener('click', function(event) {
        event.preventDefault();
        hiddenSpan.style.display = 'inline';
        readMoreLink.style.display = 'none';
    });

    readLessLink.addEventListener('click', function(event) {
        event.preventDefault();
        hiddenSpan.style.display = 'none';
        readMoreLink.style.display = 'inline';
    });
});
</script>

 