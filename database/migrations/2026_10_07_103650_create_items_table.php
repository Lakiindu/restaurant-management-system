<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();

            // Basic Information
            $table->string('name', 150);
            $table->foreignId('item_type_id')->nullable()->constrained('item_types')->onDelete('set null');
            $table->string('stock_type', 50)->default('Stock'); // Stock, Non-Stock

            // Classification
            $table->foreignId('item_category_id')->constrained('item_categories')->onDelete('restrict');
            $table->foreignId('item_sub_category_id')->nullable()->constrained('item_sub_categories')->onDelete('set null');
            $table->foreignId('item_group_id')->nullable()->constrained('item_groups')->onDelete('set null');
            $table->foreignId('item_sub_group_id')->nullable()->constrained('item_sub_groups')->onDelete('set null');

            // Codes, Units & Warranty
            $table->string('item_code', 50)->nullable()->unique();
            $table->string('barcode', 100)->nullable();
            $table->string('unit_measure', 50); // e.g., kg, pcs
            $table->string('unit_size', 50)->nullable();
            $table->string('warranty', 100)->nullable();

            // Pricing & Discount
            $table->decimal('cost_price', 15, 2)->default(0);
            $table->decimal('sales_price', 15, 2)->default(0);
            $table->string('discount_type', 50)->nullable();

            // Stock Controls
            $table->decimal('reorder_level', 10, 2)->nullable();
            $table->decimal('max_sales_qty', 10, 2)->nullable();

            // Descriptions
            $table->text('client_description');
            $table->text('office_description');

            // Status
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
