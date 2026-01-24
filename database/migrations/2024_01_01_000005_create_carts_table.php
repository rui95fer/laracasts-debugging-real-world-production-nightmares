<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Episode 5: Cache showing wrong user's data
     */
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            // Nullable for guest carts
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            // Session ID for guest users
            $table->string('session_id')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('session_id');
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(1);
            
            // Price snapshot at time of adding to cart (in cents)
            // Episode 10: Important for money accuracy
            $table->integer('price_at_time');
            
            $table->timestamps();

            // Episode 2: Indexes for N+1 prevention
            $table->index('cart_id');
            $table->index('product_id');
            
            // Ensure unique product per cart
            $table->unique(['cart_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};
