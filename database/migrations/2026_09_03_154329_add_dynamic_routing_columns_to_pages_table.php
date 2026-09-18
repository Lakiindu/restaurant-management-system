<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('url_path', 255)->nullable()->after('route_name');
            $table->string('controller_name', 255)->nullable()->after('url_path');
            $table->string('method_name', 100)->nullable()->default('index')->after('controller_name');
            $table->string('http_method', 10)->nullable()->default('GET')->after('method_name');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['url_path', 'controller_name', 'method_name', 'http_method']);
        });
    }
};
