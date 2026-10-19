<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no')->unique(); // e.g., ORD-0001
            $table->foreignId('table_id')->nullable()->constrained('dining_tables')->nullOnDelete();

            // Note: Users table primary key is user_id!
            $table->integer('waiter_id')->nullable();
            $table->foreign('waiter_id')->references('user_id')->on('users')->nullOnDelete();

            $table->enum('order_type', ['Dine-in', 'Takeaway'])->default('Dine-in');

            // Parked = Hold Order | Pending = Sent to Kitchen
            $table->enum('status', ['Parked', 'Pending', 'Preparing', 'Ready', 'Completed', 'Cancelled'])->default('Parked');

            $table->decimal('sub_total', 10, 2)->default(0);
            $table->decimal('vat_amount', 10, 2)->default(0); // 18% SL VAT
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('grand_total', 10, 2)->default(0);

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
