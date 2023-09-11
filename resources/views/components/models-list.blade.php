<section class="text-gray-600 body-font">
    <div class="container py-3 mx-auto grid md:grid-cols-2 md:gap-2 sm:grid-cols-1 sm:gap-1">
        @foreach ($rows as $row)
            <div class="text-center">
                <h2 class="text-xs text-indigo-500 tracking-widest font-medium title-font mb-1">ROOF PARTY POLAROID</h2>
                <h1 class="sm:text-3xl text-2xl font-medium title-font mb-4 text-gray-900">{{ $row->name }}</h1>
                @if (!empty($row->featuredImage))
                    <div class="flex justify-center items-center">
                        <div class="md:w-auto sm:w-full">
                            <x-curator-glider class="w-auto h-64 rounded-lg" :media="$row->featuredImage" />
                        </div>
                    </div>
                @endif
                <div class="mt-5 mb-5">
                    <a href="{{ $row->link }}"
                        class="btn mx-auto text-white bg-green-500 border-0 py-2 px-8 focus:outline-none hover:bg-green-600 rounded text-lg">Visualizza</a>
                </div>
            </div>
        @endforeach
    </div>
</section>
