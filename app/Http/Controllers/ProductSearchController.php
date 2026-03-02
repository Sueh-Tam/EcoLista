<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('query');

        if (!$query || strlen($query) < 2) {
            return response()->json([]);
        }

        $products = Product::with('brand')
            ->where('name', 'like', "%{$query}%")
            ->limit(10)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'brand_name' => $product->brand ? $product->brand->name : null,
                    'is_price_per_kg' => $product->is_price_per_kg,
                ];
            });

        return response()->json($products);
    }

    public function searchBrands(Request $request)
    {
        $query = $request->input('query');

        if (!$query || strlen($query) < 2) {
            return response()->json([]);
        }

        $brands = Brand::where('name', 'like', "%{$query}%")
            ->limit(10)
            ->get();

        return response()->json($brands);
    }
}
