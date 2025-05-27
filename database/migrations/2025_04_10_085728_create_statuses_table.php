<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // Insert default statuses
        DB::table('statuses')->insert([
            ['name' => 'Quote Generated', 'description' => 'Initial quote generated for client', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Invoice Sent', 'description' => 'Invoice has been sent to client', 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Payment Pending', 'description' => 'Waiting for payment from client', 'order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Payment Received', 'description' => 'Payment has been received', 'order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Completed', 'description' => 'Sale has been completed', 'order' => 5, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('statuses');
    }
};
