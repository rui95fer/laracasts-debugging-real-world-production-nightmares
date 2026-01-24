<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'price_at_time',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'price_at_time' => 'integer',
    ];

    /**
     * Get the cart this item belongs to.
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get the product.
     * Episode 2: N+1 - This relationship is often accessed without eager loading
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get subtotal for this cart item (in cents)
     */
    public function getSubtotalAttribute(): int
    {
        return $this->price_at_time * $this->quantity;
    }

    /**
     * Get formatted subtotal for display
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return '$' . number_format($this->subtotal / 100, 2);
    }
}
