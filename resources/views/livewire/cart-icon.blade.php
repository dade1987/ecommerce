<div class="flex items-center">
    <a href="{{ route('{item2?}.index', ['container0' => 'cart']) }}" class="flex items-center">
        <span class="text">{{ $count }}</span>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
            class="w-7 h-7">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
        </svg>
    </a>

    <x-filament::modal id="add-variations">
        {{-- Modal content --}}
        @if ($product)
            <h4 class="text-2xl font-bold dark:text-white">Prodotto Aggiunto Correttamente</h4>
            <h5 class="text-xl font-bold dark:text-white">Vuoi aggiungere qualcosa?</h5>

            <div
                class="w-full text-gray-900 bg-white  rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">

                @foreach ($product->variations as $item)
                    <button type="button"
                        class="mb-1 border border-gray-200 relative inline-flex items-center w-3/5 px-4 py-2 text-sm font-medium border-b border-gray-200 rounded-lg hover:bg-gray-100 hover:text-blue-700 dark:border-gray-600 dark:hover:bg-gray-600 dark:hover:text-white dark:focus:ring-gray-500 dark:focus:text-white">
                        <svg class="w-3 h-3 mr-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M10 0a10 10 0 1 0 10 10A10.011 10.011 0 0 0 10 0Zm0 5a3 3 0 1 1 0 6 3 3 0 0 1 0-6Zm0 13a8.949 8.949 0 0 1-4.951-1.488A3.987 3.987 0 0 1 9 13h2a3.987 3.987 0 0 1 3.951 3.512A8.949 8.949 0 0 1 10 18Z" />
                        </svg>
                        <span>{{ $item->name }}</span>
                    </button>
                    <button
                        wire:click="$dispatch('add-item-to-cart', { product_id: '{{ $item->id }}', parent_product_id: '{{ $product->id }}', option: 'No' })"
                        type="button"
                        class="text-white bg-red-700 hover:bg-red-800 focus:outline-none focus:ring-4 focus:ring-red-300 font-medium rounded-full text-sm px-5 py-2.5 text-center mr-2 mb-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900">No</button>
                    <button
                        wire:click="$dispatch('add-item-to-cart', { product_id: '{{ $item->id }}', parent_product_id: '{{ $product->id }}', option: '+' })"
                        type="button"
                        class="text-white bg-green-700 hover:bg-green-800 focus:outline-none focus:ring-4 focus:ring-green-300 font-medium rounded-full text-sm px-5 py-2.5 text-center mr-2 mb-2 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-900">+</button>
                @endforeach
            </div>

            <button wire:click="closeVariantsModal()" type="button"
                class="text-white bg-green-700 hover:bg-green-800 focus:outline-none focus:ring-4 focus:ring-green-300 font-medium rounded-full text-sm px-5 py-2.5 text-center mr-2 mb-2 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-900">Procedi con l'Ordine</button>


        @endif
    </x-filament::modal>

    <x-filament::modal id="ingredients-list">
        {{-- Modal content --}}
        @if ($product)
            <h4 class="text-2xl font-bold dark:text-white">Lista Ingredienti</h4>

            <div
                class="w-full text-gray-900 bg-white  rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">

                @foreach ($product->ingredients as $item)
                    <button type="button"
                        class="mb-1 border border-gray-200 relative inline-flex items-center w-full px-4 py-2 text-sm font-medium border-b border-gray-200 rounded-lg hover:bg-gray-100 hover:text-blue-700 dark:border-gray-600 dark:hover:bg-gray-600 dark:hover:text-white dark:focus:ring-gray-500 dark:focus:text-white">
                        <svg class="w-3 h-3 mr-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M10 0a10 10 0 1 0 10 10A10.011 10.011 0 0 0 10 0Zm0 5a3 3 0 1 1 0 6 3 3 0 0 1 0-6Zm0 13a8.949 8.949 0 0 1-4.951-1.488A3.987 3.987 0 0 1 9 13h2a3.987 3.987 0 0 1 3.951 3.512A8.949 8.949 0 0 1 10 18Z" />
                        </svg>
                        <span>{{ $item->name }}</span>
                    </button>
                @endforeach
            </div>

            <button wire:click="closeIngredientsModal()" type="button"
                class="text-white bg-red-700 hover:bg-red-800 focus:outline-none focus:ring-4 focus:ring-red-300 font-medium rounded-full text-sm px-5 py-2.5 text-center mr-2 mb-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900">Chiudi</button>


        @endif
    </x-filament::modal>
</div>
