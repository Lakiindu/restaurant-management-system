<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_sub_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_group_id')
                ->constrained('item_groups')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            // optional: prevent duplicate names under same group
            $table->unique(['item_group_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_sub_groups');
    }
};
