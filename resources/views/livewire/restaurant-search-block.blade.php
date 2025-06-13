@php
    use Filament\Support\Facades\FilamentView;
    $containerClass = FilamentView::hasSpaMode() ? 'fi-modal-window' : '';
@endphp

<div>
    <div class="mx-auto max-w-6xl p-6">
        {{-- The best athlete wants his opponent at his best. --}}

        <!-- Ricerca e aggiunta ristorante -->
        <div class="flex flex-col gap-2 mb-6">
            <form id="restaurant-search-form" method="GET" action="/restaurants" class="p-4 bg-gray-100 rounded-lg shadow-md" data-maps-key="{{ $googleMapsApiKey }}">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <!-- Ricerca per nome -->
                    <div class="md:col-span-1">
                        <label for="search" class="block text-sm font-medium text-gray-700">Nome Ristorante</label>
                        <input type="text" name="search" id="search" value="{{ $search }}" placeholder="Cerca per nome..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>

                    <!-- Ricerca per indirizzo -->
                    <div class="md:col-span-2">
                        <label for="search_address" class="block text-sm font-medium text-gray-700">Indirizzo</label>
                        <input type="text" name="search_address" id="search_address" value="{{ $search_address }}" placeholder="Cerca per indirizzo..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <input type="hidden" name="latitude" id="latitude" value="{{ $latitude }}">
                        <input type="hidden" name="longitude" id="longitude" value="{{ $longitude }}">
                    </div>

                    <!-- Ricerca per raggio -->
                    <div class="md:col-span-1">
                        <label for="radius" class="block text-sm font-medium text-gray-700">Raggio (km)</label>
                        <select name="radius" id="radius" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="5" @if($radius == 5) selected @endif>5 km</option>
                            <option value="10" @if($radius == 10) selected @endif>10 km</option>
                            <option value="20" @if($radius == 20) selected @endif>20 km</option>
                            <option value="50" @if($radius == 50) selected @endif>50 km</option>
                        </select>
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
        function initMapAutocomplete() {
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
                initMapAutocomplete();
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
