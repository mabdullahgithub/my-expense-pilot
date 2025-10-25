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
        Schema::create('employment_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('company_name');
            $table->string('position');
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'internship', 'freelance']);
            $table->decimal('salary', 10, 2)->nullable();
            $table->enum('salary_frequency', ['hourly', 'monthly', 'yearly'])->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable(); // null means current job
            $table->text('description')->nullable();
            $table->boolean('is_current')->default(false);
            $table->timestamps();
            
            $table->index(['user_id', 'is_current']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employment_history');
    }
};
