<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Collection;

class Cart extends Component
{
    public Collection $items;
    public function mount(){
        $this->items=collect([
            [
              'name' => 'lasagne alla norma',
              'price'=> 9,
              'image'=>''
            ],
            [
                'name'=> 'polpette vegetariane',
                'price'=> 5,
                'image'=>''

            ]
            ]);
    }
    public function render()
    {
        return view('livewire.cart');
    }
}
