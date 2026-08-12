<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_option_permissions', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('roles_id');
            $table->string('option_code', 255);
            $table->tinyInteger('allow')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['roles_id', 'option_code']);

            $table->foreign('roles_id')
                  ->references('role_id')
                  ->on('roles')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('option_code')
                  ->references('option_code')
                  ->on('role_options')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_option_permissions');
    }
};