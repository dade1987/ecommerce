<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Collection;

class Cart extends Component
{
    public Collection $items;
    public function mount()
    {
        $this->items = collect([
            [
                'name' => 'lasagne alla norma',
                'price' => number_format(9, 2) . ' €',
                'image' => ''
            ],
            [
                'name' => 'polpette vegetariane',
                'price' => number_format(5, 2) . ' €',
                'image' => ''

            ]
        ]);
    }
    public function render()
    {
        return view('livewire.cart');
    }
}
