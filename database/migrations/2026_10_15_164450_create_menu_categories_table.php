<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('color', 20)->default('#4f46e5'); // 🎨 For colored cards in POS!
            $table->string('icon', 50)->nullable();          // 🖼️ Bootstrap icon class
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->integer('sort_order')->default(0);       // For sorting in POS
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_categories');
    }
};
