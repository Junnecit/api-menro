<?php

namespace Database\Seeders;

use App\Models\Agency;
use App\Models\Request as PlantingRequest;
use Illuminate\Database\Seeder;

class RequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $agencyIdByName = Agency::pluck('id', 'name');

        $requests = [
            ['request_no' => '#00128', 'agency' => 'DENR Region X', 'requester_name' => 'Juan dela Cruz', 'location' => 'Bukidnon', 'status' => 'Pending', 'request_date' => '2026-06-22'],
            ['request_no' => '#00127', 'agency' => null, 'requester_name' => 'Juan dela Cruz', 'location' => 'Misamis Oriental', 'status' => 'Approved', 'request_date' => '2026-06-20'],
            ['request_no' => '#00126', 'agency' => 'City Environment Office', 'requester_name' => null, 'location' => 'Cagayan de Oro', 'status' => 'Completed', 'request_date' => '2026-06-18'],
            ['request_no' => '#00125', 'agency' => 'LGU Opol', 'requester_name' => null, 'location' => 'Misamis Oriental', 'status' => 'Rejected', 'request_date' => '2026-06-15'],
            ['request_no' => '#00124', 'agency' => null, 'requester_name' => 'Maria Santos', 'location' => 'Iligan City', 'status' => 'In Progress', 'request_date' => '2026-06-12'],
        ];

        foreach ($requests as $request) {
            PlantingRequest::create([
                'request_no' => $request['request_no'],
                'agency_id' => $request['agency'] ? $agencyIdByName[$request['agency']] ?? null : null,
                'requester_name' => $request['requester_name'],
                'location' => $request['location'],
                'status' => $request['status'],
                'request_date' => $request['request_date'],
            ]);
        }
    }
}
