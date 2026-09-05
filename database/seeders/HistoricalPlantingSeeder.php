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

class HistoricalPlantingSeeder extends Seeder
{
    private const REGION_CODE = '100000000';
    private const PROVINCE_CODE = '104300000';

    // PSGC Barangay Codes for Tagoloan (matching config/tagoloan.php)
    private const BALUARTE    = '104324001';
    private const CASINGLOT   = '104324002';
    private const POBLACION   = '104324006';
    private const ROSARIO     = '104324007';
    private const SANTA_ANA   = '104324008';
    private const SANTA_CRUZ  = '104324009';

    /**
     * Geographical planting hubs in Tagoloan (lat, lng)
     */
    private function geoHubs(): array
    {
        return [
            // Estuarine coastal mangrove zone in Baluarte / Nabulod
            'baluarte-mangrove' => ['lat' => 8.5410, 'lng' => 124.7520],
            // Sitio Calabat / Santa Ana upland reforestation hills
            'calabat-upland'    => ['lat' => 8.5220, 'lng' => 124.7920],
            // Santa Ana community area
            'santa-ana-center'  => ['lat' => 8.5245, 'lng' => 124.7885],
            // Rosario upland forest and agroforestry zone
            'rosario-forest'    => ['lat' => 8.5368, 'lng' => 124.8305],
            // Santa Cruz bamboo riparian zone
            'santa-cruz'        => ['lat' => 8.5460, 'lng' => 124.7780],
            // Tagoloan municipal poblacion
            'poblacion'         => ['lat' => 8.5365, 'lng' => 124.7780],
        ];
    }

    /**
     * Scientific species dictionary
     */
    private function speciesInfo(string $rawSpecies): array
    {
        return match ($rawSpecies) {
            'Mangrove' => [
                'scientific' => 'Rhizophora apiculata',
                'common'     => 'Mangrove',
                'photo'      => 'tree-photos/mangrove_seedling.jpg',
            ],
            'Bamboo' => [
                'scientific' => 'Bambusa blumeana',
                'common'     => 'Bamboo',
                'photo'      => 'tree-photos/pine_seedling_field.jpg',
            ],
            'Narra/Naga' => [
                'scientific' => 'Pterocarpus indicus',
                'common'     => 'Narra',
                'photo'      => 'tree-photos/pine_seedling_nursery.jpg',
            ],
            'Acacia' => [
                'scientific' => 'Samanea saman',
                'common'     => 'Acacia',
                'photo'      => 'tree-photos/pine_seedling_field.jpg',
            ],
            'Mahogany' => [
                'scientific' => 'Swietenia macrophylla',
                'common'     => 'Mahogany',
                'photo'      => 'tree-photos/pine_seedling_nursery.jpg',
            ],
            'Fire Tree' => [
                'scientific' => 'Delonix regia',
                'common'     => 'Fire Tree',
                'photo'      => 'tree-photos/pine_seedling_field.jpg',
            ],
            'Fire Tree/Palcata' => [
                'scientific' => 'Falcataria moluccana',
                'common'     => 'Falcata / Fire Tree',
                'photo'      => 'tree-photos/pine_seedling_field.jpg',
            ],
            'TAMBIS/MAKOPA' => [
                'scientific' => 'Syzygium samarangense',
                'common'     => 'Tambis / Makopa',
                'photo'      => 'tree-photos/pine_seedling_nursery.jpg',
            ],
            'Bamboo/Fruit Trees' => [
                'scientific' => 'Bambusa / Syzygium spp.',
                'common'     => 'Bamboo & Fruit Trees',
                'photo'      => 'tree-photos/pine_seedling_field.jpg',
            ],
            'Fruit Trees' => [
                'scientific' => 'Artocarpus heterophyllus',
                'common'     => 'Fruit Trees',
                'photo'      => 'tree-photos/pine_seedling_nursery.jpg',
            ],
            'Bagras' => [
                'scientific' => 'Eucalyptus deglupta',
                'common'     => 'Bagras',
                'photo'      => 'tree-photos/pine_seedling_field.jpg',
            ],
            'Pine' => [
                'scientific' => 'Pinus kesiya',
                'common'     => 'Pine',
                'photo'      => 'tree-photos/pine_seedling_field.jpg',
            ],
            default => [
                'scientific' => 'Indigenous Forest Tree',
                'common'     => $rawSpecies ?: 'Indigenous Tree',
                'photo'      => 'tree-photos/pine_seedling_field.jpg',
            ],
        };
    }

    public function run(): void
    {
        $municipalityCode = config('tagoloan.municipality_code', '104324000');
        $adminRole = Role::where('slug', 'admin')->first();
        $hubs = $this->geoHubs();

        // ─────────────────────────────────────────────────────────────────
        // 1. REGISTER ALL PARTICIPATING AGENCIES & BARANGAY LGUs
        // ─────────────────────────────────────────────────────────────────
        $agenciesDefinitions = [
            'TCC' => [
                'initials'      => 'TCC',
                'name'          => 'Tagoloan Community College',
                'type'          => 'Local Government',
                'contact'       => 'TCC Forestry & Greening Program',
                'email'         => 'tcc.forestry@tagoloan.edu.ph',
                'phone'         => '+63 88 567 1024',
                'barangay_code' => self::SANTA_ANA,
                'location'      => 'Sitio Calabat, Santa Ana, Tagoloan',
                'color'         => 'bg-emerald-100 text-emerald-800',
            ],
            'MENRO' => [
                'initials'      => 'MENRO',
                'name'          => 'LGU Tagoloan MENRO',
                'type'          => 'Local Government',
                'contact'       => 'Municipal Environment Office',
                'email'         => 'menro@tagoloan.gov.ph',
                'phone'         => '+63 88 567 1001',
                'barangay_code' => self::POBLACION,
                'location'      => 'Municipal Hall, Poblacion, Tagoloan',
                'color'         => 'bg-blue-100 text-blue-800',
            ],
            'LGU' => [
                'initials'      => 'LGU',
                'name'          => 'Municipality of Tagoloan LGU',
                'type'          => 'Local Government',
                'contact'       => 'Office of the Municipal Mayor',
                'email'         => 'mayor@tagoloan.gov.ph',
                'phone'         => '+63 88 567 1000',
                'barangay_code' => self::POBLACION,
                'location'      => 'Poblacion, Tagoloan, Misamis Oriental',
                'color'         => 'bg-blue-100 text-blue-800',
            ],
            'MDRR/MENRO' => [
                'initials'      => 'MDRR',
                'name'          => 'Tagoloan MDRRMO & MENRO',
                'type'          => 'Local Government',
                'contact'       => 'Disaster Risk & Watershed Unit',
                'email'         => 'mdrrmo@tagoloan.gov.ph',
                'phone'         => '+63 88 567 1005',
                'barangay_code' => self::POBLACION,
                'location'      => 'Poblacion, Tagoloan',
                'color'         => 'bg-teal-100 text-teal-800',
            ],
            'SENIOR HIGH' => [
                'initials'      => 'TSHS',
                'name'          => 'Tagoloan Senior High School',
                'type'          => 'Local Government',
                'contact'       => 'Youth for Environment in Schools (YES-O)',
                'email'         => 'seniorhigh@tagoloan.edu.ph',
                'phone'         => '+63 88 567 1030',
                'barangay_code' => self::BALUARTE,
                'location'      => 'Baluarte, Tagoloan',
                'color'         => 'bg-indigo-100 text-indigo-800',
            ],
            'BFP' => [
                'initials'      => 'BFP',
                'name'          => 'Bureau of Fire Protection - Tagoloan',
                'type'          => 'Government Agency',
                'contact'       => 'BFP Environmental Action Team',
                'email'         => 'bfp.tagoloan@dilg.gov.ph',
                'phone'         => '+63 88 567 1199',
                'barangay_code' => self::POBLACION,
                'location'      => 'Poblacion, Tagoloan',
                'color'         => 'bg-orange-100 text-orange-800',
            ],
            'BJMP' => [
                'initials'      => 'BJMP',
                'name'          => 'BJMP - Tagoloan Municipal Jail',
                'type'          => 'Government Agency',
                'contact'       => 'Community Reintegration Greening',
                'email'         => 'bjmp.tagoloan@bjmp.gov.ph',
                'phone'         => '+63 88 567 1150',
                'barangay_code' => self::BALUARTE,
                'location'      => 'Baluarte, Tagoloan',
                'color'         => 'bg-purple-100 text-purple-800',
            ],
            'COAST GUARD' => [
                'initials'      => 'PCG',
                'name'          => 'Philippine Coast Guard - Tagoloan Sub-Station',
                'type'          => 'Government Agency',
                'contact'       => 'Marine Environmental Protection Unit',
                'email'         => 'pcg.tagoloan@coastguard.gov.ph',
                'phone'         => '+63 88 567 1088',
                'barangay_code' => self::BALUARTE,
                'location'      => 'Macajalar Bay Sub-Station, Baluarte',
                'color'         => 'bg-sky-100 text-sky-800',
            ],
            'SAN MIGUEL' => [
                'initials'      => 'SMC',
                'name'          => 'San Miguel Corporation Tagoloan',
                'type'          => 'Private Individual',
                'contact'       => 'SMC Corporate Social Responsibility',
                'email'         => 'csr@sanmiguel.com.ph',
                'phone'         => '+63 88 567 2000',
                'barangay_code' => self::SANTA_ANA,
                'location'      => 'Santa Ana, Tagoloan',
                'color'         => 'bg-amber-100 text-amber-800',
            ],
            'FDC' => [
                'initials'      => 'FDC',
                'name'          => 'FDC Misamis Power Corporation',
                'type'          => 'Private Individual',
                'contact'       => 'FDC Environmental Stewardship Unit',
                'email'         => 'environment@fdcmisamis.com',
                'phone'         => '+63 88 567 3000',
                'barangay_code' => self::SANTA_CRUZ,
                'location'      => 'PHIVIDEC Industrial Estate, Santa Cruz',
                'color'         => 'bg-yellow-100 text-yellow-800',
            ],
            'VIPEL' => [
                'initials'      => 'VIPEL',
                'name'          => 'VIPEL Corporation',
                'type'          => 'Private Individual',
                'contact'       => 'VIPEL Community Relations',
                'email'         => 'info@vipelcorp.ph',
                'phone'         => '+63 88 567 4000',
                'barangay_code' => self::BALUARTE,
                'location'      => 'Baluarte, Tagoloan',
                'color'         => 'bg-rose-100 text-rose-800',
            ],
            'BOSTIK' => [
                'initials'      => 'BSTK',
                'name'          => 'Bostik Philippines Inc. Tagoloan',
                'type'          => 'Private Individual',
                'contact'       => 'Bostik Sustainability Program',
                'email'         => 'tagoloan.plant@bostik.com',
                'phone'         => '+63 88 567 5000',
                'barangay_code' => self::ROSARIO,
                'location'      => 'Rosario, Tagoloan',
                'color'         => 'bg-cyan-100 text-cyan-800',
            ],
            'ARCHEM' => [
                'initials'      => 'ARCH',
                'name'          => 'Asian Chemical Corporation (ARCHEM)',
                'type'          => 'Private Individual',
                'contact'       => 'ARCHEM Environmental Care',
                'email'         => 'compliance@archem.ph',
                'phone'         => '+63 88 567 6000',
                'barangay_code' => self::SANTA_ANA,
                'location'      => 'Santa Ana, Tagoloan',
                'color'         => 'bg-lime-100 text-lime-800',
            ],
            // Barangay Local Governments
            'BSA' => [
                'initials'      => 'BSA',
                'name'          => 'Barangay Santa Ana LGU',
                'type'          => 'Local Government',
                'contact'       => 'Barangay Captain / Council on Environment',
                'email'         => 'brgy.santa-ana@tagoloan.demo',
                'phone'         => '+63 88 567 1018',
                'barangay_code' => self::SANTA_ANA,
                'location'      => 'Barangay Hall, Santa Ana, Tagoloan',
                'color'         => 'bg-emerald-100 text-emerald-800',
            ],
            'BRO' => [
                'initials'      => 'BRO',
                'name'          => 'Barangay Rosario LGU',
                'type'          => 'Local Government',
                'contact'       => 'Barangay Captain / Greening Task Force',
                'email'         => 'brgy.rosario@tagoloan.demo',
                'phone'         => '+63 88 567 1017',
                'barangay_code' => self::ROSARIO,
                'location'      => 'Barangay Hall, Rosario, Tagoloan',
                'color'         => 'bg-green-100 text-green-800',
            ],
            'BBL' => [
                'initials'      => 'BBL',
                'name'          => 'Barangay Baluarte LGU',
                'type'          => 'Local Government',
                'contact'       => 'Barangay Mangrove Protection Committee',
                'email'         => 'brgy.baluarte@tagoloan.demo',
                'phone'         => '+63 88 567 1011',
                'barangay_code' => self::BALUARTE,
                'location'      => 'Barangay Hall, Baluarte, Tagoloan',
                'color'         => 'bg-teal-100 text-teal-800',
            ],
            'BSC' => [
                'initials'      => 'BSC',
                'name'          => 'Barangay Santa Cruz LGU',
                'type'          => 'Local Government',
                'contact'       => 'Barangay Council for Environmental Protection',
                'email'         => 'brgy.santa-cruz@tagoloan.demo',
                'phone'         => '+63 88 567 1019',
                'barangay_code' => self::SANTA_CRUZ,
                'location'      => 'Barangay Hall, Santa Cruz, Tagoloan',
                'color'         => 'bg-blue-100 text-blue-800',
            ],
        ];

        $agencyModels = [];
        foreach ($agenciesDefinitions as $key => $def) {
            $agency = Agency::updateOrCreate(
                ['email' => $def['email']],
                [
                    'initials'          => $def['initials'],
                    'name'              => $def['name'],
                    'type'              => $def['type'],
                    'contact'           => $def['contact'],
                    'phone'             => $def['phone'],
                    'region_code'       => self::REGION_CODE,
                    'province_code'     => self::PROVINCE_CODE,
                    'municipality_code' => $municipalityCode,
                    'barangay_code'     => $def['barangay_code'],
                    'location'          => $def['location'],
                    'custom_address'    => $def['location'] . ', Tagoloan, Misamis Oriental',
                    'color'             => $def['color'],
                    'status'            => 'Active',
                ]
            );
            $agencyModels[$key] = $agency;
        }

        // Shared lead coordinator user for seeded historical records
        $coordinator = null;
        if ($adminRole) {
            $coordinator = User::updateOrCreate(
                ['email' => 'coordinator.menro@tagoloan.demo'],
                [
                    'role_id'           => $adminRole->id,
                    'agency_id'         => $agencyModels['MENRO']->id,
                    'name'              => 'MENRO Greening Coordinator',
                    'password'          => 'MenroTagoloan2026!',
                    'status'            => UserStatus::Active,
                    'phone'             => '+63 917 567 8900',
                    'address'           => 'Poblacion, Tagoloan, Misamis Oriental',
                    'email_verified_at' => now(),
                ]
            );
        }

        // ─────────────────────────────────────────────────────────────────
        // 2. STRUCTURED DEFINITIONS OF ALL 43 CSV PLANTING & MONITORING RECORDS
        // ─────────────────────────────────────────────────────────────────

        $projects = [
            // -------------------------------------------------------------
            // 2021 HISTORICAL INITIATIVES (BRGY. SANTA ANA BAMBOO)
            // -------------------------------------------------------------
            [
                'request_no'     => '#REQ-2021-BSA-001',
                'agency_key'     => 'BSA',
                'requester_name' => 'Barangay Santa Ana LGU',
                'project_name'   => 'Santa Ana Riverbank Bamboo Buffer Planting Phase 1',
                'habitat'        => PlantingHabitat::Terrestrial->value,
                'request_date'   => '2021-03-08',
                'barangay_code'  => self::SANTA_ANA,
                'location'       => 'Santa Ana Riverbank, Tagoloan',
                'hub_key'        => 'santa-ana-center',
                'monitorings'    => [
                    [
                        'seedling_type'     => 'Bamboo',
                        'date_monitoring'   => '2021-03-23',
                        'seedlings_planted' => 150,
                        'replanted_count'   => 0,
                        'survived_count'    => 177, // Historical cumulative count from survey
                        'died_count'        => 19,
                    ],
                ],
                'tree_sample_count' => 12,
            ],
            [
                'request_no'     => '#REQ-2021-BSA-002',
                'agency_key'     => 'BSA',
                'requester_name' => 'Barangay Santa Ana LGU',
                'project_name'   => 'Santa Ana Watershed Bamboo Stabilization Phase 2',
                'habitat'        => PlantingHabitat::Terrestrial->value,
                'request_date'   => '2021-10-22',
                'barangay_code'  => self::SANTA_ANA,
                'location'       => 'Santa Ana Watershed, Tagoloan',
                'hub_key'        => 'calabat-upland',
                'monitorings'    => [
                    [
                        'seedling_type'     => 'Bamboo',
                        'date_monitoring'   => '2021-12-31',
                        'seedlings_planted' => 350,
                        'replanted_count'   => 200,
                        'survived_count'    => 321,
                        'died_count'        => 87,
                    ],
                ],
                'tree_sample_count' => 15,
            ],

            // -------------------------------------------------------------
            // 2022 HISTORICAL INITIATIVES (BRGY. ROSARIO MULTI-SPECIES)
            // -------------------------------------------------------------
            [
                'request_no'     => '#REQ-2022-BRO-001',
                'agency_key'     => 'BRO',
                'requester_name' => 'Barangay Rosario LGU',
                'project_name'   => 'Barangay Rosario Upland Multi-Species Greening Project',
                'habitat'        => PlantingHabitat::Terrestrial->value,
                'request_date'   => '2022-02-16',
                'barangay_code'  => self::ROSARIO,
                'location'       => 'Barangay Rosario Upland Sites, Tagoloan',
                'hub_key'        => 'rosario-forest',
                'monitorings'    => [
                    [
                        'seedling_type'     => 'Narra/Naga',
                        'date_monitoring'   => '2022-03-28',
                        'seedlings_planted' => 30,
                        'replanted_count'   => 0,
                        'survived_count'    => 30,
                        'died_count'        => 0,
                    ],
                    [
                        'seedling_type'     => 'Acacia',
                        'date_monitoring'   => '2022-03-28',
                        'seedlings_planted' => 21,
                        'replanted_count'   => 0,
                        'survived_count'    => 21,
                        'died_count'        => 0,
                    ],
                    [
                        'seedling_type'     => 'Mahogany',
                        'date_monitoring'   => '2022-03-28',
                        'seedlings_planted' => 87,
                        'replanted_count'   => 0,
                        'survived_count'    => 87,
                        'died_count'        => 0,
                    ],
                    [
                        'seedling_type'     => 'Fire Tree',
                        'date_monitoring'   => '2022-03-28',
                        'seedlings_planted' => 25,
                        'replanted_count'   => 0,
                        'survived_count'    => 20,
                        'died_count'        => 5,
                    ],
                    [
                        'seedling_type'     => 'TAMBIS/MAKOPA',
                        'date_monitoring'   => '2022-03-28',
                        'seedlings_planted' => 6,
                        'replanted_count'   => 0,
                        'survived_count'    => 6,
                        'died_count'        => 0,
                    ],
                ],
                'tree_sample_count' => 16,
            ],
            [
                'request_no'     => '#REQ-2022-BRO-002',
                'agency_key'     => 'BRO',
                'requester_name' => 'Barangay Rosario LGU',
                'project_name'   => 'Rosario Institutional & Community Sites Reforestation',
                'habitat'        => PlantingHabitat::Terrestrial->value,
                'request_date'   => '2022-03-10',
                'barangay_code'  => self::ROSARIO,
                'location'       => 'School Perimeter, Cemetery & Sibukahon, Rosario',
                'hub_key'        => 'rosario-forest',
                'monitorings'    => [
                    [
                        'seedling_type'     => 'Acacia',
                        'date_monitoring'   => '2022-04-16',
                        'seedlings_planted' => 12,
                        'replanted_count'   => 0,
                        'survived_count'    => 10,
                        'died_count'        => 2,
                    ],
                    [
                        'seedling_type'     => 'Narra/Naga',
                        'date_monitoring'   => '2022-04-16',
                        'seedlings_planted' => 4,
                        'replanted_count'   => 0,
                        'survived_count'    => 3,
                        'died_count'        => 1,
                    ],
                    [
                        'seedling_type'     => 'Mahogany',
                        'date_monitoring'   => '2022-04-16',
                        'seedlings_planted' => 31,
                        'replanted_count'   => 0,
                        'survived_count'    => 26,
                        'died_count'        => 5,
                    ],
                    [
                        'seedling_type'     => 'Fire Tree/Palcata',
                        'date_monitoring'   => '2022-04-16',
                        'seedlings_planted' => 21,
                        'replanted_count'   => 0,
                        'survived_count'    => 21,
                        'died_count'        => 0,
                    ],
                ],
                'tree_sample_count' => 12,
            ],
            [
                'request_no'     => '#REQ-2022-SMC-001',
                'agency_key'     => 'SAN MIGUEL',
                'requester_name' => 'San Miguel Corporation Tagoloan',
                'project_name'   => 'San Miguel Santa Ana Riparian Bamboo Initiative',
                'habitat'        => PlantingHabitat::Terrestrial->value,
                'request_date'   => '2022-03-25',
                'barangay_code'  => self::SANTA_ANA,
                'location'       => 'Santa Ana, Tagoloan',
                'hub_key'        => 'santa-ana-center',
                'monitorings'    => [
                    [
                        'seedling_type'     => 'Bamboo',
                        'date_monitoring'   => '2022-05-13',
                        'seedlings_planted' => 150,
                        'replanted_count'   => 0,
                        'survived_count'    => 140,
                        'died_count'        => 10,
                    ],
                ],
                'tree_sample_count' => 10,
            ],
            [
                'request_no'     => '#REQ-2022-BSA-003',
                'agency_key'     => 'BSA',
                'requester_name' => 'Barangay Santa Ana LGU',
                'project_name'   => 'Santa Ana Community-Wide Bamboo Slope Protection',
                'habitat'        => PlantingHabitat::Terrestrial->value,
                'request_date'   => '2022-03-25',
                'barangay_code'  => self::SANTA_ANA,
                'location'       => 'Santa Ana, Tagoloan',
                'hub_key'        => 'calabat-upland',
                'monitorings'    => [
                    [
                        'seedling_type'     => 'Bamboo',
                        'date_monitoring'   => '2022-05-13',
                        'seedlings_planted' => 650,
                        'replanted_count'   => 0,
                        'survived_count'    => 115,
                        'died_count'        => 236,
                    ],
                ],
                'tree_sample_count' => 15,
            ],

            // -------------------------------------------------------------
            // 2024 PLANTING + MONITORING BATCHES (14 RECORDS)
            // -------------------------------------------------------------
            [
                'request_no'     => '#REQ-2024-BSA-001',
                'agency_key'     => 'BSA',
                'requester_name' => 'Barangay Santa Ana LGU',
                'project_name'   => 'Sitio Kalabat Bamboo Greening 2024',
                'habitat'        => PlantingHabitat::Terrestrial->value,
                'request_date'   => '2024-02-16',
                'barangay_code'  => self::SANTA_ANA,
                'location'       => 'Sitio Kalabat, Santa Ana, Tagoloan',
                'hub_key'        => 'calabat-upland',
                'monitorings'    => [
                    [
                        'seedling_type'     => 'Bamboo',
                        'date_monitoring'   => '2024-12-15',
                        'seedlings_planted' => 100,
                        'replanted_count'   => 25,
                        'survived_count'    => 75,
                        'died_count'        => 25,
                    ],
                ],
                'tree_sample_count' => 10,
            ],
            [
                'request_no'     => '#REQ-2024-BFP-001',
                'agency_key'     => 'BFP',
                'requester_name' => 'Bureau of Fire Protection - Tagoloan',
                'project_name'   => 'BFP Nabulod Coastal Mangrove Rehabilitation',
                'habitat'        => PlantingHabitat::Mangrove->value,
                'request_date'   => '2024-03-21',
                'barangay_code'  => self::BALUARTE,
                'location'       => 'Nabulod, Baluarte, Tagoloan',
                'hub_key'        => 'baluarte-mangrove',
                'monitorings'    => [
                    [
                        'seedling_type'     => 'Mangrove',
                        'date_monitoring'   => '2024-10-17',
                        'seedlings_planted' => 100,
                        'replanted_count'   => 25,
                        'survived_count'    => 75,
                        'died_count'        => 25,
                    ],
                ],
                'tree_sample_count' => 10,
            ],
            [
                'request_no'     => '#REQ-2024-MENRO-001',
                'agency_key'     => 'MENRO',
                'requester_name' => 'LGU Tagoloan MENRO',
                'project_name'   => 'MENRO Baluarte Shoreline Mangrove Restoration',
                'habitat'        => PlantingHabitat::Mangrove->value,
                'request_date'   => '2024-03-22',
                'barangay_code'  => self::BALUARTE,
                'location'       => 'Baluarte Estuary, Tagoloan',
                'hub_key'        => 'baluarte-mangrove',
                'monitorings'    => [
                    [
                        'seedling_type'     => 'Mangrove',
                        'date_monitoring'   => '2024-12-15',
                        'seedlings_planted' => 300,
                        'replanted_count'   => 34,
                        'survived_count'    => 266,
                        'died_count'        => 34,
                    ],
                ],
                'tree_sample_count' => 15,
            ],
            [
                'request_no'     => '#REQ-2024-BBL-001',
                'agency_key'     => 'BBL',
                'requester_name' => 'Barangay Baluarte LGU',
                'project_name'   => 'Baluarte Intertidal Zone Mangrove Outplanting',
                'habitat'        => PlantingHabitat::Mangrove->value,
                'request_date'   => '2024-04-20',
                'barangay_code'  => self::BALUARTE,
                'location'       => 'Baluarte Coastline, Tagoloan',
                'hub_key'        => 'baluarte-mangrove',
                'monitorings'    => [
                    [
                        'seedling_type'     => 'Mangrove',
                        'date_monitoring'   => '2024-12-15',
                        'seedlings_planted' => 200,
                        'replanted_count'   => 50,
                        'survived_count'    => 150,
                        'died_count'        => 50,
                    ],
                ],
                'tree_sample_count' => 12,
            ],
            [
                'request_no'     => '#REQ-2024-PCG-001',
                'agency_key'     => 'COAST GUARD',
                'requester_name' => 'Philippine Coast Guard - Tagoloan Sub-Station',
                'project_name'   => 'Coast Guard Kalabat Bamboo & Fruit Tree Buffer',
                'habitat'        => PlantingHabitat::Terrestrial->value,
                'request_date'   => '2024-06-01',
                'barangay_code'  => self::SANTA_ANA,
                'location'       => 'Sitio Kalabat, Santa Ana, Tagoloan',
                'hub_key'        => 'calabat-upland',
                'monitorings'    => [
                    [
                        'seedling_type'     => 'Bamboo/Fruit Trees',
                        'date_monitoring'   => '2024-11-05',
                        'seedlings_planted' => 100,
                        'replanted_count'   => 30,
                        'survived_count'    => 70,
                        'died_count'        => 30,
                    ],
                ],
                'tree_sample_count' => 10,
            ],
            [
                'request_no'     => '#REQ-2024-FDC-001',
                'agency_key'     => 'FDC',
                'requester_name' => 'FDC Misamis Power Corporation',
                'project_name'   => 'FDC Kalabat Upland Bagras Watershed Greening',
                'habitat'        => PlantingHabitat::Terrestrial->value,
                'request_date'   => '2024-06-24',
                'barangay_code'  => self::SANTA_ANA,
                'location'       => 'Sitio Kalabat, Santa Ana, Tagoloan',
                'hub_key'        => 'calabat-upland',
                'monitorings'    => [
                    [
                        'seedling_type'     => 'Bagras',
                        'date_monitoring'   => '2024-11-05',
                        'seedlings_planted' => 100,
                        'replanted_count'   => 50,
                        'survived_count'    => 50,
                        'died_count'        => 50,
                    ],
                ],
                'tree_sample_count' => 10,
            ],
            [
                'request_no'     => '#REQ-2024-BBL-002',
                'agency_key'     => 'BBL',
                'requester_name' => 'Barangay Baluarte LGU',
                'project_name'   => 'Nabulod Community Mangrove Buffer Planting',
                'habitat'        => PlantingHabitat::Mangrove->value,
                'request_date'   => '2024-06-28',
                'barangay_code'  => self::BALUARTE,
                'location'       => 'Nabulod, Baluarte, Tagoloan',
                'hub_key'        => 'baluarte-mangrove',
                'monitorings'    => [
                    [
                        'seedling_type'     => 'Mangrove',
                        'date_monitoring'   => '2024-11-05',
                        'seedlings_planted' => 100,
                        'replanted_count'   => 17,
                        'survived_count'    => 83,
                        'died_count'        => 17,
                    ],
                ],
                'tree_sample_count' => 10,
            ],
            [
                'request_no'     => '#REQ-2024-MDRR-001',
                'agency_key'     => 'MDRR/MENRO',
                'requester_name' => 'Tagoloan MDRRMO & MENRO',
                'project_name'   => 'Kalabat Disaster Prevention Bamboo & Fruit Tree Buffer',
                'habitat'        => PlantingHabitat::Terrestrial->value,
                'request_date'   => '2024-07-18',
                'barangay_code'  => self::SANTA_ANA,
                'location'       => 'Sitio Kalabat, Santa Ana, Tagoloan',
                'hub_key'        => 'calabat-upland',
                'monitorings'    => [
                    [
                        'seedling_type'     => 'Bamboo/Fruit Trees',
                        'date_monitoring'   => '2024-10-17',
                        'seedlings_planted' => 150,
                        'replanted_count'   => 50,
                        'survived_count'    => 100,
                        'died_count'        => 50,
                    ],
                ],
                'tree_sample_count' => 12,
            ],
            [
                'request_no'     => '#REQ-2024-TSHS-001',
                'agency_key'     => 'SENIOR HIGH',
                'requester_name' => 'Tagoloan Senior High School',
                'project_name'   => 'TSHS Youth Eco-Camp Baluarte Mangrove Planting',
                'habitat'        => PlantingHabitat::Mangrove->value,
                'request_date'   => '2024-09-28',
                'barangay_code'  => self::BALUARTE,
                'location'       => 'Baluarte Coastal Trail, Tagoloan',
                'hub_key'        => 'baluarte-mangrove',
                'monitorings'    => [
                    [
                        'seedling_type'     => 'Mangrove',
                        'date_monitoring'   => '2024-11-05',
                        'seedlings_planted' => 200,
                        'replanted_count'   => 23,
                        'survived_count'    => 177,
                        'died_count'        => 23,
                    ],
                ],
                'tree_sample_count' => 12,
            ],
            [
                'request_no'     => '#REQ-2024-LGU-001',
                'agency_key'     => 'LGU',
                'requester_name' => 'Municipality of Tagoloan LGU',
                'project_name'   => 'Tagoloan Municipal Major Bamboo Reforestation',
                'habitat'        => PlantingHabitat::Terrestrial->value,
                'request_date'   => '2024-10-18',
                'barangay_code'  => self::SANTA_ANA,
                'location'       => 'Sitio Kalabat, Santa Ana, Tagoloan',
                'hub_key'        => 'calabat-upland',
                'monitorings'    => [
                    [
                        'seedling_type'     => 'Bamboo',
                        'date_monitoring'   => '2024-10-17',
                        'seedlings_planted' => 800,
                        'replanted_count'   => 147,
                        'survived_count'    => 653,
                        'died_count'        => 147,
                    ],
                ],
                'tree_sample_count' => 20,
            ],
            [
                'request_no'     => '#REQ-2024-BJMP-001',
                'agency_key'     => 'BJMP',
                'requester_name' => 'BJMP - Tagoloan Municipal Jail',
                'project_name'   => 'BJMP Green Shield Baluarte Mangrove Activity',
                'habitat'        => PlantingHabitat::Mangrove->value,
                'request_date'   => '2024-10-18',
                'barangay_code'  => self::BALUARTE,
                'location'       => 'Baluarte Estuary, Tagoloan',
                'hub_key'        => 'baluarte-mangrove',
                'monitorings'    => [
                    [
                        'seedling_type'     => 'Mangrove',
                        'date_monitoring'   => '2024-12-15',
                        'seedlings_planted' => 50,
                        'replanted_count'   => 13,
                        'survived_count'    => 37,
                        'died_count'        => 13,
                    ],
                ],
                'tree_sample_count' => 10,
            ],
            [
                'request_no'     => '#REQ-2024-TCC-001',
                'agency_key'     => 'TCC',
                'requester_name' => 'Tagoloan Community College (TCC)',
                'project_name'   => 'TCC Academic Greening Baluarte Mangrove Reforestation',
                'habitat'        => PlantingHabitat::Mangrove->value,
                'request_date'   => '2024-11-05',
                'barangay_code'  => self::BALUARTE,
                'location'       => 'Baluarte Coastal Estuary, Tagoloan',
                'hub_key'        => 'baluarte-mangrove',
                'monitorings'    => [
                    [
                        'seedling_type'     => 'Mangrove',
                        'date_monitoring'   => '2024-12-15',
                        'seedlings_planted' => 500,
                        'replanted_count'   => 127,
                        'survived_count'    => 373,
                        'died_count'        => 127,
                    ],
                ],
                'tree_sample_count' => 15,
            ],
            [
                'request_no'     => '#REQ-2024-VIPEL-001',
                'agency_key'     => 'VIPEL',
                'requester_name' => 'VIPEL Corporation',
                'project_name'   => 'VIPEL Eco-Partnership Mangrove Belt Planting',
                'habitat'        => PlantingHabitat::Mangrove->value,
                'request_date'   => '2024-11-21',
                'barangay_code'  => self::BALUARTE,
                'location'       => 'Baluarte Coast, Tagoloan',
                'hub_key'        => 'baluarte-mangrove',
                'monitorings'    => [
                    [
                        'seedling_type'     => 'Mangrove',
                        'date_monitoring'   => '2024-10-17',
                        'seedlings_planted' => 100,
                        'replanted_count'   => 5,
                        'survived_count'    => 70,
                        'died_count'        => 30,
                    ],
                ],
                'tree_sample_count' => 10,
            ],
            [
                'request_no'     => '#REQ-2024-BOSTIK-001',
                'agency_key'     => 'BOSTIK',
                'requester_name' => 'Bostik Philippines Inc. Tagoloan',
                'project_name'   => 'Bostik Community Agroforestry Fruit Tree Planting',
                'habitat'        => PlantingHabitat::Terrestrial->value,
                'request_date'   => '2024-12-19',
                'barangay_code'  => self::ROSARIO,
                'location'       => 'Rosario Agroforestry Site, Tagoloan',
                'hub_key'        => 'rosario-forest',
                'monitorings'    => [
                    [
                        'seedling_type'     => 'Fruit Trees',
                        'date_monitoring'   => '2024-12-15',
                        'seedlings_planted' => 100,
                        'replanted_count'   => 20,
                        'survived_count'    => 80,
                        'died_count'        => 20,
                    ],
                ],
                'tree_sample_count' => 10,
            ],

            // -------------------------------------------------------------
            // 2025 PLANTING + MONITORING BATCHES (4 RECORDS)
            // -------------------------------------------------------------
            [
                'request_no'     => '#REQ-2025-BFP-001',
                'agency_key'     => 'BFP',
                'requester_name' => 'Bureau of Fire Protection - Tagoloan',
                'project_name'   => 'BFP Nabulod Mangrove Fortification 2025',
                'habitat'        => PlantingHabitat::Mangrove->value,
                'request_date'   => '2025-01-15',
                'barangay_code'  => self::BALUARTE,
                'location'       => 'Nabulod, Baluarte, Tagoloan',
                'hub_key'        => 'baluarte-mangrove',
                'monitorings'    => [
                    [
                        'seedling_type'     => 'Mangrove',
                        'date_monitoring'   => '2025-04-20',
                        'seedlings_planted' => 50,
                        'replanted_count'   => 12,
                        'survived_count'    => 38,
                        'died_count'        => 12,
                    ],
                ],
                'tree_sample_count' => 10,
            ],
            [
                'request_no'     => '#REQ-2025-BJMP-001',
                'agency_key'     => 'BJMP',
                'requester_name' => 'BJMP - Tagoloan Municipal Jail',
                'project_name'   => 'BJMP Nabulod Wetland Mangrove Conservation',
                'habitat'        => PlantingHabitat::Mangrove->value,
                'request_date'   => '2025-02-05',
                'barangay_code'  => self::BALUARTE,
                'location'       => 'Nabulod, Baluarte, Tagoloan',
                'hub_key'        => 'baluarte-mangrove',
                'monitorings'    => [
                    [
                        'seedling_type'     => 'Mangrove',
                        'date_monitoring'   => '2025-04-04',
                        'seedlings_planted' => 20,
                        'replanted_count'   => 8,
                        'survived_count'    => 12,
                        'died_count'        => 8,
                    ],
                ],
                'tree_sample_count' => 8,
            ],
            [
                'request_no'     => '#REQ-2025-ARCHEM-001',
                'agency_key'     => 'ARCHEM',
                'requester_name' => 'Asian Chemical Corporation (ARCHEM)',
                'project_name'   => 'ARCHEM Santa Ana Slope Bamboo Stabilization',
                'habitat'        => PlantingHabitat::Terrestrial->value,
                'request_date'   => '2025-05-16',
                'barangay_code'  => self::SANTA_ANA,
                'location'       => 'Sta. Ana, Tagoloan',
                'hub_key'        => 'santa-ana-center',
                'monitorings'    => [
                    [
                        'seedling_type'     => 'Bamboo',
                        'date_monitoring'   => '2025-05-16',
                        'seedlings_planted' => 100,
                        'replanted_count'   => 20,
                        'survived_count'    => 80,
                        'died_count'        => 20,
                    ],
                ],
                'tree_sample_count' => 10,
            ],
            [
                'request_no'     => '#REQ-2025-FDC-001',
                'agency_key'     => 'FDC',
                'requester_name' => 'FDC Misamis Power Corporation',
                'project_name'   => 'FDC Sta. Cruz Green Corridor Bamboo Planting',
                'habitat'        => PlantingHabitat::Terrestrial->value,
                'request_date'   => '2025-06-05',
                'barangay_code'  => self::SANTA_CRUZ,
                'location'       => 'Sta. Cruz, Tagoloan',
                'hub_key'        => 'santa-cruz',
                'monitorings'    => [
                    [
                        'seedling_type'     => 'Bamboo',
                        'date_monitoring'   => '2025-06-30',
                        'seedlings_planted' => 200,
                        'replanted_count'   => 5,
                        'survived_count'    => 195,
                        'died_count'        => 5,
                    ],
                ],
                'tree_sample_count' => 12,
            ],

            // -------------------------------------------------------------
            // 2026 ACTIVE INITIATIVE (TCC CALABAT PINE)
            // -------------------------------------------------------------
            [
                'request_no'     => '#REQ-TCC-2026-001',
                'agency_key'     => 'TCC',
                'requester_name' => 'Tagoloan Community College (TCC)',
                'project_name'   => 'TCC Calabat Upland Pine Greening Project',
                'habitat'        => PlantingHabitat::Terrestrial->value,
                'request_date'   => '2026-03-23',
                'barangay_code'  => self::SANTA_ANA,
                'location'       => 'Sitio Calabat, Santa Ana, Tagoloan, Misamis Oriental',
                'hub_key'        => 'calabat-upland',
                'monitorings'    => [
                    [
                        'seedling_type'     => 'Pine',
                        'date_monitoring'   => null,
                        'seedlings_planted' => 60,
                        'replanted_count'   => 0,
                        'survived_count'    => 60,
                        'died_count'        => 0,
                    ],
                ],
                'tree_sample_count' => 60, // Full mapping of all 60 seedlings
            ],
        ];

        // ─────────────────────────────────────────────────────────────────
        // 3. SEED REQUESTS, MONITORINGS, MAPPED TREES & PHOTOS
        // ─────────────────────────────────────────────────────────────────

        $totalPlantedAll = 0;
        $globalTreeSequence = 0;

        foreach ($projects as $projIndex => $p) {
            $agency = $agencyModels[$p['agency_key']];
            $hub = $hubs[$p['hub_key']];
            $totalTarget = array_sum(array_column($p['monitorings'], 'seedlings_planted'));
            $totalPlantedAll += $totalTarget;
            $speciesNames = array_column($p['monitorings'], 'seedling_type');
            $year = Carbon::parse($p['request_date'])->format('Y');

            // 3.1 Planting Request
            $request = PlantingRequest::updateOrCreate(
                ['request_no' => $p['request_no']],
                [
                    'parent_id'      => null,
                    'user_id'        => $coordinator?->id,
                    'agency_id'      => $agency->id,
                    'requester_name' => $p['requester_name'],
                    'project_name'   => $p['project_name'],
                    'habitat'        => $p['habitat'],
                    'target_trees'   => $totalTarget,
                    'barangay_code'  => $p['barangay_code'],
                    'location'       => $p['location'],
                    'custom_address' => $p['location'] . ', Tagoloan, Misamis Oriental',
                    'seedling_draft' => [
                        'species' => $speciesNames,
                        'raw'     => implode(', ', $speciesNames),
                        'source'  => 'historical_menro_sheet',
                    ],
                    'status'         => ($year >= '2025') ? 'Approved' : 'Completed',
                    'request_date'   => $p['request_date'],
                ]
            );

            // 3.2 Planting Monitoring Records (exact counts from clean CSV)
            foreach ($p['monitorings'] as $m) {
                PlantingMonitoring::updateOrCreate(
                    [
                        'request_id'    => $request->id,
                        'seedling_type' => $m['seedling_type'],
                    ],
                    [
                        'date_monitoring'   => $m['date_monitoring'],
                        'seedlings_planted' => (int) $m['seedlings_planted'],
                        'replanted_count'   => (int) $m['replanted_count'],
                        'survived_count'    => (int) $m['survived_count'],
                        'died_count'        => (int) $m['died_count'],
                    ]
                );
            }

            // 3.3 Mapped Tree Coordinates in Tagoloan
            $sampleTrees = $p['tree_sample_count'];
            $plantDate = Carbon::parse($p['request_date']);
            $barangayName = TagoloanLocation::barangayName($p['barangay_code']);

            for ($t = 1; $t <= $sampleTrees; $t++) {
                $globalTreeSequence++;
                $treeIndex = $t;
                $mIndex = ($t - 1) % count($p['monitorings']);
                $specRaw = $p['monitorings'][$mIndex]['seedling_type'];
                $specInfo = $this->speciesInfo($specRaw);

                $clientUuid = sprintf('seed-hist-%s-%05d', strtolower($agency->initials), $globalTreeSequence);
                $treeCode   = sprintf('TGL-%s-%s-%05d', $year, $agency->initials, $globalTreeSequence);

                // Compute spiral distribution around designated hub
                $angle = ($t / max(1, $sampleTrees)) * 2 * M_PI * 2.5;
                $radiusDeg = 0.00015 + (($t / max(1, $sampleTrees)) * 0.00085);
                $lngScale = cos(deg2rad($hub['lat']));
                $lat = round($hub['lat'] + ($radiusDeg * cos($angle)), 7);
                $lng = round($hub['lng'] + (($radiusDeg * sin($angle)) / max(0.2, $lngScale)), 7);

                // Tree survival status matching monitoring ratio
                $diedCount = $p['monitorings'][$mIndex]['died_count'];
                $plantedCount = max(1, $p['monitorings'][$mIndex]['seedlings_planted']);
                $deadRatio = $diedCount / $plantedCount;
                $status = ($t <= ceil($sampleTrees * $deadRatio))
                    ? TreeStatus::Dead->value
                    : TreeStatus::Alive->value;

                $recordedDate = $m['date_monitoring'] 
                    ? Carbon::parse($m['date_monitoring'])->toDateString() 
                    : $plantDate->copy()->addDays(min(14, $t))->toDateString();

                $tree = Tree::updateOrCreate(
                    ['client_uuid' => $clientUuid],
                    [
                        'request_id'     => $request->id,
                        'tree_code'      => $treeCode,
                        'agency_id'      => $agency->id,
                        'recorded_by_id' => $coordinator?->id,
                        'species'        => $specInfo['scientific'],
                        'common_name'    => $specInfo['common'],
                        'status'         => $status,
                        'date_planted'   => $plantDate->toDateString(),
                        'date_recorded'  => $recordedDate,
                        'barangay'       => $barangayName,
                        'municipality'   => 'Tagoloan',
                        'province'       => 'Misamis Oriental',
                        'latitude'       => $lat,
                        'longitude'      => $lng,
                        'landmark'       => sprintf('%s site marker #%d', $p['location'], $t),
                        'notes'          => sprintf('Seeded record from Tagoloan MENRO %s project.', $year),
                    ]
                );

                // Link reference photo to the first 2 trees of each project
                if ($t <= 2) {
                    TreePhoto::firstOrCreate(
                        [
                            'tree_id' => $tree->id,
                            'path'    => $specInfo['photo'],
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
}
