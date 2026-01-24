<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Order Policy
 * 
 * Episode 3: The Authorization Hole Nobody Noticed
 * 
 * This policy exists but the refund action in OrderController
 * doesn't check it! The policy is correctly implemented,
 * but forgetting to call $this->authorize() creates the hole.
 */
class OrderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if user can view any orders.
     */
    public function viewAny(User $user): bool
    {
        // Admins can view all orders
        return $user->isAdmin();
    }

    /**
     * Determine if user can view specific order.
     */
    public function view(User $user, Order $order): bool
    {
        // User can view their own orders, or admin can view any
        return $user->id === $order->user_id || $user->isAdmin();
    }

    /**
     * Determine if user can update the order.
     */
    public function update(User $user, Order $order): bool
    {
        // Only admins can update orders
        return $user->isAdmin();
    }

    /**
     * Determine if user can delete the order.
     */
    public function delete(User $user, Order $order): bool
    {
        // Only admins can delete orders
        return $user->isAdmin();
    }

    /**
     * Determine if user can refund the order.
     * 
     * ============================================
     * THIS POLICY METHOD IS CORRECTLY IMPLEMENTED
     * 
     * The bug is that OrderController::refund() 
     * doesn't call $this->authorize('refund', $order)!
     * ============================================
     */
    public function refund(User $user, Order $order): bool
    {
        // Only admins can process refunds
        if (!$user->isAdmin()) {
            return false;
        }

        // Can only refund orders that aren't already refunded
        return $order->canBeRefunded();
    }

    /**
     * Determine if user can export orders.
     */
    public function export(User $user): bool
    {
        return $user->isAdmin();
    }
}
