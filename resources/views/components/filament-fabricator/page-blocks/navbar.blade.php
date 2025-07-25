@aware(['page'])
<header class="p-4 bg-white text-gray-800">
    <div class="container flex justify-between h-24 mx-auto">
        <a rel="noopener noreferrer" href="#" aria-label="Back to homepage" class="flex items-center p-2">
            <img class="object-cover h-16 w-auto rounded-full @if($logoBorder===true) border-2 border-blue-700 @endif " src="{{ $logoUrl }}" alt="Logo" />
        </a>
        <ul id="menu-links" class="items-stretch hidden space-x-3 lg:flex flex-col lg:flex-row lg:items-center lg:space-x-3 lg:space-y-0 space-y-2 lg:space-y-0">
           
            @foreach($items as $item)
                <li class="flex">
                    <a rel="noopener noreferrer" href="{{ $item->href }}"
                        class="text-xl font-bold flex items-center px-4 -mb-1 text-black border-black">{{ $item->name }}</a>
                </li>
            @endforeach
            
            <!-- Language Selector Widget -->
            <li class="flex items-center space-x-2 ml-4">
                @php
                    $isEn = request()->getHost() == 'en.cavalliniservice.com';
                @endphp
                <div class="language-selector flex items-center space-x-2">
                    <a href="https://cavalliniservice.com" class="language-btn flex items-center p-2 rounded-md transition-colors @if(!$isEn) bg-blue-100 border-blue-300 @else hover:bg-gray-100 @endif">
                        <svg class="w-6 h-6" viewBox="0 0 640 480" xmlns="http://www.w3.org/2000/svg">
                            <path fill="#009246" d="M0 0h213.3v480H0z"/>
                            <path fill="#FFF" d="M213.3 0h213.4v480H213.3z"/>
                            <path fill="#CE2B37" d="M426.7 0H640v480H426.7z"/>
                        </svg>
                        <span class="ml-1 text-sm font-medium">IT</span>
                    </a>
                    <a href="https://en.cavalliniservice.com" class="language-btn flex items-center p-2 rounded-md transition-colors @if($isEn) bg-blue-100 border-blue-300 @else hover:bg-gray-100 @endif">
                        <svg class="w-6 h-6" viewBox="0 0 640 480" xmlns="http://www.w3.org/2000/svg">
                            <path fill="#012169" d="M0 0h640v480H0z"/>
                            <path fill="#FFF" d="m75 0 244 181L562 0h78v62L400 241l240 178v61h-80L320 301 81 480H0v-60l239-178L0 64V0h75z"/>
                            <path fill="#C8102E" d="m424 281 216 159v40L369 281h55zm-184 20 6 35L54 480H0l246-179zM640 0v3L391 191l2-44L590 0h50zM0 0l239 176h-60L0 42V0z"/>
                            <path fill="#FFF" d="M241 0v480h160V0H241zM0 160v160h640V160H0z"/>
                            <path fill="#C8102E" d="M0 193v96h640v-96H0zM273 0v480h96V0h-96z"/>
                        </svg>
                        <span class="ml-1 text-sm font-medium">EN</span>
                    </a>
                </div>
            </li>
        </ul>
        <div class="items-center flex-shrink-0 hidden lg:flex">
            {{--@guest
                <a href="{{ route('login') }}" class="self-center px-8 py-3 rounded text-xl font-bold text-black">Accedi</a>
                <a href="{{ route('register') }}"
                    class="self-center px-8 py-3 font-semibold rounded bg-violet-400 text-gray-900 text-xl font-bold text-black">Registrati</a>
            @endguest
            @auth
                @if ($cartEnabled === true)
                    @livewire('cart-icon')
                @else
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type='submit' class="text-xl font-bold text-black">Esci</button>
                    </form>
                @endif
            @endauth--}}
        </div>
        <button id="menu-button" class="p-4 lg:hidden">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                class="w-6 h-6 text-gray-800">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16">
                </path>
            </svg>
        </button>
    </div>
    <div id="mobile-menu" class="lg:hidden hidden">
        <ul class="flex flex-col items-start space-y-2 p-4 bg-gray-100 rounded-md shadow-md">
            @foreach($items as $item)
                <li class="w-full">
                    <a rel="noopener noreferrer" href="{{ $item->href }}"
                        class="block w-full text-xl font-bold px-4 py-2 rounded-md hover:bg-gray-200 text-black">{{ $item->name }}</a>
                </li>
            @endforeach
            
            <!-- Language Selector Widget - Mobile -->
            <li class="w-full pt-2 border-t border-gray-300">
                <div class="language-selector flex justify-center items-center space-x-3">
                    <a href="https://cavalliniservice.com" class="language-btn flex items-center p-2 rounded-md transition-colors @if(!$isEn) bg-blue-100 border-blue-300 @else hover:bg-gray-100 @endif">
                        <svg class="w-6 h-6" viewBox="0 0 640 480" xmlns="http://www.w3.org/2000/svg">
                            <path fill="#009246" d="M0 0h213.3v480H0z"/>
                            <path fill="#FFF" d="M213.3 0h213.4v480H213.3z"/>
                            <path fill="#CE2B37" d="M426.7 0H640v480H426.7z"/>
                        </svg>
                        <span class="ml-1 text-sm font-medium">IT</span>
                    </a>
                    <a href="https://en.cavalliniservice.com" class="language-btn flex items-center p-2 rounded-md transition-colors @if($isEn) bg-blue-100 border-blue-300 @else hover:bg-gray-100 @endif">
                        <svg class="w-6 h-6" viewBox="0 0 640 480" xmlns="http://www.w3.org/2000/svg">
                            <path fill="#012169" d="M0 0h640v480H0z"/>
                            <path fill="#FFF" d="m75 0 244 181L562 0h78v62L400 241l240 178v61h-80L320 301 81 480H0v-60l239-178L0 64V0h75z"/>
                            <path fill="#C8102E" d="m424 281 216 159v40L369 281h55zm-184 20 6 35L54 480H0l246-179zM640 0v3L391 191l2-44L590 0h50zM0 0l239 176h-60L0 42V0z"/>
                            <path fill="#FFF" d="M241 0v480h160V0H241zM0 160v160h640V160H0z"/>
                            <path fill="#C8102E" d="M0 193v96h640v-96H0zM273 0v480h96V0h-96z"/>
                        </svg>
                        <span class="ml-1 text-sm font-medium">EN</span>
                    </a>
                </div>
            </li>
        </ul>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const menuButton = document.getElementById('menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        menuButton.addEventListener('click', function () {
            mobileMenu.classList.toggle('hidden');
        });
    });
</script>
