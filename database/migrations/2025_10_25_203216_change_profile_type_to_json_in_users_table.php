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
        // Check if the column exists and is not already JSON
        $columnType = DB::select("SHOW COLUMNS FROM users WHERE Field = 'profile_type'");

        if (!empty($columnType)) {
            // Store existing profile types
            $userData = DB::table('users')->get(['id', 'profile_type'])->map(function ($user) {
                return [
                    'id' => $user->id,
                    'profile_types' => [$user->profile_type]
                ];
            });

            // Drop the old enum column
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('profile_type');
            });

            // Add new JSON column (nullable temporarily)
            Schema::table('users', function (Blueprint $table) {
                $table->json('profile_type')->nullable()->after('country');
            });

            // Restore the data as JSON
            foreach ($userData as $user) {
                DB::table('users')
                    ->where('id', $user['id'])
                    ->update(['profile_type' => json_encode($user['profile_types'])]);
            }

            // Set any null values to default
            DB::table('users')
                ->whereNull('profile_type')
                ->update(['profile_type' => json_encode(['employee'])]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert JSON back to first value in array
        DB::table('users')->get()->each(function ($user) {
            $profileTypes = json_decode($user->profile_type, true);
            $firstType = is_array($profileTypes) && count($profileTypes) > 0
                ? $profileTypes[0]
                : 'employee';

            DB::table('users')
                ->where('id', $user->id)
                ->update(['profile_type' => $firstType]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->enum('profile_type', ['student', 'employee', 'business_owner', 'freelancer', 'unemployed'])
                  ->default('employee')
                  ->change();
        });
    }
};
