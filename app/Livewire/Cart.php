<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Order;
use Livewire\Component;
use Illuminate\Support\Collection;
use App\Notifications\CustomerOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Notification;

class Cart extends Component
{
    public string $back_to_shop_link;

    public string $next_link;
    public array $items;
    public string $total;

    protected $listeners = ['refresh-cart' => 'refreshCart'];

    public function mount(string $back_to_shop_link, string $next_link)
    {
        //Session::forget('cart');
        $this->back_to_shop_link = $back_to_shop_link;
        $this->next_link = $next_link;

        $this->refreshCart();
    }

    public function refreshCart()
    {
        $this->items = Session::get('cart') ?? [];

        $this->total = number_format(array_reduce($this->items, fn ($carry, $item) => $carry += $item['price'], 0), 2);
    }

    public function render()
    {
        return view('livewire.cart');
    }
}
