@aware(['page'])

<!-- Restaurant List -->
<section class="bg-white">
    <div class="mx-auto max-w-6xl p-6">
        <div class="divide-y divide-gray-200">
            @foreach ($restaurants as $row)
                <article class="flex items-center space-x-4 p-4 hover:bg-gray-50 transition-colors duration-200">
                    <a href="#" class="flex flex-1 items-center space-x-4 min-w-0">
                        <div class="flex-shrink-0">
                            <div class="h-14 w-14 rounded-full overflow-hidden bg-gray-200 flex items-center justify-center">
                                @if(isset($row->featuredImage))
                                    <x-curator-glider :media="$row->featuredImage" class="h-full w-full object-cover" />
                                @else
                                    <svg class="h-8 w-8 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                @endif
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h2 class="text-lg font-bold text-slate-800 truncate">{{ $row->name }}</h2>
                            <div class="mt-1 text-sm text-slate-500">
                                <p class="truncate"><span class="font-semibold inline-block w-14">Tel:</span> {{ $row->phone_number ?? '-' }}</p>
                                <p class="truncate"><span class="font-semibold inline-block w-14">Email:</span> {{ $row->email ?? '-' }}</p>
                                <p class="truncate"><span class="font-semibold inline-block w-14">Web:</span>
                                    @if($row->website)
                                        <a href="{{ $row->website }}" class="text-blue-500 underline" target="_blank">{{ Illuminate\Support\Str::limit($row->website, 30) }}</a>
                                    @else
                                        -
                                    @endif
                                </p>
                                <p class="truncate"><span class="font-semibold inline-block w-14">Prezzo:</span> {{ $row->price_range ?? '-' }}</p>
                            </div>
                        </div>
                    </a>
                    <div class="flex-shrink-0">
                        <button wire:click="openModal({{ $row->id }})" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-full transition">Invita</button>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
    @livewire('invite-friends-modal')
</section>
