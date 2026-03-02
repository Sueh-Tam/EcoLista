<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ShoppingList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShoppingListFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_shopping_list()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/shopping-lists/create');

        $response->assertRedirect();
        $this->assertDatabaseCount('shopping_lists', 1);
    }

    public function test_user_can_add_item_to_list()
    {
        $user = User::factory()->create();
        $list = ShoppingList::create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('shopping-lists.items.store', $list), [
            'product_name' => 'Leite',
            'quantity' => 2,
            'unit_price' => 5.00,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', ['name' => 'Leite']);
        $this->assertDatabaseHas('list_items', [
            'list_id' => $list->id,
            'quantity' => 2,
            'item_total' => 10.00
        ]);
        
        $list->refresh();
        $this->assertEquals(10.00, $list->total_value);
    }

    public function test_user_can_add_promo_item_to_list()
    {
        $user = User::factory()->create();
        $list = ShoppingList::create(['user_id' => $user->id]);

        // Leve 6 por 10. Compra 7. Preço unit 2.
        // Total esperado: 10 + 2 = 12.
        $response = $this->actingAs($user)->post(route('shopping-lists.items.store', $list), [
            'product_name' => 'Sabonete',
            'quantity' => 7,
            'unit_price' => 2.00,
            'promo_buy_quantity' => 6,
            'promo_price' => 10.00
        ]);

        $response->assertRedirect();
        
        $list->refresh();
        $this->assertEquals(12.00, $list->total_value);
    }
}
