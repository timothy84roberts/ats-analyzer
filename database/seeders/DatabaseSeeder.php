<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PipelineStageSeeder::class,
            CountrySeeder::class,
            PlatformSeeder::class,
        ]);

        User::query()->updateOrCreate(
            ['email' => 'vector@mail.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('rhfjreh123!'),
                'is_admin' => true,
                'is_ats_lab_allowed' => true,
            ]
        );
    }
}
