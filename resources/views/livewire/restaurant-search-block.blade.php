@php
    use Filament\Support\Facades\FilamentView;
    $containerClass = FilamentView::hasSpaMode() ? 'fi-modal-window' : '';

    // Impostiamo i valori di default solo se non ci sono valori precedenti
    $selectedDate = old('date', $date ?? date('Y-m-d'));
    $selectedTime = old('time_slot', $time_slot ?? '19:00');
@endphp

<div>
    <div class="mx-auto max-w-6xl p-6">
        {{-- The best athlete wants his opponent at his best. --}}

        <!-- Ricerca e aggiunta ristorante -->
        <div class="flex flex-col gap-2 mb-6">
            <form id="restaurant-search-form" method="GET" action="/restaurants" class="p-4 bg-gray-100 rounded-lg shadow-md" data-maps-key="{{ $googleMapsApiKey }}">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    
                    <!-- Data -->
                    <div class="md:col-span-2">
                        <label for="date" class="block text-xs font-medium text-gray-700">Data</label>
                        <input type="date" name="date" id="date" value="{{ $selectedDate }}" min="{{ date('Y-m-d') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>

                    <!-- Fascia Oraria -->
                    <div class="md:col-span-2">
                        <label for="time_slot" class="block text-xs font-medium text-gray-700">Fascia Oraria</label>
                        <div class="mt-1 relative flex items-center">
                            <select name="time_slot" id="time_slot" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 pr-10 appearance-none">
                                <optgroup label="Pranzo">
                                    @foreach(['12:00', '12:30', '13:00', '13:30', '14:00', '14:30'] as $time)
                                        <option value="{{ $time }}" {{ $selectedTime == $time ? 'selected' : '' }}>{{ $time }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Cena">
                                    @foreach(['19:00', '19:30', '20:00', '20:30', '21:00', '21:30', '22:00'] as $time)
                                        <option value="{{ $time }}" {{ $selectedTime == $time ? 'selected' : '' }}>{{ $time }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.414-1.414L11 9.586V6z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Ricerca per indirizzo -->
                    <div class="md:col-span-2">
                        <label for="search_address" class="block text-xs font-medium text-gray-700">Indirizzo di Partenza</label>
                        <div class="mt-1 relative flex items-center">
                            <input type="text" name="search_address" id="search_address" value="{{ old('search_address', $search_address) }}" placeholder="Cerca per indirizzo..." class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 pr-10">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                        <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude', $latitude) }}">
                        <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude', $longitude) }}">
                    </div>

                    <!-- Ricerca per raggio -->
                    <div class="md:col-span-1">
                        <label for="radius" class="block text-xs font-medium text-gray-700">Raggio (km)</label>
                        <select name="radius" id="radius" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="5" @if(old('radius', $radius) == 5) selected @endif>5 km</option>
                            <option value="10" @if(old('radius', $radius) == 10) selected @endif>10 km</option>
                            <option value="20" @if(old('radius', $radius) == 20) selected @endif>20 km</option>
                            <option value="50" @if(old('radius', $radius) == 50) selected @endif>50 km</option>
                        </select>
                    </div>

                    <!-- Ricerca per nome -->
                    <div class="md:col-span-1">
                        <label for="search" class="block text-xs font-medium text-gray-700">Nome Ristorante</label>
                        <div class="mt-1 relative flex items-center">
                            <input type="text" name="search" id="search" value="{{ old('search', $search) }}" placeholder="Cerca per nome..." class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 pr-10">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M6 2v7c0 1.1.9 2 2 2h2a2 2 0 0 0 2-2V2M8 2v20"/>
                                    <path d="M16 2v20c0 .55.45 1 1 1h1a1 1 0 0 0 1-1V2"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pulsanti di ricerca e aggiunta -->
                    <div class="md:col-span-4 flex justify-between items-center mt-4">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                            Cerca
                        </button>
                        {{ $this->addRestaurantAction }}
                    </div>
                </div>
            </form>
        </div>

        @if(count($restaurants ?? []) > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($restaurants as $restaurant)
                <div class="bg-white rounded-lg shadow-md p-4">
                    <h3 class="text-lg font-medium mb-2">{{ $restaurant->name }}</h3>
                    <p class="text-gray-500">{{ $restaurant->address }}</p>
                </div>
            @endforeach
        </div>
        @endif
    </div>

    <x-filament-actions::modals />

    @script
    <script>
        window.initMapAutocomplete = function() {
            const addressInput = document.getElementById('search_address');
            const form = document.getElementById('restaurant-search-form');
            
            if (!addressInput || !form) {
                return;
            }

            const autocomplete = new google.maps.places.Autocomplete(addressInput, {
                types: ['address'],
                componentRestrictions: {'country': 'it'}
            });

            autocomplete.addListener('place_changed', () => {
                const place = autocomplete.getPlace();

                if (place.geometry && place.geometry.location) {
                    document.getElementById('latitude').value = place.geometry.location.lat();
                    document.getElementById('longitude').value = place.geometry.location.lng();
                } else {
                    document.getElementById('latitude').value = '';
                    document.getElementById('longitude').value = '';
                }
            });
            
            addressInput.addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                }
            });
        }

        function loadGoogleMapsApi() {
            const form = document.getElementById('restaurant-search-form');
            if (!form) return;

            if (typeof google === 'undefined' || typeof google.maps === 'undefined' || typeof google.maps.places === 'undefined') {
                const script = document.createElement('script');
                const apiKey = form.dataset.mapsKey;
                
                if (!apiKey) {
                    console.error('Google Maps API key not found.');
                    return;
                }

                script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=places&callback=initMapAutocomplete`;
                script.async = true;
                script.defer = true;
                document.head.appendChild(script);
                
                script.onerror = function() {
                    console.error("Google Maps script could not be loaded.");
                };
            } else {
                window.initMapAutocomplete();
            }
        }

        document.addEventListener('livewire:navigated', loadGoogleMapsApi);
        loadGoogleMapsApi();

        function initModalAutocomplete() {
            const addressInput = document.getElementById('modal_address_search');
            if (!addressInput) {
                return;
            }

            // Adding a guard to prevent re-initialization
            if (addressInput.dataset.autocompleteInitialized) {
                return;
            }
            addressInput.dataset.autocompleteInitialized = 'true';

            const autocomplete = new google.maps.places.Autocomplete(addressInput, {
                types: ['address'],
                componentRestrictions: { 'country': 'it' }
            });

            autocomplete.addListener('place_changed', () => {
                const place = autocomplete.getPlace();
                if (!place.geometry || !place.address_components) {
                    return;
                }

                const componentForm = {
                    street_number: 'long_name',
                    route: 'long_name',
                    locality: 'long_name',
                    administrative_area_level_2: 'short_name',
                    administrative_area_level_1: 'long_name',
                    country: 'long_name',
                    postal_code: 'long_name',
                };

                const addressComponents = {};
                for (const component of place.address_components) {
                    const addressType = component.types[0];
                    if (componentForm[addressType]) {
                        addressComponents[addressType] = component[componentForm[addressType]];
                    }
                }
                
                const form = addressInput.closest('form');
                if (!form) return;

                const updateInputValue = (name, value) => {
                    const input = form.querySelector(`[name='data[${name}]']`);
                    if (input) {
                        input.value = value;
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                };
                
                updateInputValue('street', `${addressComponents.street_number || ''} ${addressComponents.route || ''}`.trim());
                updateInputValue('municipality', addressComponents.locality || '');
                updateInputValue('province', addressComponents.administrative_area_level_2 || '');
                updateInputValue('region', addressComponents.administrative_area_level_1 || '');
                updateInputValue('nation', addressComponents.country || '');
                updateInputValue('postal_code', addressComponents.postal_code || '');
                updateInputValue('latitude', place.geometry.location.lat());
                updateInputValue('longitude', place.geometry.location.lng());
            });
        }

        window.addEventListener('open-modal', () => {
            requestAnimationFrame(() => {
                setTimeout(initModalAutocomplete, 50);
            });
        });
    </script>
    @endscript
</div>
