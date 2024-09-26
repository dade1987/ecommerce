@aware(['page'])
<nav class="bg-white shadow-lg h-screen fixed top-0 left-0 min-w-[240px] py-6 px-4 font-[sans-serif] overflow-auto">
    @foreach($items as $item)
    <ul>
        <li>
            <a href="{{ $item->href }}"
                class="text-black hover:text-blue-600 text-[15px] block hover:bg-blue-50 rounded px-4 py-2.5 transition-all">
                {{ $item->name }}
            </a>
        </li>
    </ul>
    @endforeach

    
</nav>
