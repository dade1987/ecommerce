<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class Cart extends Component
{
    public array $items;
    public float $total;
    public function mount()
    {
        $this->items = Session::get('cart') ?? [];

        $this->total = array_reduce($this->items, fn ($carry, $item) => $carry += $item['price'], 0);
    }
    public function render()
    {
        return view('livewire.cart');
    }
}
