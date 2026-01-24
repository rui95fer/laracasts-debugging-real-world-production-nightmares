<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'session_id',
    ];

    /**
     * Get the user who owns this cart.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get cart items.
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Episode 5: Calculate cart total (with caching bug)
     * Episode 10: Also demonstrates money calculation issues
     * 
     * BUG: The caching happens in CartService, but here we show
     * the calculation that would be cached incorrectly
     */
    public function getSubtotal(): int
    {
        // Returns subtotal in cents
        return $this->items->sum(function ($item) {
            return $item->price_at_time * $item->quantity;
        });
    }

    /**
     * Get number of items in cart
     */
    public function getItemCount(): int
    {
        return $this->items->sum('quantity');
    }
}
