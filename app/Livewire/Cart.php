<?php

namespace App\Livewire;

use App\Notifications\CustomerOrder;
use Livewire\Component;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Notification;

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

    public function sendOrder()
    {
        Notification::route('mail', [
            'davidecavallini1987@gmail.com' => 'Davide Cavallini',
        ])->notify(new CustomerOrder($this->items));

        Session::remove('cart');

        return response()->redirectToRoute('{item1?}.index', ['container0' => 'order-completed']);
    }

    public function render()
    {
        return view('livewire.cart');
    }
}
