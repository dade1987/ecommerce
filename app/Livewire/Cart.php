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
    public array $items;
    public string $total;
    public function mount(string $back_to_shop_link)
    {
        $this->back_to_shop_link = $back_to_shop_link;

        $this->items = Session::get('cart') ?? [];

        $this->total = number_format(array_reduce($this->items, fn ($carry, $item) => $carry += $item['price'], 0), 2);
    }

    public function sendOrder()
    {

        $order = Order::create(['delivery_date' => now()]);
        foreach ($this->items as $item) {
            $order->products()->attach($item);
        }
        $order->addresses()->attach(Auth::user()->addresses()->first());
        $order->save();

        Session::remove('cart');

        Notification::route('mail', [
            'davidecavallini1987@gmail.com' => 'Davide Cavallini',
        ])->notify(new CustomerOrder($order->products->toArray(), $this->total));

        return redirect()->route('{item1?}.index', ['container0' => 'order-completed']);
    }

    public function render()
    {
        return view('livewire.cart');
    }
}
