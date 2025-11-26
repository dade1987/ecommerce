<?php

namespace App\Services\Cart\Facades;

use App\Services\Cart\CartCollection;
use App\Services\Cart\Item;
use Illuminate\Support\Facades\Facade;

/**
 * @method static CartCollection content() Get the cart content.
 * @method static float total() Get the total price of all items with TAX.
 * @method static Item addSubItem($id, $name, $quantity, $price, array $options, string $parentRowId) Add a subItem to the cart.
 * @method static int count() Get the number of items in the cart.
 * @method static array|Item|Item[] add($id, $name = null, $quantity = null, $price = null, array $options = []) Add a Item to the cart.
 * @method static bool remove(string $rowId) Remove a row from the cart.
 * @method static void destroy() Empty the cart.
 */
class Cart extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'cart';
    }
}
