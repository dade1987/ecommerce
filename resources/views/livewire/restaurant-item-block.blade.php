@aware(['page'])

<!-- Restaurant List -->
<section class="bg-white">
    <div class="mx-auto max-w-6xl p-6">
        <div class="divide-y divide-gray-200">
            @foreach ($restaurants as $row)
                @php
                    $address = $row->addresses->first();
                @endphp
                <article class="flex items-start space-x-4 md:space-x-8 py-6 px-2 hover:bg-gray-50 transition-colors duration-200">
                    
                    <!-- Image -->
                    <div class="flex-shrink-0">
                        <div class="h-16 w-16 md:h-24 md:w-24 rounded-full overflow-hidden bg-gray-200 flex items-center justify-center shadow">
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
                        <div class="flex flex-col md:flex-row justify-between md:items-start">
                            <div class="flex-1">
                                <a href="#">
                                    <h2 class="text-lg md:text-xl font-bold text-slate-800 truncate">{{ $row->name }}</h2>
                                </a>
                                <div class="mt-2 text-xs md:text-sm text-slate-600">
                                    @if($address)
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
                                            <div class="space-y-1">
                                                <p class="truncate"><span class="font-semibold inline-block w-14">Via:</span> {{ $address->street ?? '-' }}</p>
                                                <p class="truncate"><span class="font-semibold inline-block w-14">Comune:</span> {{ $address->municipality ?? '-' }}</p>
                                                <p class="truncate"><span class="font-semibold inline-block w-14">Nazione:</span> {{ $address->nation ?? '-' }}</p>
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

                            <!-- Invite Button and Price for Mobile -->
                            <div class="mt-4 md:hidden flex-shrink-0 flex flex-row items-center justify-between">
                                <button wire:click="openModal({{ $row->id }})" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-full transition whitespace-nowrap">Invita</button>
                                <div class="text-sm font-semibold text-slate-700 ml-4">
                                    <span class="font-semibold">Prezzo:</span> {{ $row->price_range ?? '-' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Invite Button and Price for Desktop -->
                    <div class="hidden md:flex flex-shrink-0 flex-col items-end space-y-4">
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
