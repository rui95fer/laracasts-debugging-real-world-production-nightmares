<?php

namespace Tests\Feature\Search;

use App\Models\Product;
use Illuminate\Contracts\Pagination\Paginator as PaginatorContract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SearchSqliteFallbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_sqlite_fallback_can_match_query_in_description(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            $this->markTestSkipped('This test only applies to SQLite fallback behavior.');
        }

        Product::factory()->create([
            'name' => 'Alpha Device',
            'slug' => 'alpha-device',
            'description' => 'Premium quality widget for edge cases.',
        ]);

        Product::factory()->create([
            'name' => 'Gamma Device',
            'slug' => 'gamma-device',
            'description' => 'Standard catalog item.',
        ]);

        $response = $this->get(route('search', ['q' => 'quality']));

        $response->assertOk();

        $products = $response->viewData('products');

        $this->assertInstanceOf(PaginatorContract::class, $products);
        $this->assertCount(1, $products->items());
        $this->assertSame('Alpha Device', $products->items()[0]->name);
    }
}
