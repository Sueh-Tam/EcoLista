<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShoppingListController;
use App\Http\Controllers\ListItemController;
use App\Http\Controllers\ProductSearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return redirect()->route('shopping-lists.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('shopping-lists', ShoppingListController::class);
    Route::post('shopping-lists/{shoppingList}/complete', [ShoppingListController::class, 'complete'])->name('shopping-lists.complete');
    Route::post('shopping-lists/{shoppingList}/items', [ListItemController::class, 'store'])->name('shopping-lists.items.store');
    Route::put('list-items/{listItem}', [ListItemController::class, 'update'])->name('list-items.update');
    Route::delete('list-items/{listItem}', [ListItemController::class, 'destroy'])->name('list-items.destroy');

    Route::get('/api/products/search', [ProductSearchController::class, 'search'])->name('api.products.search');
    Route::get('/api/brands/search', [ProductSearchController::class, 'searchBrands'])->name('api.brands.search');
});

require __DIR__.'/auth.php';
