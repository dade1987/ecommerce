<div class="mx-auto max-w-6xl p-6">
    {{-- The best athlete wants his opponent at his best. --}}

    <!-- Ricerca e aggiunta ristorante -->
    <div class="flex flex-col gap-2 mb-6">
        <form id="restaurant-search-form" method="GET" action="" class="p-4 bg-gray-100 rounded-lg shadow-md" data-maps-key="{{ $googleMapsApiKey }}">
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
                    <button type="button" wire:click="openCreateModal" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Aggiungi Ristorante
                    </button>
                </div>
            </div>
        </form>
    </div>

    @if($showCreateModal)
        <div class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50">
            <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-4xl relative">
                <button wire:click="closeCreateModal" class="absolute top-2 right-2 text-gray-500 hover:text-gray-800">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <h2 class="text-xl font-bold mb-4">Aggiungi Ristorante</h2>
                <form wire:submit.prevent="salvaRistorante">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-medium mb-4">Dati Ristorante</h3>
                            {{ $this->form }}
                        </div>
                        <div>
                            <h3 class="text-lg font-medium mb-4">Indirizzo</h3>
                            {{ $this->addressForm }}
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 mt-6">
                        <button type="button" wire:click="closeCreateModal" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                            Annulla
                        </button>
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                            Salva Ristorante
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

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
</script>
