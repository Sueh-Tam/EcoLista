<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Produto com preço por kg
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_price_per_kg')->default(false)->after('description');
        });

        // 2. Local da compra na lista de compras
        Schema::table('shopping_lists', function (Blueprint $table) {
            $table->string('market_name')->nullable()->after('user_id');
            $table->index('market_name');
        });

        // 3. Alterar quantidade para decimal em list_items para suportar KG
        Schema::table('list_items', function (Blueprint $table) {
            $table->decimal('quantity', 10, 3)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('is_price_per_kg');
        });

        Schema::table('shopping_lists', function (Blueprint $table) {
            $table->dropIndex(['market_name']);
            $table->dropColumn('market_name');
        });

        Schema::table('list_items', function (Blueprint $table) {
            $table->integer('quantity')->change();
        });
    }
};
