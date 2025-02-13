@aware(['page'])
<header class="p-4 bg-white text-gray-800">
    <div class="container flex justify-between h-24 mx-auto">
        <a rel="noopener noreferrer" href="#" aria-label="Back to homepage" class="flex items-center p-2">
            <img class="object-cover h-16 w-auto rounded-full @if($logoBorder===true) border-2 border-blue-700 @endif " src="{{ $logoUrl }}" />
        </a>
        <ul id="menu-links" class="items-stretch hidden space-x-3 lg:flex flex-col lg:flex-row lg:items-center lg:space-x-3 lg:space-y-0 space-y-2 lg:space-y-0">
           
            @foreach($items as $item)
                <li class="flex">
                    <a rel="noopener noreferrer" href="{{ $item->href }}"
                        class="text-xl font-bold flex items-center px-4 -mb-1 border-b-2 text-black border-black">{{ $item->name }}</a>
                </li>
            @endforeach
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
