<?php

namespace Tests\Feature\Search;

use App\Models\Product;
use Illuminate\Contracts\Pagination\Paginator as PaginatorContract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_returns_paginated_results_and_preserves_query_string(): void
    {
        Product::factory()->count(30)->sequence(
            fn ($sequence) => [
                'name' => 'Widget Page ' . $sequence->index,
                'slug' => 'widget-page-' . $sequence->index,
                'description' => 'Pagination item ' . $sequence->index,
            ]
        )->create();

        $response = $this->get(route('search', [
            'q' => 'Widget',
            'per_page' => 10,
        ]));

        $response->assertOk();

        $products = $response->viewData('products');

        $this->assertInstanceOf(PaginatorContract::class, $products);
        $this->assertCount(10, $products->items());
        $this->assertStringContainsString('q=Widget', (string) $products->nextPageUrl());
        $this->assertStringContainsString('per_page=10', (string) $products->nextPageUrl());
    }
}
