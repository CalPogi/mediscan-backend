<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SchoolDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {

        $schoolData = [
            'Grade 7' => [
                '7 AMETHYST',
                '7 SPJ DIAMOND',
                '7 GARNET',
                '7 JADE',
                '7 STE MALACHITE',
                '7 PEARL',
                '7 SAPPHIRE'
            ],
            'Grade 8' => [
                '8 ACACIA',
                '8 IPIL',
                '8 SPJ MAHOGANY',
                '8 MOLAVE',
                '8 NARRA',
                '8 STE TALISAY'
            ],
            'Grade 9' => [
                '9 CATTLEYA',
                '9 CHAMPACA',
                '9 ROSE',
                '9 SAMPAGUITA',
                '9 TULIP',
                '9 SPJ JASMINE'
            ],
            'Grade 10' => [
                '10 AGUINALDO',
                '10 BONIFACIO',
                '10 DEL PILAR',
                '10 LUNA',
                '10 SPJ JACINTO',
                '10 RIZAL'
            ],
            'Grade 11' => [
                '11 ABM R.Lopez',
                '11 ICT D. Banatao',
                '11 HUMSS 1 F. Timbreza',
                '11 HUMSS 2 C. Osias',
                '11 HUMSS 3 I. Delos Reyes',
                '11 STEM G. Zara'
            ],
            'Grade 12' => [
                '12 ABM 1',
                '12 ABM 2',
                '12 ICT',
                '12 HUMSS 1',
                '12 HUMSS 2',
                '12 STEM'
            ]
        ];

        foreach ($schoolData as $gradeName => $sections) {

            $gradeId = DB::table('grade_levels')->insertGetId([
                'name' => $gradeName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);


            foreach ($sections as $sectionName) {
                DB::table('sections')->insert([
                    'grade_level_id' => $gradeId,
                    'name' => $sectionName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
