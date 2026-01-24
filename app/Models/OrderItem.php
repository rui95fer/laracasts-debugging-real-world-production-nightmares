<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
        'subtotal',
    ];

    /**
     * The attributes that should be cast.
     * 
     * Episode 10: All money values stored as integers (cents)
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'price' => 'integer',
        'subtotal' => 'integer',
    ];

    /**
     * Get the order this item belongs to.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product.
     * Episode 2: N+1 - This relationship is accessed without eager loading
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get formatted price for display
     */
    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price / 100, 2);
    }

    /**
     * Get formatted subtotal for display
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return '$' . number_format($this->subtotal / 100, 2);
    }
}
