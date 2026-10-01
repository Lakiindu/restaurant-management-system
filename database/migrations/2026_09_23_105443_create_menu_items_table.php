<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->integer('item_id')->autoIncrement();
            $table->string('item_name', 100); // e.g., "Chicken Fried Rice"
            $table->string('item_code', 50)->unique(); // e.g., "FR-CHK-01"
            $table->integer('category_id'); // Link to menu_categories
            $table->decimal('price', 10, 2)->default(0.00); // Selling price to customer
            $table->string('description', 255)->nullable(); // e.g., "Spicy fried rice with roasted chicken"
            $table->string('image', 255)->nullable(); // Photo for POS/Digital Menu
            $table->tinyInteger('is_vegetarian')->default(0); // 1 = Veg, 0 = Non-Veg
            $table->tinyInteger('status')->default(1); // 1 = Available, 0 = Sold Out
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('category_id')
                ->references('category_id')
                ->on('menu_categories')
                ->onDelete('restrict')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
