<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employment_history', function (Blueprint $table) {
            $table->enum('profile_type', ['student', 'employee', 'business_owner', 'freelancer', 'unemployed'])
                ->after('user_id')
                ->default('employee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employment_history', function (Blueprint $table) {
            $table->dropColumn('profile_type');
        });
    }
};
