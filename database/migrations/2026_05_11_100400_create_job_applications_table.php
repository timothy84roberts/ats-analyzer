<?php

use App\Models\JobApplication;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('outcome_status', 32)->default(JobApplication::OUTCOME_WAITING)->index();
            $table->foreignId('pipeline_stage_id')->constrained('pipeline_stages')->restrictOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('country_id')->constrained('countries')->restrictOnDelete();
            $table->string('company_name')->nullable();
            $table->foreignId('platform_id')->constrained('platforms')->restrictOnDelete();
            $table->decimal('analysis_percentage', 5, 2)->nullable();
            $table->date('applied_on');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'applied_on']);
            $table->index(['user_id', 'outcome_status', 'applied_on']);
            $table->index(['user_id', 'country_id', 'applied_on']);
            $table->index(['user_id', 'platform_id', 'applied_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
