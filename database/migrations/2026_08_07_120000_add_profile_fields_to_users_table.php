<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->text('address')->nullable()->after('phone');
            $table->string('country_code', 2)->nullable()->after('address');
            $table->string('city')->nullable()->after('country_code');
            $table->string('state')->nullable()->after('city');
            $table->date('birthday')->nullable()->after('state');
            $table->string('linkedin')->nullable()->after('birthday');
            $table->string('github')->nullable()->after('linkedin');
            $table->string('x_url')->nullable()->after('github');
            $table->string('facebook')->nullable()->after('x_url');
            $table->string('instagram')->nullable()->after('facebook');
            $table->string('website')->nullable()->after('instagram');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'address',
                'country_code',
                'city',
                'state',
                'birthday',
                'linkedin',
                'github',
                'x_url',
                'facebook',
                'instagram',
                'website',
            ]);
        });
    }
};
