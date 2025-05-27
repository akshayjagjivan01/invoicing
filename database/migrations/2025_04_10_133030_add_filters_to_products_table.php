<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('brand_id')->nullable()->after('sku')->constrained()->nullOnDelete();
            $table->foreignId('size_id')->nullable()->after('brand_id')->constrained()->nullOnDelete();
            $table->json('attributes')->nullable()->after('description'); // For any additional flexible attributes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropForeign(['size_id']);
            $table->dropColumn(['brand_id', 'size_id', 'attributes']);
        });
    }
};
