<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'order_number',
        'subtotal',
        'tax',
        'total',
        'status',
        'placed_at',
    ];

    /**
     * The attributes that should be cast.
     * 
     * Episode 10: Money values are stored as integers (cents)
     * Episode 7: Dates are cast to Carbon for timezone handling
     *
     * @var array<string, string>
     */
    protected $casts = [
        'subtotal' => 'integer',
        'tax' => 'integer',
        'total' => 'integer',
        'placed_at' => 'datetime',
    ];

    /**
     * Order statuses
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_REFUNDED = 'refunded';

    /**
     * Get the user who placed this order.
     * Episode 2: N+1 - Relationship for eager loading
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get order items.
     * Episode 2: N+1 - Relationship for eager loading
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Generate a unique order number
     */
    public static function generateOrderNumber(): string
    {
        $prefix = config('shop.orders.number_prefix', 'NM-');
        $timestamp = now()->format('Ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));
        
        return $prefix . $timestamp . '-' . $random;
    }

    /**
     * Episode 3: Process refund
     * BUG: No authorization check in the controller that calls this!
     */
    public function refund(): void
    {
        $this->status = self::STATUS_REFUNDED;
        $this->save();
        
        // In a real app, this would also:
        // - Process payment refund
        // - Send email notification
        // - Restore inventory
    }

    /**
     * Check if order can be refunded
     */
    public function canBeRefunded(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING, self::STATUS_COMPLETED]);
    }

    /**
     * Episode 10: Format money for display
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return '$' . number_format($this->subtotal / 100, 2);
    }

    public function getFormattedTaxAttribute(): string
    {
        return '$' . number_format($this->tax / 100, 2);
    }

    public function getFormattedTotalAttribute(): string
    {
        return '$' . number_format($this->total / 100, 2);
    }

    /**
     * Episode 7: Get placed_at in user's timezone
     * BUG: This method exists but isn't always used in views
     */
    public function getPlacedAtUserTimezone(?string $timezone = null): Carbon
    {
        $tz = $timezone ?? (auth()->user()?->timezone ?? 'UTC');
        return $this->placed_at->timezone($tz);
    }

    /**
     * Scope for orders by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for recent orders
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('placed_at', 'desc');
    }
}
