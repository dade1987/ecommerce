<?php

namespace App\Livewire;

use Livewire\Component;

class RestaurantSearchBlock extends Component
{
    public string $search = '';

    public function cerca()
    {
        $this->js("window.history.replaceState({}, '', '?search=" . urlencode($this->search) . "')");
        $this->dispatch('filterRestaurants', search: $this->search);
    }

    public function render()
    {
        return view('livewire.restaurant-search-block');
    }
}
