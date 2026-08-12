<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_options', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('option_name', 45);
            $table->string('option_code', 255)->unique();
            $table->integer('page_id');
            $table->timestamp('created_at')->useCurrent();
            $table->tinyInteger('status')->default(1);

            $table->foreign('page_id')
                  ->references('page_id')
                  ->on('pages')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_options');
    }
};