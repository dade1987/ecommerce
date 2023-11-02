<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\Cart\Facades\Cart as CartFacade;

class Cart extends Component
{
    public string $back_to_shop_link;

    public string $next_link;
    public array $items;
    public string $total;

    protected $listeners = ['refresh-cart' => 'refreshCart'];

    public function mount(string $back_to_shop_link, string $next_link)
    {
        //CartFacade::destroy();
        $this->back_to_shop_link = $back_to_shop_link;
        $this->next_link = $next_link;

        $this->refreshCart();
    }

    public function refreshCart()
    {
        $this->items = CartFacade::content()->toArray();

        $this->total = CartFacade::total();
    }

    public function render()
    {
        return view('livewire.cart');
    }
}
