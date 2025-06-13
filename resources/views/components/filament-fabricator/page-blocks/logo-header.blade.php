@aware(['page'])
<header class="p-4 bg-white text-gray-800">
    <div class="container h-24 mx-auto flex justify-center items-center">
        <a rel="noopener noreferrer" href="/" aria-label="Homepage" class="flex items-center p-2">
            <img class="object-cover h-28 w-auto rounded-full @if($logoBorder) border-2 border-blue-700 @endif" src="{{ $logoUrl }}" alt="Logo" />
        </a>
    </div>
</header> 