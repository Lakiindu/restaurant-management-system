<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_item_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_item_id')->constrained('menu_items')->onDelete('cascade');
            $table->foreignId('inventory_item_id')->constrained('items')->onDelete('cascade');
            $table->decimal('quantity', 10, 3); // Quantity of raw material per portion (e.g., 0.250 kg)
            $table->timestamps();

            $table->unique(['menu_item_id', 'inventory_item_id'], 'menu_item_ingredient_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_item_ingredients');
    }
};
