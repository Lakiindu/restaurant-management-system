<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_groups', function (Blueprint $table) {
            $table->id(); // group_id
            $table->string('name', 100)->unique();
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(1); // 1=Active, 0=Inactive
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_groups');
    }
};
