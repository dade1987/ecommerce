<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modello per magazzini/fornitori/negozi
 */
class Warehouse extends Model
{
    protected $table = 'logistic_warehouses';
    
    protected $fillable = [
        'nome',
        'tipo',
    ];

    /**
     * Movimenti in entrata verso questo magazzino
     */
    public function incomingMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'to_warehouse_id');
    }

    /**
     * Movimenti in uscita da questo magazzino
     */
    public function outgoingMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'from_warehouse_id');
    }

    /**
     * Tutti i movimenti collegati a questo magazzino
     */
    public function allMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'to_warehouse_id')
            ->union(
                $this->hasMany(InventoryMovement::class, 'from_warehouse_id')
            );
    }
}
