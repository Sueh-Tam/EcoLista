<?php

namespace App\Http\Controllers;

use App\Models\ListItem;
use App\Models\ShoppingList;
use App\Http\Requests\StoreListItemRequest;
use App\Services\ShoppingListService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ListItemController extends Controller
{
    protected $service;

    public function __construct(ShoppingListService $service)
    {
        $this->service = $service;
    }

    public function store(StoreListItemRequest $request, ShoppingList $shoppingList)
    {
        if ($shoppingList->user_id !== Auth::id()) {
            abort(403);
        }

        $this->service->addItem($shoppingList, $request->validated());

        return redirect()->route('shopping-lists.show', $shoppingList);
    }

    public function update(StoreListItemRequest $request, ListItem $listItem)
    {
         if ($listItem->shoppingList->user_id !== Auth::id()) {
            abort(403);
        }
        
        $this->service->updateItem($listItem, $request->validated());

        return redirect()->route('shopping-lists.show', $listItem->shoppingList);
    }

    public function destroy(ListItem $listItem)
    {
         if ($listItem->shoppingList->user_id !== Auth::id()) {
            abort(403);
        }

        $list = $listItem->shoppingList;
        $this->service->removeItem($listItem);

        return redirect()->route('shopping-lists.show', $list);
    }
}
