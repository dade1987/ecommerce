<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modello per magazzini/fornitori/negozi
 */
class Warehouse extends Model
{
    use HasFactory;

    protected $table = 'logistic_warehouses';

    protected $fillable = [
        'name',
        'type',
        'is_final_destination',
    ];

    /**
     * ProductTwin attualmente presenti in questo magazzino
     */
    public function currentTwins(): HasMany
    {
        return $this->hasMany(\App\Models\ProductTwin::class, 'current_warehouse_id', 'id');
    }

    /**
     * Movimenti in entrata verso questo magazzino
     */
    public function incomingMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'to_warehouse_id', 'id');
    }

    /**
     * Movimenti in uscita da questo magazzino
     */
    public function outgoingMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'from_warehouse_id', 'id');
    }
}
