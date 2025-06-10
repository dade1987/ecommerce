@aware(['page'])

<!-- Restaurant List -->
<section class="bg-emerald-50">
    <div class="mx-auto grid max-w-6xl grid-cols-1 gap-6 p-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
        @foreach ($restaurants as $row)
            <article class="rounded-xl bg-white p-3 shadow-lg hover:shadow-xl hover:transform hover:scale-105 duration-300">
                <a href="#">
                    <div class="relative flex items-end overflow-hidden rounded-xl">
                        @if(isset($row->featuredImage))
                            <x-curator-glider :media="$row->featuredImage" />
                        @else
                            <div class="w-full h-40 bg-gray-200 flex items-center justify-center text-gray-400">Nessuna immagine</div>
                        @endif
                    </div>
                    <div class="mt-1 p-2">
                        <h2 class="text-slate-700 text-xl font-bold">{{ $row->name }}</h2>
                        <p class="mt-1 text-sm text-slate-400"><span class="font-semibold">Fascia prezzo:</span> {{ $row->price_range ?? '-' }}</p>
                        <p class="mt-1 text-sm text-slate-400"><span class="font-semibold">Telefono:</span> {{ $row->phone_number ?? '-' }}</p>
                        <p class="mt-1 text-sm text-slate-400"><span class="font-semibold">Email:</span> {{ $row->email ?? '-' }}</p>
                        <p class="mt-1 text-sm text-slate-400"><span class="font-semibold">Sito web:</span> 
                            @if($row->website)
                                <a href="{{ $row->website }}" class="text-blue-500 underline" target="_blank">{{ $row->website }}</a>
                            @else
                                -
                            @endif
                        </p>
                    </div>
                </a>
                <div class="mt-4 flex justify-center">
                    <button wire:click="openModal({{ $row->id }})" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded transition">Invita amici su WhatsApp</button>
                </div>
            </article>
        @endforeach
    </div>
    @livewire('invite-friends-modal')
</section>
