<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Restaurant;
use Illuminate\Support\Facades\DB;

class RestaurantItemBlock extends Component
{
    public $restaurants = [];

    public function mount($restaurants = [])
    {
        $search = request()->query('search');
        $latitude = request()->query('latitude');
        $longitude = request()->query('longitude');
        $radius = request()->query('radius', 10); // Default 10km

        $query = Restaurant::query();

        if ($latitude && $longitude) {

            $haversine = "(
                6371 * acos(
                    cos(radians(?))
                    * cos(radians(addresses.latitude))
                    * cos(radians(addresses.longitude) - radians(?))
                    + sin(radians(?))
                    * sin(radians(addresses.latitude))
                )
            )";

            $query->join('address_morph', 'restaurants.id', '=', 'address_morph.model_id')
                ->join('addresses', 'address_morph.address_id', '=', 'addresses.id')
                ->where('address_morph.model_type', Restaurant::class)
                ->select('restaurants.*')
                ->selectRaw("{$haversine} AS distance", [$latitude, $longitude, $latitude])
                ->whereRaw("{$haversine} < ?", [$latitude, $longitude, $latitude, $radius])
                ->orderBy('distance');
        }

        if ($search) {
            $query->where('restaurants.name', 'like', "%{$search}%");
        }

        // Se non ci sono filtri, carica i ristoranti passati come parametro (comportamento originale)
        if (!$search && !($latitude && $longitude)) {
            $this->restaurants = $restaurants;
        } else {
            $this->restaurants = $query->get();
        }
    }

    public function render()
    {
        return view('livewire.restaurant-item-block');
    }

    public function openModal($restaurantId)
    {
        $this->dispatch('openInviteFriendsModal', params: ['restaurantId' => $restaurantId]);
    }
}
