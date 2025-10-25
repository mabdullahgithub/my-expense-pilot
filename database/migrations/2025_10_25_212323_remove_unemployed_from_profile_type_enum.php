<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update any 'unemployed' values to 'employee' before modifying the enum
        DB::table('employment_history')
            ->where('profile_type', 'unemployed')
            ->update(['profile_type' => 'employee']);

        // Recreate the enum column without 'unemployed'
        DB::statement("ALTER TABLE employment_history MODIFY COLUMN profile_type ENUM('student', 'employee', 'business_owner', 'freelancer') NOT NULL DEFAULT 'employee'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add 'unemployed' back to the enum
        DB::statement("ALTER TABLE employment_history MODIFY COLUMN profile_type ENUM('student', 'employee', 'business_owner', 'freelancer', 'unemployed') NOT NULL DEFAULT 'employee'");
    }
};
