<?php

use App\Models\JobApplication;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $allowed = JobApplication::outcomeStatuses();
        DB::table('job_applications')
            ->whereNotIn('outcome_status', $allowed)
            ->update(['outcome_status' => JobApplication::OUTCOME_WAITING]);
    }

    public function down(): void
    {
        //
    }
};
