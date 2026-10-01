<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->integer('item_id')->autoIncrement();
            $table->string('item_name', 100);
            $table->string('item_code', 50)->unique();
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('type_id');
            $table->string('unit', 20)->default('pcs'); // kg, g, ml, L, pcs
            $table->decimal('current_stock', 10, 2)->default(0);
            $table->decimal('minimum_stock', 10, 2)->default(0);
            $table->string('description', 255)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('type_id')
                ->references('type_id')
                ->on('inventory_types')
                ->onDelete('restrict')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
