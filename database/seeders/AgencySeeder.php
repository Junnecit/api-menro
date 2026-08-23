<?php

namespace Database\Seeders;

use App\Models\Agency;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AgencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $agencies = [
            ['initials' => 'DE', 'name' => 'DENR Region X', 'type' => 'Government Agency', 'contact' => 'Juan dela Cruz', 'email' => 'denr10@gov.ph', 'phone' => '+63 88 123 4567', 'location' => 'Cagayan de Oro City', 'color' => 'bg-green-100 text-green-800', 'status' => 'Active'],
            ['initials' => 'CE', 'name' => 'City Environment Office', 'type' => 'Government Agency', 'contact' => 'Maria Santos', 'email' => 'ceo@cdo.gov.ph', 'phone' => '+63 88 234 5678', 'location' => 'Cagayan de Oro City', 'color' => 'bg-green-100 text-green-800', 'status' => 'Active'],
            ['initials' => 'LG', 'name' => 'LGU Opol', 'type' => 'Local Government', 'contact' => 'Pedro Reyes', 'email' => 'lgu@opol.gov.ph', 'phone' => '+63 88 345 6789', 'location' => 'Opol, Misamis Oriental', 'color' => 'bg-blue-100 text-blue-800', 'status' => 'Active'],
            ['initials' => 'MS', 'name' => 'Maria Santos', 'type' => 'Private Individual', 'contact' => 'Maria Santos', 'email' => 'maria.s@gmail.com', 'phone' => '+63 917 456 7890', 'location' => 'Iligan City', 'color' => 'bg-purple-100 text-purple-800', 'status' => 'Active'],
            ['initials' => 'BF', 'name' => 'Bukidnon Farmers Coop', 'type' => 'Cooperative', 'contact' => 'Jose Lim', 'email' => 'bfc@coop.ph', 'phone' => '+63 88 456 7891', 'location' => 'Malaybalay, Bukidnon', 'color' => 'bg-amber-100 text-amber-800', 'status' => 'Active'],
            ['initials' => 'JD', 'name' => 'Juan dela Cruz', 'type' => 'Private Individual', 'contact' => 'Juan dela Cruz', 'email' => 'juan.dc@yahoo.com', 'phone' => '+63 919 567 8901', 'location' => 'Misamis Oriental', 'color' => 'bg-pink-100 text-pink-800', 'status' => 'Inactive'],
            ['initials' => 'DS', 'name' => 'DOST Region X', 'type' => 'Government Agency', 'contact' => 'Ana Fernandez', 'email' => 'dost10@dost.gov.ph', 'phone' => '+63 88 567 8902', 'location' => 'Cagayan de Oro City', 'color' => 'bg-teal-100 text-teal-800', 'status' => 'Active'],
            ['initials' => 'GE', 'name' => 'Green Earth NGO', 'type' => 'NGO', 'contact' => 'Ben Morales', 'email' => 'info@greenearth.org', 'phone' => '+63 88 678 9013', 'location' => 'Cagayan de Oro City', 'color' => 'bg-green-100 text-green-800', 'status' => 'Active'],
            ['initials' => 'LT', 'name' => 'LGU Tagoloan', 'type' => 'Local Government', 'contact' => 'Rosa Villanueva', 'email' => 'lgu@tagoloan.gov.ph', 'phone' => '+63 88 789 0124', 'location' => 'Tagoloan, Misamis Oriental', 'color' => 'bg-blue-100 text-blue-800', 'status' => 'Active'],
            ['initials' => 'CR', 'name' => 'Carlo Reyes', 'type' => 'Private Individual', 'contact' => 'Carlo Reyes', 'email' => 'carlo.r@gmail.com', 'phone' => '+63 920 890 1235', 'location' => 'Gingoog City', 'color' => 'bg-orange-100 text-orange-800', 'status' => 'Inactive'],
            ['initials' => 'MP', 'name' => 'MisOr Provincial Office', 'type' => 'Government Agency', 'contact' => 'Ligaya Bautista', 'email' => 'province@misor.gov.ph', 'phone' => '+63 88 901 2346', 'location' => 'Cagayan de Oro City', 'color' => 'bg-green-100 text-green-800', 'status' => 'Active'],
            ['initials' => 'EF', 'name' => 'Ecotree Foundation', 'type' => 'NGO', 'contact' => 'Dani Soriano', 'email' => 'dani@ecotree.org', 'phone' => '+63 88 012 3457', 'location' => 'Iligan City', 'color' => 'bg-teal-100 text-teal-800', 'status' => 'Active'],
        ];

        foreach ($agencies as &$agency) {
            $agency['created_at'] = $now;
            $agency['updated_at'] = $now;
        }

        Agency::insert($agencies);
    }
}
