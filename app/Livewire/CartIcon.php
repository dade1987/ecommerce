<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use Illuminate\Support\Str;

use Filament\Actions\Action;
use App\Services\Cart\Facades\Cart;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\Session;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Actions\Concerns\InteractsWithActions;

class CartIcon extends Component implements HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;

    public string $count = '0.00';
    public Product $product;
    public string $last_cart_item_row_id;

    protected $listeners = [
        'add-to-cart' => 'addToCart',
        'add-item-to-cart' => 'addItemToCart',
        'remove-from-cart' => 'removeFromCart',
        'ingredients-list' => 'ingredientsList'
    ];

    public function ingredientsList(string $product_id)
    {
        $this->product = Product::findOrFail($product_id);

        $this->dispatch('open-modal', id: 'ingredients-list');
    }

    public function closeIngredientsModal()
    {
        $this->dispatch('close-modal', id: 'ingredients-list');
    }

    public function addToCart(string $product_id, ?string $option = null)
    {
        //ATTENZIONE: Non uso l'associazione del modello nativa altrimenti mi sovrascrive gli articoli se cambio le varianti
        //Preferisco così per ora
        if ($option != null) {
            $variant = Product::findOrFail($product_id);

            $price = 0;

            if ($option == '+') {
                $price = $variant->price;
            }

            $variant = Cart::addSubItem(Cart::count() + 1,  $variant->name, 1, $price, ['operation' => $option, 'model_id' => $variant->id, 'model_type' => Product::class, 'featured_image_id' => $variant->featured_image_id], $this->last_cart_item_row_id);
        } else {
            $this->product = Product::findOrFail($product_id);

            $cart_item = Cart::add(Cart::count() + 1, $this->product->name, 1, $this->product->price, ['model_id' => $this->product->id, 'model_type' => Product::class, 'featured_image_id' => $this->product->featured_image_id]);

            $this->last_cart_item_row_id = $cart_item->getRowId();
        }
        $this->count =  '€ ' . number_format(Cart::total(), 2);
    }

    public function addItemToCart(string $product_id, ?string $option = null)
    {
        //in realtà si potrebbero aggiungere anche le note come opzioni sempre in quel modal
        if ($option == null) {
            $this->addToCart($product_id);

            if (!$this->product->variations->isEmpty()) {
                //è giusta questa sintassi anche se è sottolineata
                $this->dispatch('open-modal', id: 'add-variations');
            }
        } else {
            $this->addToCart($product_id, $option);
        }
    }

    public function closeVariantsModal()
    {
        $this->dispatch('close-modal', id: 'add-variations');
    }

    public function removeFromCart(string $product_id)
    {
        Cart::remove($product_id);

        $this->count =  '€ ' . number_format(Cart::total(), 2);

        $this->dispatch('refresh-cart');
    }

    public function render()
    {
        $this->count = '€ ' . number_format(Cart::total(), 2);
        return view('livewire.cart-icon');
    }
}
