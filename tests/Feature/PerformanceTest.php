<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\User;
use App\Models\ShoppingList;
use App\Models\Brand;
use App\Models\Product;
use App\Services\ShoppingListService;
use Illuminate\Support\Facades\DB;

class PerformanceTest extends TestCase
{
    use DatabaseTransactions;

    protected $service;
    protected $user;
    protected $list;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ShoppingListService();
        $this->user = User::factory()->create();
        $this->list = ShoppingList::factory()->create(['user_id' => $this->user->id]);
    }

    /** @test */
    public function adding_new_item_performance_check()
    {
        DB::enableQueryLog();

        $start = microtime(true);

        $this->service->addItem($this->list, [
            'product_name' => 'New Product',
            'brand_name' => 'New Brand',
            'quantity' => 1,
            'unit_price' => 10.00,
            'is_price_per_kg' => false
        ]);

        $end = microtime(true);
        $duration = $end - $start;

        $queries = DB::getQueryLog();
        $queryCount = count($queries);

        // Assert duration is under 2 seconds for new items (due to firstOrCreate overhead)
        $this->assertLessThan(2.0, $duration, "Adding new item took too long: {$duration}s");

        // Assert query count is optimized
        // 1. Select/Create Brand (1 or 2 queries)
        // 2. Select/Create Product (1 or 2 queries)
        // 3. Create ListItem (1 query)
        // 4. Update List Total (1 query - increment)
        // Max expected: 6 queries. If SUM was used, it would be +1 heavy query.
        $this->assertLessThanOrEqual(7, $queryCount, "Too many queries executed: {$queryCount}");
    }

    /** @test */
    public function adding_existing_item_with_ids_performance_check()
    {
        $brand = Brand::factory()->create(['name' => 'Existing Brand']);
        $product = Product::factory()->create([
            'name' => 'Existing Product',
            'brand_id' => $brand->id
        ]);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $start = microtime(true);

        $this->service->addItem($this->list, [
            'product_name' => 'Existing Product',
            'product_id' => $product->id,
            'brand_name' => 'Existing Brand',
            'brand_id' => $brand->id,
            'quantity' => 1,
            'unit_price' => 10.00,
            'is_price_per_kg' => false
        ]);

        $end = microtime(true);
        $duration = $end - $start;

        $queries = DB::getQueryLog();
        $queryCount = count($queries);

        // Optimized path:
        // 1. Find Brand by ID (1 query)
        // 2. Find Product by ID (1 query)
        // 3. Create ListItem (1 query)
        // 4. Update List Total (1 query)
        // Total 4 queries.
        $this->assertLessThanOrEqual(5, $queryCount, "Optimized path queries: {$queryCount}");
        $this->assertLessThan(1.0, $duration, "Adding existing item with ID took too long: {$duration}s");
    }

    /** @test */
    public function adding_items_does_not_use_sum_query()
    {
        // Add 50 items first to make SUM slow if it existed
        for ($i = 0; $i < 50; $i++) {
            $this->list->listItems()->create([
                'product_id' => Product::factory()->create()->id,
                'quantity' => 1,
                'unit_price' => 10,
                'item_total' => 10
            ]);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->service->addItem($this->list, [
            'product_name' => 'New Product ' . uniqid(),
            'brand_name' => 'New Brand ' . uniqid(),
            'quantity' => 1,
            'unit_price' => 10.00,
            'is_price_per_kg' => false
        ]);

        $queries = DB::getQueryLog();
        
        // Check if any query contains "sum"
        $hasSum = false;
        foreach ($queries as $query) {
            if (stripos($query['query'], 'sum') !== false) {
                $hasSum = true;
                break;
            }
        }

        $this->assertFalse($hasSum, "The operation should not perform a SUM query on list_items table.");
    }
}
