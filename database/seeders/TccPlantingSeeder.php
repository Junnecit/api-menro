<?php

namespace Database\Seeders;

use App\Enums\PlantingHabitat;
use App\Enums\TreeStatus;
use App\Enums\UserStatus;
use App\Models\Agency;
use App\Models\PlantingMonitoring;
use App\Models\Request as PlantingRequest;
use App\Models\Role;
use App\Models\Tree;
use App\Models\TreePhoto;
use App\Models\User;
use App\Support\TagoloanLocation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TccPlantingSeeder extends Seeder
{
    private const REGION_CODE = '100000000';
    private const PROVINCE_CODE = '104300000';
    private const SANTA_ANA_BARANGAY = '104324008'; // Calabat is located within Brgy. Santa Ana

    public function run(): void
    {
        $municipalityCode = config('tagoloan.municipality_code', '104324000');
        $adminRole = Role::where('slug', 'admin')->first();
        $planterRole = Role::where('slug', 'user')->first();

        // 1. Partner Agency: Tagoloan Community College (TCC)
        $agency = Agency::updateOrCreate(
            ['email' => 'tcc.forestry@tagoloan.edu.ph'],
            [
                'initials'          => 'TCC',
                'name'              => 'Tagoloan Community College',
                'type'              => 'Local Government',
                'contact'           => 'TCC Forestry & Greening Program',
                'phone'             => '+63 88 567 1024',
                'region_code'       => self::REGION_CODE,
                'province_code'     => self::PROVINCE_CODE,
                'municipality_code' => $municipalityCode,
                'barangay_code'     => self::SANTA_ANA_BARANGAY,
                'location'          => 'Sitio Calabat, ' . TagoloanLocation::formatLocation(self::SANTA_ANA_BARANGAY),
                'custom_address'    => 'Sitio Calabat, Brgy. Santa Ana, Tagoloan, Misamis Oriental',
                'color'             => 'bg-emerald-100 text-emerald-800',
                'status'            => 'Active',
            ]
        );

        // 2. Lead Admin / Coordinator for TCC
        $adminUser = null;
        if ($adminRole) {
            $adminUser = User::updateOrCreate(
                ['email' => 'admin.tcc@tagoloan.demo'],
                [
                    'role_id'           => $adminRole->id,
                    'agency_id'         => $agency->id,
                    'name'              => 'TCC Project Coordinator',
                    'password'          => 'MenroTcc2026!',
                    'status'            => UserStatus::Active,
                    'phone'             => '+63 917 123 4567',
                    'address'           => TagoloanLocation::formatLocation(self::SANTA_ANA_BARANGAY),
                    'email_verified_at' => now(),
                ]
            );
        }

        // 3. Planting Request based on cleaned data
        // Date Planted: March 23, 2026 | Area: Calabat | Seedling: Pine | Total: 60
        $plantingRequest = PlantingRequest::updateOrCreate(
            ['request_no' => '#REQ-TCC-2026-001'],
            [
                'parent_id'       => null,
                'user_id'         => $adminUser?->id,
                'agency_id'       => $agency->id,
                'requester_name'  => 'Tagoloan Community College (TCC)',
                'project_name'    => 'TCC Calabat Upland Pine Greening Project',
                'habitat'         => PlantingHabitat::Terrestrial->value,
                'target_trees'    => 60,
                'barangay_code'   => self::SANTA_ANA_BARANGAY,
                'location'        => 'Sitio Calabat, Santa Ana, Tagoloan, Misamis Oriental',
                'custom_address'  => 'Upper Calabat Reforestation Site, Tagoloan',
                'seedling_draft'  => [
                    'species' => ['Pine'],
                    'raw'     => 'Pine',
                    'source'  => 'tcc_planting_sheet',
                ],
                'status'          => 'Approved',
                'request_date'    => '2026-03-23',
            ]
        );

        // 4. Planting Monitoring Record (Report Center KPI basis)
        PlantingMonitoring::updateOrCreate(
            [
                'request_id'    => $plantingRequest->id,
                'seedling_type' => 'Pine',
            ],
            [
                'date_monitoring'   => null, // Pending first formal monitoring date
                'seedlings_planted' => 60,
                'replanted_count'   => 0,
                'survived_count'    => 60,   // Initial full batch survival count
                'died_count'        => 0,
            ]
        );

        // 5. Seed 60 Individual Tree Rows in Sitio Calabat (Coordinates: ~8.5220 N, 124.7920 E)
        $hubLat = 8.5220;
        $hubLng = 124.7920;
        $plantDate = Carbon::parse('2026-03-23');

        for ($t = 1; $t <= 60; $t++) {
            $clientUuid = sprintf('seed-tcc-calabat-pine-%03d', $t);
            $treeCode   = sprintf('TGL-2026-TCC-%04d', $t);

            // Spiral coordinate offset around Calabat reforestation hub
            $turns = 3.0;
            $angle = ($t / 60) * 2 * M_PI * $turns;
            $radiusDeg = 0.00015 + (($t / 60) * 0.0007);
            $lngScale = cos(deg2rad($hubLat));
            $lat = round($hubLat + ($radiusDeg * cos($angle)), 7);
            $lng = round($hubLng + (($radiusDeg * sin($angle)) / max(0.2, $lngScale)), 7);

            $tree = Tree::updateOrCreate(
                ['client_uuid' => $clientUuid],
                [
                    'request_id'     => $plantingRequest->id,
                    'agency_id'      => $agency->id,
                    'recorded_by_id' => $adminUser?->id,
                    'species'        => 'Pinus kesiya',
                    'common_name'    => 'Pine',
                    'status'         => TreeStatus::Alive->value,
                    'date_planted'   => $plantDate->toDateString(),
                    'date_recorded'  => $plantDate->toDateString(),
                    'barangay'       => 'Santa Ana',
                    'municipality'   => 'Tagoloan',
                    'province'       => 'Misamis Oriental',
                    'latitude'       => $lat,
                    'longitude'      => $lng,
                    'landmark'       => sprintf('Sitio Calabat pine marker #%d', $t),
                    'notes'          => 'Seeded tree from TCC Calabat Pine planting initiative.',
                ]
            );

            if (! $tree->tree_code) {
                $tree->update(['tree_code' => $treeCode]);
            }

            // Attach photo references to the first few trees as photo references
            if ($t <= 3) {
                $photoPath = ($t % 2 === 1) 
                    ? 'tree-photos/pine_seedling_nursery.jpg' 
                    : 'tree-photos/pine_seedling_field.jpg';

                TreePhoto::firstOrCreate(
                    [
                        'tree_id' => $tree->id,
                        'path'    => $photoPath,
                    ],
                    [
                        'capture_mode' => 'close_up',
                        'angle'        => 'F',
                    ]
                );
            }
        }
    }
}
