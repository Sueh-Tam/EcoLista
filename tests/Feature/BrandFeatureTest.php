<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Product;
use App\Models\ShoppingList;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BrandFeatureTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_search_products_by_name()
    {
        $user = User::factory()->create();
        $brand = Brand::factory()->create(['name' => 'Nestle']);
        $product = Product::factory()->create([
            'name' => 'Chocolate KitKat',
            'brand_id' => $brand->id
        ]);

        $response = $this->actingAs($user)->getJson(route('api.products.search', ['query' => 'KitKat']));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Chocolate KitKat',
                'brand_name' => 'Nestle'
            ]);
    }

    public function test_can_search_brands_by_name()
    {
        $user = User::factory()->create();
        Brand::factory()->create(['name' => 'Coca Cola']);
        Brand::factory()->create(['name' => 'Pepsi']);

        $response = $this->actingAs($user)->getJson(route('api.brands.search', ['query' => 'Coca']));

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Coca Cola'])
            ->assertJsonMissing(['name' => 'Pepsi']);
    }

    public function test_can_add_item_with_new_brand()
    {
        $user = User::factory()->create();
        $list = ShoppingList::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('shopping-lists.items.store', $list), [
            'product_name' => 'Novo Produto',
            'brand_name' => 'Marca Nova', // Não existe no DB, deve ser criada
            'quantity' => 1,
            'is_price_per_kg' => false
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('brands', ['name' => 'Marca Nova']);
        $this->assertDatabaseHas('products', [
            'name' => 'Novo Produto',
        ]);
    }

    public function test_can_add_item_with_existing_brand()
    {
        $user = User::factory()->create();
        $list = ShoppingList::factory()->create(['user_id' => $user->id]);
        $brand = Brand::factory()->create(['name' => 'Marca Teste']);

        $response = $this->actingAs($user)->post(route('shopping-lists.items.store', $list), [
            'product_name' => 'Produto Teste',
            'brand_name' => 'Marca Teste',
            'quantity' => 1,
            'is_price_per_kg' => false
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'name' => 'Produto Teste',
            'brand_id' => $brand->id
        ]);
        $this->assertDatabaseHas('list_items', [
            'quantity' => 1
        ]);
    }

    public function test_reuse_existing_product_and_brand()
    {
        $user = User::factory()->create();
        $list = ShoppingList::factory()->create(['user_id' => $user->id]);
        $brand = Brand::factory()->create(['name' => 'Marca Existente']);
        $product = Product::factory()->create([
            'name' => 'Produto Existente',
            'brand_id' => $brand->id
        ]);

        $response = $this->actingAs($user)->post(route('shopping-lists.items.store', $list), [
            'product_name' => 'Produto Existente',
            'brand_name' => 'Marca Existente',
            'quantity' => 2,
            'is_price_per_kg' => false
        ]);

        $response->assertRedirect();
        // Verifica se não criou duplicado
        $this->assertEquals(1, Product::where('name', 'Produto Existente')->count());
        $this->assertEquals(1, Brand::where('name', 'Marca Existente')->count());
    }
}
