<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Episode 3: The Authorization Hole Nobody Noticed
 *
 * These tests protect refund authorization behavior.
 */
class AuthorizationHoleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Regular users cannot refund orders.
     */
    public function test_regular_user_cannot_refund_order(): void
    {
        // Create a regular user (not admin)
        $user = User::factory()->create(['is_admin' => false]);

        // Create an order for a different user
        $otherUser = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $otherUser->id,
            'status' => 'completed',
        ]);

        // Act as regular user and try to refund
        $response = $this->actingAs($user)
            ->post(route('orders.refund', $order));

        $response->assertForbidden();
        $this->assertEquals('completed', $order->fresh()->status);
    }

    /**
     * Regular users cannot refund even their own orders.
     */
    public function test_regular_user_cannot_refund_own_order(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($user)
            ->post(route('orders.refund', $order));

        $response->assertForbidden();
        $this->assertEquals('completed', $order->fresh()->status);
    }

    /**
     * Admin users should be able to refund orders.
     */
    public function test_admin_user_can_refund_order(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $order = Order::factory()->create(['status' => 'completed']);

        $response = $this->actingAs($admin)
            ->post(route('orders.refund', $order));

        $response->assertRedirect();
        $this->assertEquals('refunded', $order->fresh()->status);
    }

    /**
     * Guest users should be redirected to login.
     */
    public function test_guest_cannot_refund_order(): void
    {
        $order = Order::factory()->create(['status' => 'completed']);

        $response = $this->post(route('orders.refund', $order));

        $response->assertRedirect(route('login'));
    }
}
