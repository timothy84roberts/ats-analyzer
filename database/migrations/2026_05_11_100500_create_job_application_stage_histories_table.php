<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_application_stage_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pipeline_stage_id')->constrained('pipeline_stages')->restrictOnDelete();
            $table->timestamp('entered_at')->useCurrent();

            $table->index(['job_application_id', 'entered_at'], 'ja_stage_hist_app_entered_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_application_stage_histories');
    }
};
