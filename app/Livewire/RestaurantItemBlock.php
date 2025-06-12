<?php

namespace App\Livewire;

use Livewire\Component;

class RestaurantItemBlock extends Component
{
    public $restaurants = [];

    public function mount($restaurants = [])
    {
        $search = request()->query('search', '');
        if ($search) {
            $this->restaurants = \App\Models\Restaurant::where('name', 'like', "%{$search}%")->get();
        } else {
            $this->restaurants = $restaurants;
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
