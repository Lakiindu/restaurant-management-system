<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('grns', function (Blueprint $table) {
            $table->id();
            $table->string('grn_no', 50)->unique();
            $table->foreignId('store_id')->constrained('stores')->onDelete('restrict');
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('restrict');

            $table->date('grn_date');
            $table->string('ref_no', 100)->nullable();
            $table->text('note')->nullable();

            // Financials
            $table->decimal('sub_total', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);

            // 🔑 Fixed: Pointing explicitly to users.user_id
            $table->integer('created_by');
            $table->foreign('created_by')
                ->references('user_id')
                ->on('users')
                ->onDelete('restrict');

            $table->string('status', 20)->default('Completed');
            $table->timestamps();
        });

        Schema::create('grn_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grn_id')->constrained('grns')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('restrict');

            $table->decimal('qty', 10, 2)->default(0);
            $table->decimal('cost', 15, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('batch_exp', 100)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grn_items');
        Schema::dropIfExists('grns');
    }
};
