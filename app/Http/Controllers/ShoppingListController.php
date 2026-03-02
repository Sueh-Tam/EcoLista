<?php

namespace App\Http\Controllers;

use App\Models\ShoppingList;
use App\Services\ShoppingListService;
use App\Http\Requests\StoreShoppingListRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShoppingListController extends Controller
{
    protected $service;

    public function __construct(ShoppingListService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $lists = Auth::user()->shoppingLists()->withCount('listItems')->orderBy('created_at', 'desc')->get();
        return view('shopping_lists.index', compact('lists'));
    }

    public function create()
    {
        return view('shopping_lists.create');
    }

    public function store(StoreShoppingListRequest $request)
    {
        $list = $this->service->createList(Auth::user(), [
            'creation_date' => now(),
            'total_value' => 0,
            'market_name' => $request->market_name,
            'notes' => $request->notes,
        ]);

        return redirect()->route('shopping-lists.show', $list);
    }

    public function show(ShoppingList $shoppingList)
    {
        if ($shoppingList->user_id !== Auth::id()) {
            abort(403);
        }

        $shoppingList->load('listItems.product.brand');
        return view('shopping_lists.show', compact('shoppingList'));
    }

    public function complete(ShoppingList $shoppingList)
    {
        if ($shoppingList->user_id !== Auth::id()) {
            abort(403);
        }

        $shoppingList->update(['is_completed' => true]);

        return redirect()->route('shopping-lists.index')->with('success', 'Lista finalizada com sucesso!');
    }

    public function destroy(ShoppingList $shoppingList)
    {
        if ($shoppingList->user_id !== Auth::id()) {
            abort(403);
        }

        $shoppingList->delete();
        return redirect()->route('shopping-lists.index')->with('success', 'Lista removida com sucesso!');
    }
}
