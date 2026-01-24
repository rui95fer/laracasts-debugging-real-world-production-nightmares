<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Episode 4: Race conditions in inventory
     * Episode 9: Search query performance
     * Episode 10: Money handling (price in cents)
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            
            // Episode 10: Store price in cents to avoid floating-point issues
            // Price 1099 = $10.99
            $table->integer('price'); // In cents!
            
            // Episode 4: Inventory tracking with race condition vulnerability
            // Note: using signed integer allows negative values (bug for Episode 4)
            // The fixed version should use: $table->unsignedInteger('stock_quantity');
            $table->integer('stock_quantity')->default(0);
            
            $table->string('image_path')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Episode 2: N+1 - Index for eager loading
            $table->index('category_id');
            
            // Episode 9: Index for search (though LIKE %term% can't use it)
            $table->index('name');
            
            // Note: Full-text index would be added in a separate migration
            // for MySQL compatibility. SQLite handles this differently.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
