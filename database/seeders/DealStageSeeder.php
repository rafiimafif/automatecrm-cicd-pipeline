<?php

namespace Database\Seeders;

use App\Models\DealStage;
use Illuminate\Database\Seeder;

class DealStageSeeder extends Seeder
{
    public function run()
    {
        $stages = [
            ['name' => 'Lead', 'order' => 1, 'color' => '#858e96'],
            ['name' => 'Qualified', 'order' => 2, 'color' => '#36b9cc'],
            ['name' => 'Proposal', 'order' => 3, 'color' => '#4e73df'],
            ['name' => 'Negotiation', 'order' => 4, 'color' => '#f6c23e'],
            ['name' => 'Closed Won', 'order' => 5, 'color' => '#1cc88a'],
            ['name' => 'Closed Lost', 'order' => 6, 'color' => '#e74a3b'],
        ];

        foreach ($stages as $stage) {
            DealStage::firstOrCreate(['name' => $stage['name']], $stage);
        }

        echo "[seeder] Seeded deal stages.\n";
    }
}
