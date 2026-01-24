<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Episode 2: N+1 queries in admin order listing
     * Episode 3: Authorization hole in refund action
     * Episode 7: Timezone issues with placed_at
     * Episode 10: Money handling bugs
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Unique order number for display
            $table->string('order_number')->unique();
            
            // Episode 10: All money values stored in cents
            $table->integer('subtotal'); // In cents
            $table->integer('tax');      // In cents
            $table->integer('total');    // In cents
            
            // Order status for Episode 3 (refund action)
            $table->enum('status', ['pending', 'processing', 'completed', 'refunded'])
                  ->default('pending');
            
            // Episode 7: Timezone handling
            // This is stored in UTC, display should convert to user timezone
            $table->timestamp('placed_at');
            
            $table->timestamps();

            // Indexes for Episode 2 and Episode 9
            $table->index('user_id');
            $table->index('order_number');
            $table->index('placed_at');
            $table->index('status');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            $table->integer('quantity');
            
            // Episode 10: Price snapshot at time of order (in cents)
            $table->integer('price');    // Unit price in cents
            $table->integer('subtotal'); // quantity * price in cents
            
            $table->timestamps();

            // Episode 2: Indexes for N+1 prevention
            $table->index('order_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
