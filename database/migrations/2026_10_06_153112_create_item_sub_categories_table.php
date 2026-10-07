<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_sub_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_category_id')
                ->constrained('item_categories')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            // Prevent duplicate sub-category names under the same category
            $table->unique(['item_category_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_sub_categories');
    }
};
