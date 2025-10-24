<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'inventory_movement_id',
        'internal_product_id',
        'item_type',
        'quantity',
        'unit_price',
        'total_price',
        'description',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->total_price)) {
                $model->total_price = $model->quantity * $model->unit_price;
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty(['quantity', 'unit_price'])) {
                $model->total_price = $model->quantity * $model->unit_price;
            }
        });
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function inventoryMovement(): BelongsTo
    {
        return $this->belongsTo(InventoryMovement::class);
    }

    public function internalProduct(): BelongsTo
    {
        return $this->belongsTo(InternalProduct::class);
    }

    /**
     * Relazione many-to-many con ProductTwin
     */
    public function productTwins()
    {
        return $this->belongsToMany(ProductTwin::class, 'invoice_item_product_twin');
    }

    /**
     * Verifica se l'item è di tipo fisico
     */
    public function isPhysical(): bool
    {
        return $this->item_type === 'physical';
    }

    /**
     * Verifica se l'item è di tipo servizio
     */
    public function isService(): bool
    {
        return $this->item_type === 'service';
    }

    /**
     * Verifica se l'item è di tipo virtuale
     */
    public function isVirtual(): bool
    {
        return $this->item_type === 'virtual';
    }
}
