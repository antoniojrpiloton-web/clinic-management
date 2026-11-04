<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('patient_number', 50)->unique();
            $table->string('school_id_number', 50)->nullable()->index();
            $table->string('type', 30)->index();
            $table->string('first_name', 60);
            $table->string('middle_name', 60)->nullable();
            $table->string('last_name', 60);
            $table->string('suffix', 15)->nullable();
            $table->string('gender', 20);
            $table->date('date_of_birth');
            $table->string('civil_status', 30)->default('Single');
            $table->string('blood_type', 10)->nullable();
            $table->string('email', 120)->nullable()->index();
            $table->string('phone', 35)->nullable()->index();
            $table->text('address')->nullable();
            $table->string('emergency_contact_name', 120)->nullable();
            $table->string('emergency_contact_relationship', 60)->nullable();
            $table->string('emergency_contact_phone', 35)->nullable();
            $table->text('allergies')->nullable();
            $table->text('medical_history')->nullable();
            $table->string('status', 25)->default('active')->index();

            $table->foreignId('college_id')->nullable()->constrained('colleges')->nullOnDelete();
            $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->string('year_level', 30)->nullable();
            $table->string('section', 50)->nullable();
            $table->string('enrollment_status', 30)->nullable();

            $table->string('office_department', 120)->nullable();
            $table->string('employment_classification', 50)->nullable();
            $table->string('category', 40)->nullable();
            $table->string('position', 120)->nullable();
            $table->string('employment_status', 30)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
