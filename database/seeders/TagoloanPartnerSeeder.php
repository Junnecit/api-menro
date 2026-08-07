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
use App\Models\User;
use App\Services\PlantingRequestTemplateService;
use App\Support\PrivateStorage;
use App\Support\TagoloanLocation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TagoloanPartnerSeeder extends Seeder
{
    private const REGION_CODE = '100000000';

    private const PROVINCE_CODE = '104300000';

    private const TREES_PER_REQUEST = 50;

    /**
     * Deterministic unique demo password per email (must match scripts/generate_demo_accounts_pdf.py).
     */
    private function demoPassword(string $email): string
    {
        return 'Menro'.substr(hash('sha256', 'menro-tagoloan-demo|'.strtolower($email)), 0, 8);
    }

    /**
     * Inland planting hubs from PhilAtlas barangay centers, shifted east of the
     * Macajalar Bay shoreline so every point sits on land (not in the water).
     * Spaced ~1 km+ apart for ~10 distinct MarkerCluster badges.
     *
     * @return array<string, array{lat: float, lng: float}>
     */
    private function inlandHubs(): array
    {
        return [
            // Poblacion inland (PhilAtlas 8.5385, 124.7540 — push east off the shore)
            'menro' => ['lat' => 8.5365, 'lng' => 124.7780],
            // Near Natumolan / Gracia inland
            'poblacion' => ['lat' => 8.5300, 'lng' => 124.7820],
            // Santa Ana inland (east)
            'santa-ana' => ['lat' => 8.5220, 'lng' => 124.7920],
            // Casinglot inland (east of coast)
            'casinglot' => ['lat' => 8.5440, 'lng' => 124.7800],
            // Was coastal/north — move into Santa Cruz inland
            'green-alliance' => ['lat' => 8.5460, 'lng' => 124.7780],
            // Santa Cruz east
            'coastal-watch' => ['lat' => 8.5420, 'lng' => 124.7880],
            // Rosario (elev. ~285 m) — deep inland
            'ecotree' => ['lat' => 8.5368, 'lng' => 124.8305],
            // Natumolan inland
            'agro' => ['lat' => 8.5320, 'lng' => 124.8000],
            // Mohon inland
            'nursery' => ['lat' => 8.5440, 'lng' => 124.8020],
            // Sugbongcogon inland
            'rivera' => ['lat' => 8.5240, 'lng' => 124.7860],
        ];
    }

    /**
     * @return list<array{
     *     slug: string,
     *     initials: string,
     *     name: string,
     *     type: string,
     *     contact: string,
     *     admin_name: string,
     *     color: string,
     *     soil_type: string,
     *     barangay_code: string
     * }>
     */
    private function partners(): array
    {
        return [
            [
                'slug' => 'menro',
                'initials' => 'TM',
                'name' => 'LGU Tagoloan MENRO',
                'type' => 'Local Government',
                'contact' => 'Rosa Villanueva',
                'admin_name' => 'Rosa Villanueva',
                'color' => 'bg-blue-100 text-blue-800',
                'soil_type' => 'Alluvial, fertile',
                'barangay_code' => '104324006',
            ],
            [
                'slug' => 'poblacion',
                'initials' => 'BP',
                'name' => 'Barangay Poblacion LGU',
                'type' => 'Local Government',
                'contact' => 'Antonio Mercado',
                'admin_name' => 'Antonio Mercado',
                'color' => 'bg-blue-100 text-blue-800',
                'soil_type' => 'Clay loam, well-drained',
                'barangay_code' => '104324006',
            ],
            [
                'slug' => 'santa-ana',
                'initials' => 'SA',
                'name' => 'Barangay Santa Ana LGU',
                'type' => 'Local Government',
                'contact' => 'Elena Cabahug',
                'admin_name' => 'Elena Cabahug',
                'color' => 'bg-blue-100 text-blue-800',
                'soil_type' => 'Sandy loam, coastal',
                'barangay_code' => '104324008',
            ],
            [
                'slug' => 'casinglot',
                'initials' => 'BC',
                'name' => 'Barangay Casinglot LGU',
                'type' => 'Local Government',
                'contact' => 'Mario Ablan',
                'admin_name' => 'Mario Ablan',
                'color' => 'bg-blue-100 text-blue-800',
                'soil_type' => 'Silty clay, high moisture',
                'barangay_code' => '104324002',
            ],
            [
                'slug' => 'green-alliance',
                'initials' => 'GA',
                'name' => 'Tagoloan Green Alliance',
                'type' => 'NGO',
                'contact' => 'Ben Morales',
                'admin_name' => 'Ben Morales',
                'color' => 'bg-green-100 text-green-800',
                'soil_type' => 'Loamy, high organic matter',
                'barangay_code' => '104324001',
            ],
            [
                'slug' => 'coastal-watch',
                'initials' => 'CW',
                'name' => 'MisOr Coastal Watch',
                'type' => 'NGO',
                'contact' => 'Dani Soriano',
                'admin_name' => 'Dani Soriano',
                'color' => 'bg-teal-100 text-teal-800',
                'soil_type' => 'Loamy, well-drained inland',
                'barangay_code' => '104324009',
            ],
            [
                'slug' => 'ecotree',
                'initials' => 'ET',
                'name' => 'EcoTree Tagoloan Chapter',
                'type' => 'NGO',
                'contact' => 'Ligaya Bautista',
                'admin_name' => 'Ligaya Bautista',
                'color' => 'bg-teal-100 text-teal-800',
                'soil_type' => 'Volcanic loam, fertile',
                'barangay_code' => '104324007',
            ],
            [
                'slug' => 'agro',
                'initials' => 'AV',
                'name' => 'Tagoloan Agro Ventures',
                'type' => 'Private Individual',
                'contact' => 'Carlo Reyes',
                'admin_name' => 'Carlo Reyes',
                'color' => 'bg-purple-100 text-purple-800',
                'soil_type' => 'Red clay, well-drained',
                'barangay_code' => '104324005',
            ],
            [
                'slug' => 'nursery',
                'initials' => 'NM',
                'name' => 'Northern Mindanao Nursery',
                'type' => 'Private Individual',
                'contact' => 'Ana Fernandez',
                'admin_name' => 'Ana Fernandez',
                'color' => 'bg-orange-100 text-orange-800',
                'soil_type' => 'Sandy, low moisture',
                'barangay_code' => '104324004',
            ],
            [
                'slug' => 'rivera',
                'initials' => 'RF',
                'name' => 'Rivera Family Farm',
                'type' => 'Private Individual',
                'contact' => 'Jose Rivera',
                'admin_name' => 'Jose Rivera',
                'color' => 'bg-pink-100 text-pink-800',
                'soil_type' => 'Loamy, slightly acidic',
                'barangay_code' => '104324003',
            ],
        ];
    }

    /**
     * @return list<array{species: string, common_name: string}>
     */
    private function speciesCatalog(): array
    {
        return [
            ['species' => 'Pterocarpus indicus', 'common_name' => 'Narra'],
            ['species' => 'Swietenia macrophylla', 'common_name' => 'Mahogany'],
            ['species' => 'Vitex parviflora', 'common_name' => 'Molave'],
            ['species' => 'Casuarina equisetifolia', 'common_name' => 'Agoho'],
            ['species' => 'Terminalia catappa', 'common_name' => 'Talisay'],
            ['species' => 'Samanea saman', 'common_name' => 'Acacia'],
            ['species' => 'Gmelina arborea', 'common_name' => 'Gmelina'],
            ['species' => 'Artocarpus heterophyllus', 'common_name' => 'Jackfruit'],
        ];
    }

    /**
     * Tight inland spiral around an agency hub (≈15–95 m). Unique lat/lng per tree.
     *
     * @return array{lat: float, lng: float}
     */
    private function spiralPoint(float $hubLat, float $hubLng, int $index, int $total, float $phase = 0.0): array
    {
        $t = max(1, $index);
        $turns = 3.0;
        $angle = $phase + (($t / $total) * 2 * M_PI * $turns);
        $radiusDeg = 0.00015 + (($t / $total) * 0.0007);
        $lngScale = cos(deg2rad($hubLat));

        return [
            'lat' => round($hubLat + ($radiusDeg * cos($angle)), 7),
            'lng' => round($hubLng + (($radiusDeg * sin($angle)) / max(0.2, $lngScale)), 7),
        ];
    }

    public function run(): void
    {
        $adminRole = Role::where('slug', 'admin')->firstOrFail();
        $planterRole = Role::where('slug', 'user')->firstOrFail();
        $municipalityCode = config('tagoloan.municipality_code');
        $speciesCatalog = $this->speciesCatalog();
        $hubs = $this->inlandHubs();
        $year = now()->format('Y');

        foreach ($this->partners() as $index => $partner) {
            $agencyIndex = $index + 1;
            $agencyEmail = "agency.{$partner['slug']}@tagoloan.demo";
            $adminEmail = "admin.{$partner['slug']}@tagoloan.demo";
            $hub = $hubs[$partner['slug']];

            $agency = Agency::updateOrCreate(
                ['email' => $agencyEmail],
                [
                    'initials' => $partner['initials'],
                    'name' => $partner['name'],
                    'type' => $partner['type'],
                    'contact' => $partner['contact'],
                    'phone' => sprintf('+63 88 700 %04d', 1000 + $agencyIndex),
                    'region_code' => self::REGION_CODE,
                    'province_code' => self::PROVINCE_CODE,
                    'municipality_code' => $municipalityCode,
                    'barangay_code' => $partner['barangay_code'],
                    'location' => TagoloanLocation::formatLocation($partner['barangay_code']),
                    'soil_type' => $partner['soil_type'],
                    'color' => $partner['color'],
                    'status' => 'Active',
                ]
            );

            $admin = User::updateOrCreate(
                ['email' => $adminEmail],
                [
                    'role_id' => $adminRole->id,
                    'agency_id' => $agency->id,
                    'admin_id' => null,
                    'name' => $partner['admin_name'],
                    'password' => $this->demoPassword($adminEmail),
                    'status' => UserStatus::Active,
                    'phone' => sprintf('+63 917 %07d', 1000000 + $agencyIndex),
                    'address' => TagoloanLocation::formatLocation($partner['barangay_code']),
                    'email_verified_at' => now(),
                ]
            );

            $planters = [];
            for ($p = 1; $p <= 10; $p++) {
                $planterEmail = "planter{$p}.{$partner['slug']}@tagoloan.demo";
                $planters[] = User::updateOrCreate(
                    ['email' => $planterEmail],
                    [
                        'role_id' => $planterRole->id,
                        'agency_id' => null,
                        'admin_id' => $admin->id,
                        'name' => sprintf('%s Planter %d', $partner['initials'], $p),
                        'password' => $this->demoPassword($planterEmail),
                        'status' => UserStatus::Active,
                        'phone' => sprintf('+63 918 %07d', ($agencyIndex * 10) + $p),
                        'address' => TagoloanLocation::formatLocation($partner['barangay_code']),
                        'email_verified_at' => now(),
                    ]
                );
            }

            $requestDefs = [
                [
                    'suffix' => 'A',
                    'project' => "{$partner['name']} Reforestation Phase A",
                    'habitat' => PlantingHabitat::Terrestrial,
                    'status' => 'Approved',
                    'barangay_code' => $partner['barangay_code'],
                    'phase' => 0.0,
                ],
                [
                    'suffix' => 'B',
                    'project' => "{$partner['name']} Upland Phase B",
                    'habitat' => PlantingHabitat::Terrestrial,
                    'status' => 'In Progress',
                    'barangay_code' => $partner['barangay_code'],
                    'phase' => M_PI,
                ],
            ];

            $parentRequestDate = Carbon::now()->subDays(30 - $agencyIndex)->toDateString();
            $createdBySuffix = [];

            foreach ($requestDefs as $reqDef) {
                $requestNo = sprintf('#T%02d-%s', $agencyIndex, $reqDef['suffix']);
                $barangayCode = $reqDef['barangay_code'];
                $barangayName = TagoloanLocation::barangayName($barangayCode) ?? 'Poblacion';

                $seedlingType = 'Narra, Mahogany, Molave, Agoho';
                $documentMeta = $this->storeFilledRequestDocument(
                    requestNo: $requestNo,
                    agencyIndex: $agencyIndex,
                    suffix: $reqDef['suffix'],
                    projectName: $reqDef['project'],
                    barangayName: $barangayName,
                    seedlingType: $seedlingType,
                    requesterName: $partner['admin_name'],
                );

                $plantingRequest = PlantingRequest::updateOrCreate(
                    ['request_no' => $requestNo],
                    [
                        'parent_id' => null,
                        'user_id' => $admin->id,
                        'agency_id' => $agency->id,
                        'requester_name' => $partner['admin_name'],
                        'project_name' => $reqDef['project'],
                        'habitat' => $reqDef['habitat'],
                        'target_trees' => self::TREES_PER_REQUEST,
                        'barangay_code' => $barangayCode,
                        'location' => TagoloanLocation::formatLocation($barangayCode),
                        'document_path' => $documentMeta['path'],
                        'document_name' => $documentMeta['name'],
                        'document_mime' => $documentMeta['mime'],
                        'document_hash' => $documentMeta['hash'],
                        'seedling_draft' => [
                            'species' => array_column(array_slice($speciesCatalog, 0, 4), 'common_name'),
                            'raw' => $seedlingType,
                            'source' => 'document',
                        ],
                        'status' => $reqDef['status'],
                        'request_date' => $parentRequestDate,
                    ]
                );

                $createdBySuffix[$reqDef['suffix']] = $plantingRequest;

                for ($t = 1; $t <= self::TREES_PER_REQUEST; $t++) {
                    $clientUuid = sprintf('seed-t%02d-%s-%02d', $agencyIndex, strtolower($reqDef['suffix']), $t);
                    $species = $speciesCatalog[($t - 1) % count($speciesCatalog)];
                    $planter = $planters[($t - 1) % count($planters)];
                    $point = $this->spiralPoint(
                        $hub['lat'],
                        $hub['lng'],
                        $t,
                        self::TREES_PER_REQUEST,
                        $reqDef['phase']
                    );

                    // ~90 alive, 5 dead, 5 need_replacement per agency (spread across both requests)
                    $status = match (true) {
                        $reqDef['suffix'] === 'A' && $t <= 3 => TreeStatus::Dead,
                        $reqDef['suffix'] === 'B' && $t <= 2 => TreeStatus::Dead,
                        $reqDef['suffix'] === 'A' && $t <= 5 => TreeStatus::NeedReplacement,
                        $reqDef['suffix'] === 'B' && $t <= 5 => TreeStatus::NeedReplacement,
                        default => TreeStatus::Alive,
                    };

                    $plantedOn = Carbon::now()->subDays(60 - (($agencyIndex + $t) % 50));

                    $tree = Tree::updateOrCreate(
                        ['client_uuid' => $clientUuid],
                        [
                            'request_id' => $plantingRequest->id,
                            'agency_id' => $agency->id,
                            'recorded_by_id' => $planter->id,
                            'species' => $species['species'],
                            'common_name' => $species['common_name'],
                            'status' => $status,
                            'date_planted' => $plantedOn->toDateString(),
                            'date_recorded' => $plantedOn->copy()->addDay()->toDateString(),
                            'barangay' => $barangayName,
                            'municipality' => 'Tagoloan',
                            'province' => 'Misamis Oriental',
                            'latitude' => $point['lat'],
                            'longitude' => $point['lng'],
                            'landmark' => sprintf('%s inland site marker %d', $barangayName, $t),
                            'notes' => 'Seeded demo tree for Tagoloan partner data (land only).',
                        ]
                    );

                    if (! $tree->tree_code) {
                        $tree->update([
                            'tree_code' => sprintf('TGL-%s-%05d', $year, $tree->id),
                        ]);
                    }
                }

                // Report Center KPIs come from planting_monitorings (not tree rows).
                $alive = Tree::query()
                    ->where('request_id', $plantingRequest->id)
                    ->where('status', TreeStatus::Alive)
                    ->count();
                $dead = Tree::query()
                    ->where('request_id', $plantingRequest->id)
                    ->where('status', TreeStatus::Dead)
                    ->count();
                $needReplacement = Tree::query()
                    ->where('request_id', $plantingRequest->id)
                    ->where('status', TreeStatus::NeedReplacement)
                    ->count();

                PlantingMonitoring::updateOrCreate(
                    ['request_id' => $plantingRequest->id],
                    [
                        'seedling_type' => $seedlingType,
                        'date_monitoring' => Carbon::now()->subDays(7 + $agencyIndex)->toDateString(),
                        'seedlings_planted' => self::TREES_PER_REQUEST,
                        'replanted_count' => $needReplacement,
                        'survived_count' => $alive,
                        'died_count' => $dead,
                    ]
                );
            }

            // With 2 requests: In Progress (B) is the list anchor (keeps its document + seed);
            // Approved (A) is the only sub-request.
            if (isset($createdBySuffix['A'], $createdBySuffix['B'])) {
                $createdBySuffix['A']->update(['parent_id' => $createdBySuffix['B']->id]);
                $createdBySuffix['B']->update(['parent_id' => null]);
            }

            // Drop legacy empty shell parents from earlier seed runs.
            PlantingRequest::query()
                ->where('request_no', sprintf('#T%02d-P', $agencyIndex))
                ->where('agency_id', $agency->id)
                ->each(function (PlantingRequest $shell) {
                    PlantingRequest::query()->where('parent_id', $shell->id)->update(['parent_id' => null]);
                    $shell->forceDelete();
                });
        }

        $this->command?->info('Tagoloan partners seeded: 10 inland agency clusters, 10 admins, 100 planters, 20 requests (In Progress anchors + Approved sub-requests), 1000 trees, 20 planting monitorings (Report Center).');
    }

    /**
     * Store a filled official MENRO planting-request DOCX for a seeded request.
     *
     * @return array{path: string, name: string, mime: string, hash: string}
     */
    private function storeFilledRequestDocument(
        string $requestNo,
        int $agencyIndex,
        string $suffix,
        string $projectName,
        string $barangayName,
        string $seedlingType,
        string $requesterName,
    ): array {
        $templateService = app(PlantingRequestTemplateService::class);

        $binary = $templateService->buildFilledDocxBinary([
            'project_name' => $projectName,
            'target_trees' => self::TREES_PER_REQUEST,
            'seedling_type' => $seedlingType,
            'barangay' => $barangayName,
            'notes' => sprintf(
                'Submitted by %s for %s. Request %s — Tagoloan community planting activity.',
                $requesterName,
                $projectName,
                $requestNo,
            ),
        ]);

        $storagePath = sprintf(
            'planting-request-docs/seed-t%02d-%s.docx',
            $agencyIndex,
            strtolower($suffix)
        );
        $documentName = sprintf(
            'MENRO-Planting-Request-T%02d-%s.docx',
            $agencyIndex,
            $suffix
        );

        PrivateStorage::delete($storagePath);
        PrivateStorage::put($storagePath, $binary);

        return [
            'path' => $storagePath,
            'name' => $documentName,
            'mime' => $templateService->mimeType(),
            'hash' => hash('sha256', $binary),
        ];
    }
}
