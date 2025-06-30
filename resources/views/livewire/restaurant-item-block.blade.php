@aware(['page'])

<!-- Restaurant List -->
<section class="bg-white">
    <div class="mx-auto max-w-6xl p-6">
        <div class="divide-y divide-gray-200">
            @foreach ($restaurants as $row)
                @php
                    $address = $row->addresses->first();
                @endphp
                <article class="flex items-center space-x-8 py-6 px-2 hover:bg-gray-50 transition-colors duration-200">
                    
                    <!-- Image -->
                    <div class="flex-shrink-0">
                        <div class="h-24 w-24 rounded-full overflow-hidden bg-gray-200 flex items-center justify-center shadow">
                            @if(isset($row->featuredImage))
                                <x-curator-glider :media="$row->featuredImage" class="h-full w-full object-cover" />
                            @else
                                <svg class="h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            @endif
                        </div>
                    </div>

                    <!-- Details Column -->
                    <div class="flex-1 min-w-0">
                        <a href="#">
                            <h2 class="text-xl font-bold text-slate-800 truncate">{{ $row->name }}</h2>
                        </a>
                        <div class="mt-3 text-sm text-slate-600">
                            @if($address)
                                <div class="grid grid-cols-2 gap-x-6">
                                    <div class="space-y-1">
                                        <p class="truncate"><span class="font-semibold inline-block w-16">Via:</span> {{ $address->street ?? '-' }}</p>
                                        <p class="truncate"><span class="font-semibold inline-block w-16">Comune:</span> {{ $address->municipality ?? '-' }}</p>
                                        <p class="truncate"><span class="font-semibold inline-block w-16">Nazione:</span> {{ $address->nation ?? '-' }}</p>
                                    </div>
                                    <div class="space-y-1">
                                        <p class="truncate"><span class="font-semibold inline-block w-12">Prov:</span> {{ $address->province ?? '-' }}</p>
                                        <p class="truncate"><span class="font-semibold inline-block w-12">CAP:</span> {{ $address->postal_code ?? '-' }}</p>
                                    </div>
                                </div>
                            @else
                                <p class="pt-2">Indirizzo non disponibile</p>
                            @endif
                        </div>
                    </div>

                    <!-- Invite Button and Price -->
                    <div class="flex-shrink-0 flex flex-col items-end space-y-4">
                        <button wire:click="openModal({{ $row->id }})" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-full transition whitespace-nowrap">Invita</button>
                        <div class="text-md font-semibold text-slate-700">
                           <span class="font-semibold">Prezzo:</span> {{ $row->price_range ?? '-' }}
                        </div>
                    </div>

                </article>
            @endforeach
        </div>
    </div>
    @livewire('invite-friends-modal')
</section>
