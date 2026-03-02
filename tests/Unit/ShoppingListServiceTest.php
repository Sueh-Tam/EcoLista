<?php

namespace Tests\Unit;

use App\Services\ShoppingListService;
use PHPUnit\Framework\TestCase;

class ShoppingListServiceTest extends TestCase
{
    public function test_calculate_item_total_without_promo()
    {
        $service = new ShoppingListService();
        // 5 itens a R$ 2.00 = R$ 10.00
        $total = $service->calculateItemTotal(5, 2.00, null, null);
        $this->assertEquals(10.00, $total);
    }

    public function test_calculate_item_total_with_promo_exact_quantity()
    {
        $service = new ShoppingListService();
        // Leve 6 por R$ 10.00. Compra 6.
        $total = $service->calculateItemTotal(6, 2.00, 6, 10.00);
        $this->assertEquals(10.00, $total);
    }

    public function test_calculate_item_total_with_promo_extra_quantity()
    {
        $service = new ShoppingListService();
        // Leve 6 por R$ 10.00. Compra 7.
        // 6 custam 10.00. 1 custa 2.00. Total 12.00.
        $total = $service->calculateItemTotal(7, 2.00, 6, 10.00);
        $this->assertEquals(12.00, $total);
    }

    public function test_calculate_item_total_with_promo_multiple_packs()
    {
        $service = new ShoppingListService();
        // Leve 6 por R$ 10.00. Compra 13.
        // 12 custam 20.00 (2 pacotes). 1 custa 2.00. Total 22.00.
        $total = $service->calculateItemTotal(13, 2.00, 6, 10.00);
        $this->assertEquals(22.00, $total);
    }
}
