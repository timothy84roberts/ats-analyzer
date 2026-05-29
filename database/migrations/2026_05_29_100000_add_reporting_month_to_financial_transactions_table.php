<?php

use App\Services\FinancialPeriodService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->string('reporting_month', 7)->nullable()->after('transacted_at');
        });

        $service = new FinancialPeriodService;

        DB::table('financial_transactions')
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($service) {
                foreach ($rows as $row) {
                    $date = Carbon::parse($row->transacted_at);
                    DB::table('financial_transactions')
                        ->where('id', $row->id)
                        ->update([
                            'reporting_month' => $service->autoReportingMonth($date),
                        ]);
                }
            });

        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->index(['user_id', 'reporting_month']);
        });
    }

    public function down(): void
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'reporting_month']);
            $table->dropColumn('reporting_month');
        });
    }
};
