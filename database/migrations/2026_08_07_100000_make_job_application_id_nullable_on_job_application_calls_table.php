<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            $this->migrateSqlite(nullable: true);

            return;
        }

        Schema::table('job_application_calls', function (Blueprint $table) {
            $table->dropForeign(['job_application_id']);
        });

        DB::statement('ALTER TABLE job_application_calls MODIFY job_application_id BIGINT UNSIGNED NULL');

        Schema::table('job_application_calls', function (Blueprint $table) {
            $table->foreign('job_application_id')
                ->references('id')
                ->on('job_applications')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            $this->migrateSqlite(nullable: false);

            return;
        }

        Schema::table('job_application_calls', function (Blueprint $table) {
            $table->dropForeign(['job_application_id']);
        });

        DB::statement('ALTER TABLE job_application_calls MODIFY job_application_id BIGINT UNSIGNED NOT NULL');

        Schema::table('job_application_calls', function (Blueprint $table) {
            $table->foreign('job_application_id')
                ->references('id')
                ->on('job_applications')
                ->cascadeOnDelete();
        });
    }

    private function migrateSqlite(bool $nullable): void
    {
        $rows = DB::table('job_application_calls')->get()->map(fn ($row) => (array) $row)->all();

        Schema::drop('job_application_calls');

        Schema::create('job_application_calls', function (Blueprint $table) use ($nullable) {
            $table->id();
            if ($nullable) {
                $table->foreignId('job_application_id')->nullable()->constrained('job_applications')->nullOnDelete();
            } else {
                $table->foreignId('job_application_id')->constrained('job_applications')->cascadeOnDelete();
            }
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('scheduled_at');
            $table->timestamps();

            $table->index(['job_application_id', 'scheduled_at']);
        });

        foreach ($rows as $row) {
            if (! $nullable && ($row['job_application_id'] ?? null) === null) {
                continue;
            }

            DB::table('job_application_calls')->insert($row);
        }
    }
};
