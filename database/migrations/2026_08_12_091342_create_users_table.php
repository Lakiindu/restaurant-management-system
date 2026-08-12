<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->integer('user_id')->autoIncrement();
            $table->string('user_name', 45)->unique();
            $table->string('email', 100)->unique();
            $table->string('password', 255);
            $table->tinyInteger('must_change_password')->default(0);
            $table->string('remember_token', 255)->nullable();
            $table->integer('role_id');
            $table->integer('employee_id')->nullable();
            $table->integer('branch_id')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->tinyInteger('status')->default(1);

            $table->foreign('role_id')
                  ->references('role_id')
                  ->on('roles')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};