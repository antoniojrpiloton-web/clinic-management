<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_logbooks', function (Blueprint $table) {
            $table->id();
            $table->string('log_number', 40)->unique();
            $table->date('log_date')->index();
            $table->time('time_in');
            $table->time('time_out')->nullable();
            $table->foreignId('patient_id')->nullable()->constrained('patients')->nullOnDelete();
            $table->foreignId('clinic_visit_id')->nullable()->constrained('clinic_visits')->nullOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->foreignId('college_id')->nullable()->constrained('colleges')->nullOnDelete();
            $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();
            $table->string('visitor_type', 30)->default('student')->index();
            $table->string('id_number', 50)->nullable()->index();
            $table->string('full_name', 160);
            $table->string('course_or_department', 120)->nullable();
            $table->string('year_or_position', 80)->nullable();
            $table->string('contact_number', 40)->nullable();
            $table->string('purpose', 190);
            $table->string('notes', 500)->nullable();
            $table->string('status', 30)->default('inside')->index();
            $table->timestamps();

            $table->index(['log_date', 'visitor_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_logbooks');
    }
};
