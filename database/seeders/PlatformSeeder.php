<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlatformSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'LinkedIn', 'slug' => 'linkedin', 'is_active' => true, 'sort_order' => 10],
        ];

        foreach ($rows as $row) {
            DB::table('platforms')->updateOrInsert(
                ['slug' => $row['slug']],
                array_merge($row, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
