<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('role_id');
            $table->string('page_code', 255);
            $table->tinyInteger('allow')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['role_id', 'page_code']);

            $table->foreign('role_id')
                  ->references('role_id')
                  ->on('roles')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('page_code')
                  ->references('page_code')
                  ->on('pages')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};