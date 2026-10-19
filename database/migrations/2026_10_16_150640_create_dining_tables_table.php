<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dining_tables', function (Blueprint $table) {
            $table->id();
            $table->string('table_number')->unique(); // e.g., T-01
            $table->integer('capacity')->default(4);
            $table->enum('status', ['Available', 'Occupied', 'Reserved'])->default('Available');
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('dining_tables');
    }
};
