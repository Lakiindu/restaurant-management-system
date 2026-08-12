<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->integer('page_id')->autoIncrement();
            $table->string('page_name', 45);
            $table->string('page_code', 255)->unique();
            $table->string('route_name', 255)->nullable();
            $table->string('description', 255)->nullable();
            $table->integer('category_id');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->tinyInteger('status')->default(1);

            $table->foreign('category_id')
                  ->references('category_id')
                  ->on('page_categories')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};