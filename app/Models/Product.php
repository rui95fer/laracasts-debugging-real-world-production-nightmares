<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'stock_quantity',
        'image_path',
    ];

    /**
     * The attributes that should be cast.
     * 
     * Episode 10: Price is stored in cents as integer
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'integer',
        'stock_quantity' => 'integer',
    ];

    /**
     * Get the category this product belongs to.
     * Episode 2: N+1 - Relationship for eager loading
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get order items containing this product.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get cart items containing this product.
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Episode 10: Format price for display (WRONG WAY - mixes formatting with data)
     * This demonstrates the bug where formatting happens in the model
     * 
     * @return string
     */
    public function getFormattedPriceAttribute(): string
    {
        // BUG: This can cause floating-point display issues
        return '$' . number_format($this->price / 100, 2);
    }

    /**
     * Episode 10: Get price in dollars (for display only!)
     * Never use this for calculations
     */
    public function getPriceDollarsAttribute(): float
    {
        return $this->price / 100;
    }

    /**
     * Episode 4: Check if product is in stock
     */
    public function inStock(): bool
    {
        return $this->stock_quantity > 0;
    }

    /**
     * Episode 4: Check if specific quantity is available
     * BUG: This is not atomic - race condition possible!
     */
    public function hasStock(int $quantity): bool
    {
        return $this->stock_quantity >= $quantity;
    }

    /**
     * Episode 4: Decrease stock (non-atomic version - BUG!)
     * This demonstrates the race condition
     */
    public function decreaseStock(int $quantity): void
    {
        // BUG: Not atomic! Another request could modify stock between read and write
        $this->stock_quantity -= $quantity;
        $this->save();
    }
}
