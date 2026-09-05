<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->error('Refusing to seed demo users in production.');

            return;
        }

        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            HistoricalPlantingSeeder::class,
        ]);
    }
}
