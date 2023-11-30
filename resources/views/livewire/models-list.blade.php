<section class="text-gray-600 body-font">
    <div class="container py-3 mx-auto grid md:grid-cols-2 md:gap-2 sm:grid-cols-1 sm:gap-1">
        @foreach ($rows as $row)
            <div wire:key="{{ $row->id }}" class="text-center">
                <h2 class="text-xs text-indigo-500 tracking-widest font-medium title-font mb-1">SELEZIONARE</h2>
                <h1 class="sm:text-3xl text-2xl font-medium title-font mb-4 text-gray-900">{{ $row->name }}</h1>
                @if (!empty($row->featuredImage))
                    <div class="flex justify-center items-center">
                        <div class="md:w-auto sm:w-full">
                            <x-curator-glider class="w-auto h-64 rounded-lg" :media="$row->featuredImage" />
                        </div>
                    </div>
                @endif
                @if ($row->price)
                    <h1 class="sm:text-3xl text-2xl font-medium title-font mt-4 mb-4 text-gray-900">Price: €
                        {{ $row->price }}</h1>
                @endif

                @if ($row->ingredients && !$row->ingredients->isEmpty())
                    <div class="flex justify-center items-center">
                        <div class="md:w-4/6 sm:w-full bg-white border">
                            <div class="mt-5 mb-5 ">
                                <strong>{{ $row->ingredients->pluck('name')->join(', ') }}</strong>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="mt-5 mb-5">

                    <button {!! $row->action !!}
                        class="btn mx-auto text-white bg-blue-500 border-0 py-2 px-8 focus:outline-none hover:bg-blue-600 rounded text-lg">{{ $row->action_text }}</button>

                    @if ($second_button === true)
                        <button {!! $row->second_action !!}
                            class="btn mx-auto text-white bg-blue-500 border-0 py-2 px-8 focus:outline-none hover:bg-blue-600 rounded text-lg">{{ $row->second_action_text }}</button>
                    @endif



                </div>
            </div>
        @endforeach
    </div>

</section>
