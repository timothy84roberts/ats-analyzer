<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
        ];

        foreach ($rows as $row) {
            DB::table('countries')->updateOrInsert(
                ['code' => $row['code']],
                array_merge($row, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
