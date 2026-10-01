<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_categories', function (Blueprint $table) {
            $table->integer('category_id')->autoIncrement();
            $table->string('category_name', 100)->unique(); // e.g., "Fried Rice", "Noodles"
            $table->string('description', 255)->nullable();
            $table->string('image', 255)->nullable(); // Optional icon/image for POS
            $table->tinyInteger('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_categories');
    }
};
