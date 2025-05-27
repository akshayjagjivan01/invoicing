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
        Schema::create('sizes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('value');
            $table->string('dimension_type')->nullable(); // e.g., length, weight, volume
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Make name and dimension_type unique together
            $table->unique(['name', 'dimension_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sizes');
    }
};
