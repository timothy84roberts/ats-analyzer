<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add year_month column if not already present (partial migration guard)
        if (! Schema::hasColumn('financial_settings', 'year_month')) {
            Schema::table('financial_settings', function (Blueprint $table) {
                $table->string('year_month', 7)->after('user_id')->default('');
            });
        }

        // 2. Stamp any rows that have no month yet
        DB::table('financial_settings')
            ->where('year_month', '')
            ->update(['year_month' => now()->format('Y-m')]);

        // 3. Swap unique constraint only if the old single-column one still exists
        $indexes = collect(DB::select('SHOW INDEX FROM financial_settings'))
            ->pluck('Key_name')->unique()->values();

        if ($indexes->contains('financial_settings_user_id_unique')) {
            Schema::table('financial_settings', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropUnique('financial_settings_user_id_unique');
            });
        }

        if (! $indexes->contains('financial_settings_user_id_year_month_unique')) {
            Schema::table('financial_settings', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->unique(['user_id', 'year_month']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('financial_settings', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id', 'year_month']);
        });

        Schema::table('financial_settings', function (Blueprint $table) {
            $table->unique('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->dropColumn('year_month');
        });
    }
};
