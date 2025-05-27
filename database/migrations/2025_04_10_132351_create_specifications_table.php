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
        Schema::create('specifications', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('group')->nullable(); // For grouping specs like "Technical", "Physical", etc.
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('product_specification', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('specification_id')->constrained()->cascadeOnDelete();
            $table->text('value');
            $table->timestamps();

            // A product should have each spec only once
            $table->unique(['product_id', 'specification_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_specification');
        Schema::dropIfExists('specifications');
    }
};
