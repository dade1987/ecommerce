@props(['back_to_shop_link'])

<section>
    <div class="container py-3 mx-auto w-full">
        <div class="text-center py-3 mx-auto">
            <div class="flex flex-col p-6 space-y-4 sm:p-10 dark:bg-gray-900 dark:text-gray-100">
                <h2 class="text-xl font-semibold">Your cart</h2>
                <ul class="flex flex-col divide-y divide-gray-700">
                    @foreach ($items as $item)
                        <li class="flex flex-col py-6 sm:flex-row sm:justify-between">
                            <div class="flex w-full space-x-2 sm:space-x-4">
                                <x-curator-glider
                                    class="flex-shrink-0 object-cover w-20 h-20 dark:border-transparent rounded outline-none sm:w-32 sm:h-32 dark:bg-gray-500"
                                    :media="$item['options']['featured_image_id']" />
                                <div class="flex flex-col justify-between w-full pb-4">
                                    <div class="flex justify-between w-full pb-2 space-x-2">
                                        <div class="space-y-1">
                                            <h3 class="text-lg font-semibold leadi sm:pr-8">{{ $item['name'] }}</h3>
                                            {{-- <p class="text-sm dark:text-gray-400">Classic</p> --}}
                                        </div>
                                        <div class="text-right">
                                            <p class="text-lg font-semibold">
                                                € {{ number_format($item['quantity'] * $item['price'], 2) }}
                                            </p>
                                            {{-- <p class="text-sm dark:text-gray-600">{{ $item['quantity'] }} x €
                                                {{ number_format($item['price'], 2) }}</p> --}}
                                        </div>
                                    </div>
                                    <div class="flex text-sm divide-x">
                                        <button
                                            wire:click="$dispatch('remove-from-cart', {product_id: '{{ $item['rowId'] }}'})"
                                            type="button" class="flex items-center px-2 py-1 pl-0 space-x-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"
                                                class="w-4 h-4 fill-current">
                                                <path
                                                    d="M96,472a23.82,23.82,0,0,0,23.579,24H392.421A23.82,23.82,0,0,0,416,472V152H96Zm32-288H384V464H128Z">
                                                </path>
                                                <rect width="32" height="200" x="168" y="216">
                                                </rect>
                                                <rect width="32" height="200" x="240" y="216">
                                                </rect>
                                                <rect width="32" height="200" x="312" y="216">
                                                </rect>
                                                <path
                                                    d="M328,88V40c0-13.458-9.488-24-21.6-24H205.6C193.488,16,184,26.542,184,40V88H64v32H448V88ZM216,48h80V88H216Z">
                                                </path>
                                            </svg>
                                            <span>Remove</span>
                                        </button>
                                        <button type="button" class="flex items-center px-2 py-1 space-x-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                              </svg>                                             
                                            <span>Add Notes</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </li>
                        @foreach ($item['subItems'] as $subitem)
                            <li class="flex flex-col py-6 sm:flex-row sm:justify-between">
                                <div class="flex w-full space-x-2 sm:space-x-4">
                                    <x-curator-glider
                                        class="flex-shrink-0 object-cover w-20 h-20 dark:border-transparent rounded outline-none sm:w-32 sm:h-32 dark:bg-gray-500"
                                        :media="$subitem['options']['featured_image_id']" />
                                    <div class="flex flex-col justify-between w-full pb-4">
                                        <div class="flex justify-between w-full pb-2 space-x-2">
                                            <div class="space-y-1">
                                                <h3 class="text-lg font-semibold leadi sm:pr-8">
                                                    {{ $subitem['options']['operation'] }} {{ $subitem['name'] }}
                                                </h3>
                                                {{-- <p class="text-sm dark:text-gray-400">Classic</p> --}}
                                            </div>
                                            <div class="text-right">
                                                <p class="text-lg font-semibold">€
                                                    {{ number_format($subitem['price'], 2) }}</p>
                                                {{-- <p class="text-sm line-through dark:text-gray-600">75.50€</p> --}}
                                            </div>
                                        </div>
                                        <div class="flex text-sm divide-x">
                                            <button
                                                wire:click="$dispatch('remove-from-cart', {product_id: '{{ $subitem['rowId'] }}'})"
                                                type="button" class="flex items-center px-2 py-1 pl-0 space-x-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"
                                                    class="w-4 h-4 fill-current">
                                                    <path
                                                        d="M96,472a23.82,23.82,0,0,0,23.579,24H392.421A23.82,23.82,0,0,0,416,472V152H96Zm32-288H384V464H128Z">
                                                    </path>
                                                    <rect width="32" height="200" x="168" y="216">
                                                    </rect>
                                                    <rect width="32" height="200" x="240" y="216">
                                                    </rect>
                                                    <rect width="32" height="200" x="312" y="216">
                                                    </rect>
                                                    <path
                                                        d="M328,88V40c0-13.458-9.488-24-21.6-24H205.6C193.488,16,184,26.542,184,40V88H64v32H448V88ZM216,48h80V88H216Z">
                                                    </path>
                                                </svg>
                                                <span>Remove</span>
                                            </button>
                                            <button type="button" class="flex items-center px-2 py-1 space-x-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"
                                                    class="w-4 h-4 fill-current">
                                                    <path
                                                        d="M453.122,79.012a128,128,0,0,0-181.087.068l-15.511,15.7L241.142,79.114l-.1-.1a128,128,0,0,0-181.02,0l-6.91,6.91a128,128,0,0,0,0,181.019L235.485,449.314l20.595,21.578.491-.492.533.533L276.4,450.574,460.032,266.94a128.147,128.147,0,0,0,0-181.019ZM437.4,244.313,256.571,425.146,75.738,244.313a96,96,0,0,1,0-135.764l6.911-6.91a96,96,0,0,1,135.713-.051l38.093,38.787,38.274-38.736a96,96,0,0,1,135.765,0l6.91,6.909A96.11,96.11,0,0,1,437.4,244.313Z">
                                                    </path>
                                                </svg>
                                                <span>Add to favorites</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    @endforeach


                </ul>
                <div class="space-y-1 text-right">
                    <p>Total amount:
                        <span class="font-semibold">€ {{ number_format($total, 2) }}</span>
                    </p>
                    <p class="text-sm dark:text-gray-400">Not including taxes and shipping costs</p>
                </div>
                <div class="flex justify-end space-x-4">
                    <a href="{{ url($back_to_shop_link) }}" type="button"
                        class="px-6 py-2 border rounded-md dark:border-violet-400">Back
                        <span class="sr-only sm:not-sr-only">to shop</span>
                    </a>
                    <a href="{{ url($next_link) }}" type="button"
                        class="px-6 py-2 border rounded-md dark:bg-violet-400 dark:text-gray-900 dark:border-violet-400">
                        <span class="sr-only sm:not-sr-only">Continue to</span> Checkout
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
