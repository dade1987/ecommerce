<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class ProductTwin extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'internal_product_id',
        'current_warehouse_id',
        'lifecycle_status',
        'co2_emissions_production',
        'co2_emissions_logistics',
        'co2_emissions_total',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function internalProduct(): BelongsTo
    {
        return $this->belongsTo(InternalProduct::class);
    }

    public function currentWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'current_warehouse_id');
    }

    public function inventoryMovements(): BelongsToMany
    {
        return $this->belongsToMany(InventoryMovement::class, 'inventory_movement_product_twin');
    }

    public function invoiceItems(): BelongsToMany
    {
        return $this->belongsToMany(InvoiceItem::class, 'invoice_item_product_twin');
    }
}
