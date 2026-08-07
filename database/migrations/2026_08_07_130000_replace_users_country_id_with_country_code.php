<?php

use App\Models\Country;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'country_code') && ! Schema::hasColumn('users', 'country_id')) {
            return;
        }

        if (! Schema::hasColumn('users', 'country_code')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('country_code', 2)->nullable()->after('address');
            });
        }

        if (! Schema::hasColumn('users', 'country_id')) {
            return;
        }

        $countries = Country::query()->pluck('code', 'id');
        foreach (DB::table('users')->whereNotNull('country_id')->get(['id', 'country_id']) as $row) {
            $code = $countries[$row->country_id] ?? null;
            if ($code) {
                DB::table('users')->where('id', $row->id)->update([
                    'country_code' => strtoupper((string) $code),
                ]);
            }
        }

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            $this->dropCountryIdSqlite();

            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('country_id');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'country_id') || ! Schema::hasColumn('users', 'country_code')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('country_id')->nullable()->after('address')->constrained('countries')->nullOnDelete();
        });

        $countries = Country::query()->pluck('id', 'code');
        foreach (DB::table('users')->whereNotNull('country_code')->get(['id', 'country_code']) as $row) {
            $id = $countries[strtoupper((string) $row->country_code)] ?? null;
            if ($id) {
                DB::table('users')->where('id', $row->id)->update(['country_id' => $id]);
            }
        }

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            // Leave country_code in place on sqlite down for simplicity.
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('country_code');
        });
    }

    private function dropCountryIdSqlite(): void
    {
        $rows = DB::table('users')->get()->map(function ($row) {
            $data = (array) $row;
            unset($data['country_id']);

            return $data;
        })->all();

        Schema::drop('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('country_code', 2)->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->date('birthday')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('github')->nullable();
            $table->string('x_url')->nullable();
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('website')->nullable();
            $table->string('password');
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_ats_lab_allowed')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });

        foreach ($rows as $row) {
            DB::table('users')->insert($row);
        }
    }
};
