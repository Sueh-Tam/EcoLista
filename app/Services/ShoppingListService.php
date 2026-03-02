<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\ShoppingList;
use App\Models\ListItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ShoppingListService
{
    public function createList($user, array $data)
    {
        return $user->shoppingLists()->create($data);
    }

    public function addItem(ShoppingList $list, array $data)
    {
        return DB::transaction(function () use ($list, $data) {
            $isPerKg = $data['is_price_per_kg'] ?? false;
            
            // Otimização: Se o ID da marca for fornecido, usa-o diretamente
            if (!empty($data['brand_id'])) {
                $brand = Brand::find($data['brand_id']);
                // Fallback caso o ID não exista (embora validado)
                if (!$brand) {
                    $brand = Brand::firstOrCreate(['name' => $data['brand_name']]);
                }
            } else {
                $brand = Brand::firstOrCreate(['name' => $data['brand_name']]);
            }

            // Otimização: Se o ID do produto for fornecido, usa-o diretamente
            if (!empty($data['product_id'])) {
                $product = Product::find($data['product_id']);
                // Fallback caso o ID não exista
                if (!$product) {
                    $product = Product::firstOrCreate(
                        [
                            'name' => $data['product_name'],
                            'brand_id' => $brand->id
                        ],
                        [
                            'description' => $data['product_description'] ?? null,
                            'is_price_per_kg' => $isPerKg
                        ]
                    );
                }
            } else {
                $product = Product::firstOrCreate(
                    [
                        'name' => $data['product_name'],
                        'brand_id' => $brand->id
                    ],
                    [
                        'description' => $data['product_description'] ?? null,
                        'is_price_per_kg' => $isPerKg
                    ]
                );
            }
            
            // Atualiza a flag apenas se o produto já existia e o valor for diferente
            if (isset($product->wasRecentlyCreated) && !$product->wasRecentlyCreated && $product->is_price_per_kg != $isPerKg) {
                $product->update(['is_price_per_kg' => $isPerKg]);
            } else if (!isset($product->wasRecentlyCreated) && $product->is_price_per_kg != $isPerKg) {
                // If fetched by find(), wasRecentlyCreated is not set/true in the same way as firstOrCreate
                $product->update(['is_price_per_kg' => $isPerKg]);
            }

            $quantity = $data['quantity'];
            $promoBuyQty = $data['promo_buy_quantity'] ?? null;
            $promoPrice = $data['promo_price'] ?? null;

            // Se for por KG, não pode ter promoção de atacado
            if ($isPerKg) {
                $promoBuyQty = null;
                $promoPrice = null;
            }

            $unitPrice = $data['unit_price'] ?? 0;
            
            // Calculate unit price if missing and promo info is available
            if (!$unitPrice && $promoBuyQty && $promoPrice) {
                $unitPrice = $promoPrice / $promoBuyQty;
            }

            $itemTotal = $this->calculateItemTotal($quantity, $unitPrice, $promoBuyQty, $promoPrice);

            $listItem = $list->listItems()->create([
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'promo_buy_quantity' => $promoBuyQty,
                'promo_price' => $promoPrice,
                'item_total' => $itemTotal,
            ]);

            // Otimização: Incrementa o valor total em vez de recalcular tudo (evita query de SUM)
            $list->increment('total_value', $itemTotal);

            return $listItem;
        });
    }
    
    public function updateItem(ListItem $item, array $data)
    {
         return DB::transaction(function () use ($item, $data) {
            $isPerKg = $data['is_price_per_kg'] ?? false;
            
            // Atualiza o produto se mudou o tipo de preço
            if ($item->product->is_price_per_kg != $isPerKg) {
                $item->product->update(['is_price_per_kg' => $isPerKg]);
            }

            $quantity = $data['quantity'];
            $promoBuyQty = $data['promo_buy_quantity'] ?? null;
            $promoPrice = $data['promo_price'] ?? null;

            // Se for por KG, não pode ter promoção de atacado
            if ($isPerKg) {
                $promoBuyQty = null;
                $promoPrice = null;
            }

            $unitPrice = $data['unit_price'] ?? 0;

            // Calculate unit price if missing and promo info is available
            if (!$unitPrice && $promoBuyQty && $promoPrice) {
                $unitPrice = $promoPrice / $promoBuyQty;
            }

            $itemTotal = $this->calculateItemTotal($quantity, $unitPrice, $promoBuyQty, $promoPrice);
            $oldItemTotal = $item->item_total;

            $item->update([
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'promo_buy_quantity' => $promoBuyQty,
                'promo_price' => $promoPrice,
                'item_total' => $itemTotal,
            ]);

            // Otimização: Atualiza apenas a diferença
            $diff = $itemTotal - $oldItemTotal;
            if ($diff != 0) {
                if ($diff > 0) {
                    $item->shoppingList()->increment('total_value', $diff);
                } else {
                    $item->shoppingList()->decrement('total_value', abs($diff));
                }
            }

            return $item;
        });
    }

    public function removeItem(ListItem $item)
    {
        return DB::transaction(function () use ($item) {
            $list = $item->shoppingList;
            $itemTotal = $item->item_total;
            $item->delete();
            // Otimização: Decrementa o valor
            $list->decrement('total_value', $itemTotal);
        });
    }

    public function calculateItemTotal($quantity, $unitPrice, $promoBuyQty, $promoPrice)
    {
        if ($promoBuyQty && $promoPrice && $quantity >= $promoBuyQty) {
            // Lógica: Pacotes completos pagam preço promocional, o resto paga preço unitário
            $promoPacks = intdiv($quantity, $promoBuyQty);
            $remainder = $quantity % $promoBuyQty;
            
            return ($promoPacks * $promoPrice) + ($remainder * $unitPrice);
        }

        return $quantity * $unitPrice;
    }
    
    protected function updateListTotal(ShoppingList $list)
    {
        $total = $list->listItems()->sum('item_total');
        $list->update(['total_value' => $total]);
    }
}
