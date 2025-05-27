<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            'Items Procured',
            'Quote Generated',
            'Invoice Sent',
            'Payment Pending',
            'Payment Received',
            'Completed',
        ];

        foreach ($statuses as $status) {
            Status::create(['name' => $status]);
        }
    }
}
