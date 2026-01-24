<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Episode 3: The Authorization Hole Nobody Noticed
 * 
 * These tests demonstrate the authorization bug - regular users
 * can refund orders because the route lacks middleware.
 */
class AuthorizationHoleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * BUG: Regular user CAN refund orders (should fail!)
     */
    public function test_regular_user_can_refund_order_bug(): void
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

        // BUG: This succeeds when it shouldn't!
        // The route is missing ->middleware('admin')
        $response->assertRedirect(); // Succeeds instead of 403
        
        // Verify the order was refunded (BUG!)
        $this->assertEquals('refunded', $order->fresh()->status);
    }

    /**
     * What SHOULD happen: Regular user gets 403 Forbidden
     */
    public function test_regular_user_should_not_refund_order(): void
    {
        $this->markTestSkipped('This test shows expected behavior after fix');

        $user = User::factory()->create(['is_admin' => false]);
        $order = Order::factory()->create(['status' => 'completed']);

        $response = $this->actingAs($user)
            ->post(route('orders.refund', $order));

        // After fixing, this should be the result
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
