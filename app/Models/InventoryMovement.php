<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        'internal_product_id',
        'from_warehouse_id',
        'to_warehouse_id',
        'movement_type',
        'quantity',
        'note',
        'distance_km',
        'transport_mode',
        'production_order_id',
        'origine_automatica',
    ];

    protected $casts = [
        'origine_automatica' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::deleting(function ($movement) {
            // Cancella tutti i ProductTwin creati da questo carico
            foreach ($movement->productTwins as $twin) {
                if (isset($twin->metadata['carico_inventory_movement_id']) && $twin->metadata['carico_inventory_movement_id'] == $movement->id) {
                    // Elimina tutti i movimenti di inventario successivi collegati a questo ProductTwin
                    foreach ($twin->inventoryMovements as $otherMovement) {
                        if ($otherMovement->id != $movement->id) {
                            $otherMovement->delete();
                        }
                    }
                    $twin->delete();
                }
            }
            // Rimuovi anche i collegamenti nella tabella pivot
            $movement->productTwins()->detach();
        });
    }

    public function internalProduct(): BelongsTo
    {
        return $this->belongsTo(InternalProduct::class);
    }

    public function productTwins(): BelongsToMany
    {
        return $this->belongsToMany(ProductTwin::class, 'inventory_movement_product_twin');
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
