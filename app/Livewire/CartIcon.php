<?php

namespace App\Livewire;

use App\Models\Product;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class CartIcon extends Component
{
    public int $count = 0;
    protected $listeners = [
        'add-to-cart' => 'addToCart',
        'remove-from-cart' => 'removeFromCart',
    ];
    public function render()
    {
        $this->count = collect(Session::get('cart'))->count();
        return view('livewire.cart-icon');
    }

    public function addToCart(string $product_id)
    {
        $product = Product::find($product_id);
        Session::push('cart', $product);
        $this->count = collect(Session::get('cart'))->count();
    }

    public function removeFromCart(string $product_id)
    {

        $products = Session::get('cart');

        foreach ($products as $key => $item) {
            if ($item->id === (int)$product_id) {
                unset($products[$key]);
            }
        }
        Session::put('cart', $products);

        $this->count = collect(Session::get('cart'))->count();

        $this->dispatch('refresh-cart');
    }
}
