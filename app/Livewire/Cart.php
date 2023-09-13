<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class Cart extends Component
{
    public string $back_to_shop_link;
    public array $items;
    public float $total;
    public function mount(string $back_to_shop_link)
    {
        $this->back_to_shop_link = $back_to_shop_link;

        $this->items = Session::get('cart') ?? [];

        $this->total = array_reduce($this->items, fn ($carry, $item) => $carry += $item['price'], 0);
    }
    public function render()
    {
        return view('livewire.cart');
    }
}
