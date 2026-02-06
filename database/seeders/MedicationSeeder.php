<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MedicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('medications')->insert([
            [
                'name' => 'Biogesic',
                'dosage' => '500mg Tablet',
                'hardware_slot_id' => 1,
                'stock_level' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Neozep',
                'dosage' => '1 Tablet',
                'hardware_slot_id' => 2,
                'stock_level' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sanitary Napkin',
                'dosage' => '1 Pad',
                'hardware_slot_id' => 3,
                'stock_level' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
