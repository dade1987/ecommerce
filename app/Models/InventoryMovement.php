<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modello per movimenti di inventario
 * 
 * Gestisce i movimenti di carico, scarico e trasferimento dei prodotti logistici.
 * Include integrazione con il sistema di produzione tramite production_order_id.
 */
class InventoryMovement extends Model
{
    protected $table = 'logistic_inventory_movements';
    
    protected $fillable = [
        'logistic_product_id',
        'from_warehouse_id',
        'to_warehouse_id',
        'movement_type',
        'quantita',
        'note',
        'production_order_id',
        'origine_automatica',
    ];

    protected $casts = [
        'origine_automatica' => 'boolean',
    ];

    /**
     * Relazione con il prodotto logistico
     */
    public function logisticProduct(): BelongsTo
    {
        return $this->belongsTo(LogisticProduct::class);
    }

    /**
     * Relazione con il magazzino di origine
     */
    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    /**
     * Relazione con il magazzino di destinazione
     */
    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    /**
     * Scope per movimenti automatici generati da produzione
     */
    public function scopeAutomatic($query)
    {
        return $query->where('origine_automatica', true);
    }

    /**
     * Scope per movimenti manuali
     */
    public function scopeManual($query)
    {
        return $query->where('origine_automatica', false);
    }

    /**
     * Scope per tipo di movimento
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('movement_type', $type);
    }
}
