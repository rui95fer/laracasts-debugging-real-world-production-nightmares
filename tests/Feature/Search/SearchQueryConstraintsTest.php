<?php

namespace Tests\Feature\Search;

use App\Models\Product;
use Illuminate\Contracts\Pagination\Paginator as PaginatorContract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class SearchQueryConstraintsTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_shorter_than_minimum_length_returns_empty_results(): void
    {
        Product::factory()->create([
            'name' => 'Widget Prime',
            'slug' => 'widget-prime',
            'description' => 'Fast and reliable widget.',
        ]);

        $response = $this->get(route('search', ['q' => 'wi']));

        $response->assertOk();

        $products = $response->viewData('products');

        $this->assertInstanceOf(Collection::class, $products);
        $this->assertTrue($products->isEmpty());
    }

    public function test_per_page_is_capped_by_configured_max_results(): void
    {
        Config::set('shop.search.max_results', 5);

        Product::factory()->count(12)->sequence(
            fn ($sequence) => [
                'name' => 'Widget Cap ' . $sequence->index,
                'slug' => 'widget-cap-' . $sequence->index,
                'description' => 'Cap test item ' . $sequence->index,
            ]
        )->create();

        $response = $this->get(route('search', [
            'q' => 'Widget',
            'per_page' => 500,
        ]));

        $response->assertOk();

        $products = $response->viewData('products');

        $this->assertInstanceOf(PaginatorContract::class, $products);
        $this->assertCount(5, $products->items());
    }
}
