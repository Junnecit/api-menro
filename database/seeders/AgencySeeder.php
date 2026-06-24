<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Agency;

class AgencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Agency::insert([
    [
        'initials' => 'DE',
        'name' => 'DENR Region X',
        'soil_type' => 'Clay loam, slightly acidic',
        'color' => 'bg-green-100 text-green-800',
    ],
    [
        'initials' => 'CE',
        'name' => 'City Environment Office',
        'soil_type' => 'Sandy loam, well-drained',
        'color' => 'bg-green-100 text-green-800',
    ],
    [
        'initials' => 'LG',
        'name' => 'LGU Opol',
        'soil_type' => 'Silty clay, high moisture',
        'color' => 'bg-green-100 text-green-800',
    ],
    [
        'initials' => 'MS',
        'name' => 'Maria Santos',
        'soil_type' => 'Volcanic ash, fertile',
        'color' => 'bg-blue-100 text-blue-800',
    ]
]);
    }
}
