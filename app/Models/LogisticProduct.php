<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modello per prodotti logistici
 * 
 * Questo modello rappresenta i prodotti utilizzati nel sistema logistico,
 * separato dal modello Product che è dedicato ai ristoranti.
 */
class LogisticProduct extends Model
{
    protected $fillable = [
        'codice',
        'nome',
        'descrizione',
        'unita_misura',
    ];

    /**
     * Relazione con i movimenti di inventario
     */
    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    /**
     * Calcola la giacenza corrente per un magazzino specifico
     */
    public function getCurrentStock(int $warehouseId): int
    {
        $entrate = $this->inventoryMovements()
            ->where('to_warehouse_id', $warehouseId)
            ->sum('quantita');

        $uscite = $this->inventoryMovements()
            ->where('from_warehouse_id', $warehouseId)
            ->sum('quantita');

        return $entrate - $uscite;
    }

    /**
     * Calcola la giacenza totale per tutti i magazzini
     */
    public function getTotalStock(): int
    {
        $entrate = $this->inventoryMovements()
            ->whereNotNull('to_warehouse_id')
            ->sum('quantita');

        $uscite = $this->inventoryMovements()
            ->whereNotNull('from_warehouse_id')
            ->sum('quantita');

        return $entrate - $uscite;
    }
}
